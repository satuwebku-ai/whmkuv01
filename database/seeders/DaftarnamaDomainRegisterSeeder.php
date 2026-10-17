<?php

namespace Database\Seeders;

use App\Models\DomainRegister;
use Illuminate\Database\Seeder;

class DaftarnamaDomainRegisterSeeder extends Seeder
{
    public function run(): void
    {
        $register = DomainRegister::firstOrNew(['alias' => 'Daftarnama']);
        if ($register->exists) {
            return; // Do not overwrite an existing production configuration or API key.
        }

        $register->name = 'Daftarnama';
        // Nameservers are intentionally blank; configure the actual values from the provider/admin.
        $register->ns1 = null;
        $register->ns2 = null;
        $register->ns3 = null;
        $register->ns4 = null;
        $register->params = (object) [
            'api_key' => (object) ['title' => 'API Key', 'value' => '', 'required' => true, 'test_mode' => false],
            'api_key_header' => (object) ['title' => 'API Key Header (confirm with provider)', 'value' => 'X-API-Key', 'required' => true, 'test_mode' => false],
            'base_url' => (object) ['title' => 'API Base URL (optional override)', 'value' => '', 'required' => false, 'test_mode' => false],
        ];
        $register->test_mode = 1;
        $register->default = 0;
        $register->status = 1;
        $register->setup_done = 0;
        $register->save();
    }
}
