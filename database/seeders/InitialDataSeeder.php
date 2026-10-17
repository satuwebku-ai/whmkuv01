<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\CronSchedule;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Data awal minimum agar situs, login admin, dan login client berjalan.
 * Aman dijalankan berulang: data yang sudah ada tidak ditimpa.
 *
 * Akun admin awal dapat diatur lewat .env:
 *   ADMIN_USERNAME, ADMIN_EMAIL, ADMIN_PASSWORD
 */
class InitialDataSeeder extends Seeder
{
    private const TEMPLATE = 'basic';

    public function run(): void
    {
        $this->generalSettings();
        $this->admin();
        $this->language();
        $this->cronSchedules();
        $this->homePage();
        $this->frontendContent();

        Cache::forget('GeneralSetting');
    }

    private function generalSettings(): void
    {
        if (GeneralSetting::query()->exists()) {
            return;
        }

        $gs = new GeneralSetting();
        $gs->site_name          = env('APP_NAME', 'WHMLab');
        $gs->cur_text           = 'IDR';
        $gs->cur_sym            = 'Rp';
        $gs->email_from         = 'noreply@example.com';
        $gs->email_from_name    = env('APP_NAME', 'WHMLab');
        $gs->email_template     = '<div>{{fullname}} ({{username}})<br>{{message}}</div>';
        $gs->sms_template       = 'hi {{fullname}} ({{username}}), {{message}}';
        $gs->sms_from           = env('APP_NAME', 'WHMLab');
        $gs->push_title         = env('APP_NAME', 'WHMLab');
        $gs->push_template      = 'Hi {{fullname}} ({{username}}), {{message}}';
        $gs->base_color         = '4634ff';
        $gs->mail_config        = json_encode(['name' => 'php']);
        $gs->sms_config         = json_encode(['name' => 'clickatell']);
        $gs->global_shortcodes  = json_encode([
            'site_name'  => '{{site_name}}',
            'site_currency' => '{{site_currency}}',
            'currency_symbol' => '{{currency_symbol}}',
        ]);
        $gs->firebase_config    = json_encode([]);
        $gs->socialite_credentials = json_encode([]);
        $gs->active_template    = self::TEMPLATE;
        $gs->registration       = 1;
        $gs->ev                 = 0;
        $gs->sv                 = 0;
        $gs->kv                 = 0;
        $gs->en                 = 1;
        $gs->sn                 = 0;
        $gs->pn                 = 0;
        $gs->multi_language     = 1;
        $gs->deposit_module     = 1;
        $gs->maintenance_mode   = 0;
        $gs->force_ssl          = 0;
        $gs->secure_password    = 0;
        $gs->agree              = 1;
        $gs->paginate_number    = 20;
        $gs->invoice_start      = 1;
        $gs->invoice_increment  = 1;
        $gs->config_progress    = json_encode(['complete_step' => [], 'finish_step' => []]);
        $gs->save();
    }

    private function admin(): void
    {
        if (Admin::query()->exists()) {
            return;
        }

        $admin           = new Admin();
        $admin->id       = 1;                       // id 1 = super admin (lihat Role::hasPermission)
        $admin->role_id  = 0;
        $admin->name     = 'Super Admin';
        $admin->username = env('ADMIN_USERNAME', 'admin');
        $admin->email    = env('ADMIN_EMAIL', 'admin@example.com');
        $admin->password = Hash::make(env('ADMIN_PASSWORD', 'Admin@12345'));
        $admin->status   = 1;
        $admin->save();
    }

    private function language(): void
    {
        if (Language::query()->exists()) {
            return;
        }

        $lang             = new Language();
        $lang->name       = 'English';
        $lang->code       = 'en';
        $lang->is_default = 1;
        $lang->save();
    }

    private function cronSchedules(): void
    {
        if (CronSchedule::query()->exists()) {
            return;
        }

        foreach ([['Hourly', 3600], ['Daily', 86400], ['Weekly', 604800]] as [$name, $interval]) {
            $row           = new CronSchedule();
            $row->name     = $name;
            $row->interval = $interval;
            $row->status   = 1;
            $row->save();
        }
    }

    private function homePage(): void
    {
        $tempname = 'templates.' . self::TEMPLATE . '.';

        $seo = [
            'image'              => null,
            'description'        => null,
            'social_title'       => null,
            'social_description' => null,
            'keywords'           => [],
        ];

        $exists = Page::where('tempname', $tempname)->where('slug', '/')->exists();
        if (!$exists) {
            $page             = new Page();
            $page->name       = 'HOME';
            $page->slug       = '/';
            $page->tempname   = $tempname;
            $page->secs       = json_encode(['service', 'domain', 'faq']);
            $page->seo_content = (object) $seo;
            $page->is_default = 1;
            $page->save();
        }
    }

    private function frontendContent(): void
    {
        $rows = [
            ['service.content', true, [
                'heading'    => 'Layanan Kami',
                'subheading' => 'Pilih layanan hosting yang sesuai dengan kebutuhan Anda.',
            ]],
            ['domain.content', true, [
                'heading'    => 'Cari Nama Domain Anda',
                'subheading' => 'Mulai kehadiran online Anda dengan domain yang tepat.',
                'text'       => 'Periksa ketersediaan domain dalam hitungan detik.',
            ]],
            ['footer.content', true, [
                'description' => 'Layanan hosting dan domain yang cepat, aman, dan terpercaya.',
            ]],
            ['subscribe.content', true, [
                'heading'    => 'Berlangganan Info Terbaru',
                'subheading' => 'Dapatkan promo dan kabar terbaru langsung di email Anda.',
            ]],
            ['contact_us.content', true, [
                'heading'     => 'Hubungi Kami',
                'description' => 'Tim kami siap membantu Anda.',
                'address'     => 'Indonesia',
                'phone'       => '+62 000 0000 0000',
                'email'       => 'support@example.com',
            ]],
            ['register_disable.content', true, [
                'heading'     => 'Pendaftaran Ditutup',
                'subheading'  => 'Pendaftaran akun baru sedang tidak tersedia.',
                'button_name' => 'Kembali ke Beranda',
                'button_url'  => '/',
                'image'       => '',
            ]],
            ['banned.content', true, [
                'heading' => 'Akun Anda Diblokir',
                'image'   => '',
            ]],
            ['kyc.content', true, [
                'kyc_required' => 'Anda harus menyelesaikan verifikasi KYC terlebih dahulu.',
                'kyc_pending'  => 'Verifikasi KYC Anda sedang ditinjau.',
                'kyc_reject'   => 'Verifikasi KYC Anda ditolak.',
            ]],
            ['faq.element', false, [
                'question' => 'Berapa lama aktivasi hosting?',
                'answer'   => 'Umumnya otomatis aktif setelah pembayaran terkonfirmasi.',
            ]],
            ['faq.element', false, [
                'question' => 'Apakah tersedia dukungan teknis?',
                'answer'   => 'Ya, Anda dapat membuka tiket dukungan kapan saja dari area client.',
            ]],
            ['policy_pages.element', false, [
                'title'   => 'Syarat dan Ketentuan',
                'details' => '<p>Isi syarat dan ketentuan layanan.</p>',
            ]],
            ['policy_pages.element', false, [
                'title'   => 'Kebijakan Privasi',
                'details' => '<p>Isi kebijakan privasi.</p>',
            ]],
        ];

        foreach ($rows as [$key, $single, $values]) {
            $query = Frontend::where('tempname', self::TEMPLATE)->where('data_keys', $key);

            if ($single) {
                if ($query->exists()) {
                    continue;
                }
            } else {
                // elemen berulang: lewati jika elemen identik sudah ada
                $dupe = $query->get()->contains(fn ($f) => json_encode($f->data_values) === json_encode((object) $values));
                if ($dupe) {
                    continue;
                }
            }

            $f              = new Frontend();
            $f->data_keys   = $key;
            $f->tempname    = self::TEMPLATE;
            $f->data_values = (object) $values;
            $f->save();
        }

        if (!Frontend::where('data_keys', 'seo.data')->exists()) {
            $seo               = new Frontend();
            $seo->data_keys    = 'seo.data';
            $seo->data_values  = (object) [
                'keywords'           => [],
                'description'        => '',
                'social_title'       => env('APP_NAME', 'WHMLab'),
                'social_description' => '',
                'image'              => null,
            ];
            $seo->save();
        }
    }
}
