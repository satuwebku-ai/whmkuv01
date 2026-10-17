<?php

namespace App\DomainRegisters;

use App\Models\AdminNotification;
use App\Models\DomainRegister;
use App\Models\DomainSetup;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * DaftarNama V2 reseller adapter.
 * Endpoints and request/response fields follow the supplied API documentation.
 */
class Daftarnama
{
    public $domain;
    public $request;
    public $singleSearch = false;
    protected $register;
    protected $baseUrl;
    protected $apiKey;
    protected $apiKeyHeader;

    public function __construct($domain)
    {
        $this->domain = $domain;
        $this->register = DomainRegister::where('alias', 'Daftarnama')->firstOrFail();
        $params = $this->register->params;

        $configuredUrl = trim((string) ($params->base_url->value ?? ''));
        $this->baseUrl = rtrim($configuredUrl ?: ($this->register->test_mode
            ? 'https://api.stagingv2.daftarnama.id'
            : 'https://api.daftarnama.id'), '/');
        $this->apiKey = trim((string) ($params->api_key->value ?? ''));
        // Header name is configurable because the provided rendered documentation
        // does not expose the OpenAPI security-scheme header name.
        $this->apiKeyHeader = trim((string) ($params->api_key_header->value ?? 'X-API-Key')) ?: 'X-API-Key';
    }

    protected function api(string $method, string $path, array $payload = [], array $query = [])
    {
        if ($this->apiKey === '') {
            return ['success' => false, 'message' => 'Daftarnama API Key belum dikonfigurasi.'];
        }

        try {
            $client = Http::acceptJson()->asJson()->withHeaders([$this->apiKeyHeader => $this->apiKey])->timeout(20)->connectTimeout(8);
            $url = $this->baseUrl . '/' . ltrim($path, '/');
            $response = match (strtoupper($method)) {
                'GET' => $client->get($url, $query),
                'POST' => $client->post($url, $payload),
                'PUT' => $client->put($url, $payload),
                'DELETE' => $client->delete($url, $payload),
                default => throw new \InvalidArgumentException('Unsupported HTTP method'),
            };

            $json = $response->json();
            if (!$response->successful() || !is_array($json) || (isset($json['code']) && (string) $json['code'] !== (string) $response->status())) {
                $message = is_array($json) ? ($json['message'] ?? 'Daftarnama API request failed.') : 'Respons Daftarnama bukan JSON yang valid.';
                return ['success' => false, 'message' => is_string($message) ? $message : json_encode($message)];
            }

            return ['success' => true, 'response' => $json, 'http_status' => $response->status()];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Koneksi ke Daftarnama gagal atau timeout. Periksa konfigurasi dan log aplikasi.'];
        }
    }

    protected function failed($result)
    {
        $message = $result['message'] ?? 'Daftarnama API request failed.';
        $this->adminNotification($this->domain, $message);
        return ['success' => false, 'message' => $message];
    }

    protected function makeNameservers($request = null, $noChange = false)
    {
        $domain = $this->domain;
        $server = optional(optional($domain->hosting)->server);
        $values = [];

        if ($request && isset($request->ns1, $request->ns2)) {
            $values = [$request->ns1, $request->ns2, $request->ns3 ?? null, $request->ns4 ?? null];
        } elseif ($noChange && $domain->ns1 && $domain->ns2) {
            $values = [$domain->ns1, $domain->ns2, $domain->ns3, $domain->ns4];
        } elseif ($server && $server->ns1 && $server->ns2) {
            $values = [$server->ns1, $server->ns2, $server->ns3, $server->ns4];
        } elseif ($domain->ns1 && $domain->ns2) {
            $values = [$domain->ns1, $domain->ns2, $domain->ns3, $domain->ns4];
        } else {
            $values = [$this->register->ns1, $this->register->ns2, $this->register->ns3, $this->register->ns4];
        }

        return array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $values)));
    }

    protected function contactPayload($user, $request = null)
    {
        $name = trim((string) ($request->name ?? ($user->fullname ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')))));
        $address1 = $request->address1 ?? $user->address ?? 'N/A';
        $address2 = $request->address2 ?? '';
        $country = strtoupper((string) ($request->country ?? $user->country_code ?? 'ID'));
        $phone = trim((string) ($request->telephone ?? $user->mobile ?? ''));
        $cc = trim((string) ($request->telephonecc ?? ''));
        if ($cc !== '' && $phone !== '' && !str_starts_with($phone, '+')) {
            $phone = '+' . ltrim($cc, '+') . $phone;
        }

        return [
            'name' => $name ?: 'Domain Customer',
            'company_name' => (string) ($request->company_name ?? $user->company_name ?? ''),
            'email' => (string) ($request->email ?? $user->email ?? ''),
            'address_1' => (string) $address1,
            'address_2' => (string) $address2,
            'address_3' => '',
            'city' => (string) ($request->city ?? $user->city ?? 'N/A'),
            'province' => (string) ($request->province ?? $user->state ?? 'N/A'),
            'country' => $country,
            'postal_code' => (string) ($request->zip ?? $user->zip ?? '00000'),
            'phone_number' => $phone,
            'mobile_phone_number' => $phone,
        ];
    }

    public function searchDomain()
    {
        $domainInput = trim((string) $this->domain);
        $sld = getSld($domainInput);
        $tld = getTld($domainInput);
        $setups = DomainSetup::active()->with('pricing')->get(['id', 'extension']);
        $supportedSetup = $tld ? $setups->firstWhere('extension', $tld) : null;

        if ($this->singleSearch) {
            if (!$supportedSetup) {
                return ['success' => true, 'regster_name' => 'daftarnama', 'domain' => $domainInput, 'sld' => $sld, 'tld' => $tld, 'isSupported' => false, 'setup' => null, 'data' => ['status' => 'ERROR', 'message' => 'TLD tidak didukung.']];
            }
            $result = $this->api('GET', '/domain-availability', [], ['domain_name' => $domainInput]);
            if (!$result['success']) return $this->failed($result);
            $data = $result['response']['data'] ?? [];
            return [
                'success' => true, 'regster_name' => 'daftarnama', 'domain' => $domainInput,
                'sld' => $sld, 'tld' => $tld, 'isSupported' => true, 'setup' => $supportedSetup,
                'data' => ['status' => 'OK', 'available' => (bool) ($data['available'] ?? false), 'Available' => !empty($data['available']) ? 'true' : 'false'],
            ];
        }

        $results = [];
        foreach ($setups as $setup) {
            $candidate = $sld . $setup->extension;
            $result = $this->api('GET', '/domain-availability', [], ['domain_name' => $candidate]);
            if (!$result['success']) return $this->failed($result);
            $available = (bool) data_get($result, 'response.data.available', false);
            $results[] = [
                'match' => $candidate === $domainInput ? 999 : 0,
                'domain' => $candidate,
                'setup' => $setup,
                'available' => $available,
            ];
        }

        return ['success' => true, 'regster_name' => 'daftarnama', 'domain' => $domainInput, 'sld' => $sld, 'tld' => $tld, 'isSupported' => (bool) $supportedSetup, 'data' => $results];
    }

    public function register()
    {
        $domain = $this->domain;
        $user = $domain->user;
        if (!$user) return ['success' => false, 'message' => 'Data pelanggan domain tidak ditemukan.'];

        $payload = array_merge([
            'domain_name' => (string) $domain->domain,
            'duration' => max(1, (int) $domain->reg_period),
            'nameservers' => $this->makeNameservers($this->request),
            'with_existing_customer' => false,
            'username' => substr(preg_replace('/[^a-zA-Z0-9_.-]/', '', (string) ($user->username ?? $user->email)), 0, 48),
            'password' => Str::random(32),
        ], $this->contactPayload($user));

        $result = $this->api('POST', '/domains', $payload);
        if (!$result['success']) return $this->failed($result);

        $data = $result['response']['data'] ?? [];
        $status = strtoupper((string) ($data['status'] ?? ''));
        $pendingDocument = $status === 'WAITING_FOR_DOCUMENT' || strtoupper((string) ($data['document_status'] ?? '')) === 'NOT_COMPLETED';

        $domain->ns1 = $payload['nameservers'][0] ?? null;
        $domain->ns2 = $payload['nameservers'][1] ?? null;
        $domain->ns3 = $payload['nameservers'][2] ?? null;
        $domain->ns4 = $payload['nameservers'][3] ?? null;
        if (!empty($data['expiry_date'])) $domain->expiry_date = $data['expiry_date'];
        // Status 3 is the application's existing Pending Registration status.
        $domain->status = $pendingDocument ? 3 : 1;
        $domain->save();

        if ($pendingDocument) {
            $upload = $this->api('GET', '/domains/' . rawurlencode($domain->domain) . '/upload_document_url');
            $uploadUrl = $upload['success'] ? data_get($upload, 'response.data.upload_document_uri') : null;
            $message = 'Registrasi diterima Daftarnama, tetapi menunggu dokumen verifikasi. Domain tetap Pending Registration.';
            if ($uploadUrl) $message .= ' URL unggah dokumen: ' . $uploadUrl;
            $this->adminNotification($domain, $message);
            return ['success' => true, 'pending' => true, 'message' => $message];
        }

        return ['success' => true, 'pending' => false, 'message' => 'Registrasi domain diterima Daftarnama.'];
    }

    public function renew()
    {
        $domain = $this->domain;
        $request = $this->request;
        $expiry = $domain->expiry_date ? $domain->expiry_date->format('Y-m-d') : null;
        if (!$expiry) return ['success' => false, 'message' => 'Tanggal kedaluwarsa domain belum tersedia; perpanjangan dibatalkan agar tanggal tidak ditebak.'];

        $result = $this->api('POST', '/domains/' . rawurlencode($domain->domain) . '/renew', [
            'current_expiry_date' => $expiry,
            'duration' => max(1, (int) ($request ? $request->renew_year : $domain->reg_period)),
        ]);
        if (!$result['success']) return $this->failed($result);
        $newExpiry = data_get($result, 'response.data.expiry_date');
        if ($newExpiry) $domain->expiry_date = $newExpiry;
        $domain->save();
        return ['success' => true, 'message' => 'Perpanjangan domain diterima Daftarnama.', 'expiry_date' => $newExpiry];
    }

    public function getContact()
    {
        $result = $this->api('GET', '/domains/' . rawurlencode($this->domain->domain));
        if (!$result['success']) return $this->failed($result);
        $contact = data_get($result, 'response.data.registrant_contact', []);
        $mapped = (object) [
            'name' => $contact['name'] ?? '', 'company' => $contact['company_name'] ?? '',
            'emailaddr' => $contact['email'] ?? '', 'address1' => $contact['address_1'] ?? '',
            'address2' => $contact['address_2'] ?? '', 'city' => $contact['city'] ?? '',
            'country' => $contact['country'] ?? 'ID', 'zip' => $contact['postal_code'] ?? '',
            'telnocc' => '', 'telno' => $contact['mobile_phone_number'] ?? ($contact['phone_number'] ?? ''),
        ];
        return ['success' => true, 'response' => $mapped];
    }

    public function setContact()
    {
        $domain = $this->domain;
        $contact = $this->contactPayload($domain->user, $this->request);
        unset($contact['mobile_phone_number']);
        $contact['organization_name'] = $contact['company_name'];
        unset($contact['company_name']);
        $result = $this->api('PUT', '/domains/' . rawurlencode($domain->domain) . '/registrant_contact', $contact);
        if (!$result['success']) return $this->failed($result);
        return ['success' => true, 'message' => 'Informasi kontak domain berhasil diperbarui.'];
    }

    public function changeNameservers()
    {
        $domain = $this->domain;
        $nameservers = $this->makeNameservers($this->request, true);
        if (count($nameservers) < 2) return ['success' => false, 'message' => 'Minimal dua nameserver harus dikonfigurasi.'];
        $result = $this->api('PUT', '/domains/' . rawurlencode($domain->domain) . '/nameservers', ['nameservers' => $nameservers]);
        if (!$result['success']) return $this->failed($result);
        $domain->ns1 = $nameservers[0] ?? null;
        $domain->ns2 = $nameservers[1] ?? null;
        $domain->ns3 = $nameservers[2] ?? null;
        $domain->ns4 = $nameservers[3] ?? null;
        $domain->save();
        return ['success' => true, 'message' => 'Nameserver domain berhasil diperbarui.'];
    }

    // DaftarNama documents update/transfer protection, not WHOIS privacy. Do not
    // silently map the application's ID-protection action to a different feature.
    public function enableIdProtection()
    {
        return ['success' => false, 'message' => 'Daftarnama API tidak mendokumentasikan WHOIS/ID protection; tidak ada perubahan yang dilakukan.'];
    }

    public function disableIdProtection()
    {
        return ['success' => false, 'message' => 'Daftarnama API tidak mendokumentasikan WHOIS/ID protection; tidak ada perubahan yang dilakukan.'];
    }

    public function enableUpdateProtection() { return $this->setUpdateProtection(true); }
    public function disableUpdateProtection() { return $this->setUpdateProtection(false); }

    protected function setUpdateProtection(bool $enabled)
    {
        $result = $this->api('PUT', '/domains/' . rawurlencode($this->domain->domain) . '/is_enable_update_protection', ['is_enable_update_protection' => $enabled]);
        if (!$result['success']) return $this->failed($result);
        return ['success' => true, 'message' => $enabled ? 'Proteksi update domain diaktifkan.' : 'Proteksi update domain dinonaktifkan.'];
    }

    protected function adminNotification($domain, $message)
    {
        try {
            $notification = new AdminNotification();
            $notification->user_id = $domain->user_id;
            $notification->title = is_array($message) ? implode('. ', $message) : (string) $message;
            $notification->api_response = 1;
            $notification->click_url = urlPath('admin.order.domain.details', $domain->id);
            $notification->save();
        } catch (Throwable $ignored) {
            // API failure reporting must not itself crash the domain workflow.
        }
    }
}
