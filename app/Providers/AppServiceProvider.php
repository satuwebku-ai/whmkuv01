<?php

namespace App\Providers;

use App\Constants\Status;
use App\Lib\Searchable;
use App\Models\AdminNotification;
use App\Models\CancelRequest;
use App\Models\Deposit;
use App\Models\Frontend;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        Builder::mixin(new Searchable);
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        /*
         * Gunakan Bootstrap 5 untuk pagination.
         * Pengaturan ini tidak memerlukan database.
         */
        Paginator::useBootstrapFive();

        /*
         * Jangan menjalankan query database ketika database
         * belum tersedia atau migration belum membuat tabel.
         */
        try {
            if (!Schema::hasTable('general_settings')) {
                return;
            }
        } catch (\Throwable $exception) {
            /*
             * Misalnya database belum dibuat atau koneksi gagal.
             * Biarkan Artisan berjalan sejauh memungkinkan.
             */
            return;
        }

        /*
         * Pemeriksaan instalasi aplikasi.
         * Dijalankan setelah tabel general_settings tersedia.
         */
        if (!cache()->get('SystemInstalled')) {
            $envFilePath = base_path('.env');

            if (!file_exists($envFilePath)) {
                if (!app()->runningInConsole()) {
                    header('Location: install');
                    exit;
                }

                return;
            }

            $envContents = file_get_contents($envFilePath);

            if (empty($envContents)) {
                if (!app()->runningInConsole()) {
                    header('Location: install');
                    exit;
                }

                return;
            }

            cache()->put('SystemInstalled', true);
        }

        /*
         * Variabel yang dibagikan ke view.
         */
        view()->share([
            'emptyMessage' => 'Data not found',
        ]);

        /*
         * Data untuk sidebar admin.
         */
        view()->composer('admin.partials.sidenav', function ($view) {
            $view->with([
                'bannedUsersCount' => User::banned()->count(),
                'emailUnverifiedUsersCount' => User::emailUnverified()->count(),
                'mobileUnverifiedUsersCount' => User::mobileUnverified()->count(),
                'kycUnverifiedUsersCount' => User::kycUnverified()->count(),
                'kycPendingUsersCount' => User::kycPending()->count(),

                'pendingTicketCount' => SupportTicket::whereIn(
                    'status',
                    [
                        Status::TICKET_OPEN,
                        Status::TICKET_REPLY,
                    ]
                )->count(),

                'pendingDepositsCount' => Deposit::pending()->count(),
                'pendingOrderCount' => Order::pending()->count(),
                'unpaidInvoiceCount' => Invoice::unpaid()->count(),
                'pendingCancelRequestCount' => CancelRequest::pending()->count(),

                'countAutomationError' => AdminNotification::where(
                    'api_response',
                    1
                )->where('is_read', 0)->count(),
            ]);
        });

        /*
         * Data kategori layanan untuk layout frontend.
         */
        $activeTemplate = activeTemplate();

        view()->composer(
            [
                $activeTemplate . 'partials.header',
                $activeTemplate . 'layouts.side_bar',
                $activeTemplate . 'layouts.master_side_bar',
            ],
            function ($view) {
                $view->with([
                    'serviceCategories' => ServiceCategory::active()
                        ->get(['id', 'name', 'slug']),
                ]);
            }
        );

        /*
         * Jumlah notifikasi admin.
         */
        view()->composer('admin.partials.topnav', function ($view) {
            $view->with([
                'adminNotificationCount' => AdminNotification::where(
                    'is_read',
                    Status::NO
                )->count(),
            ]);
        });

        /*
         * Metadata SEO frontend.
         */
        view()->composer('partials.seo', function ($view) {
            $seo = Frontend::where('data_keys', 'seo.data')->first();

            $view->with([
                'seo' => $seo ? $seo->data_values : null,
            ]);
        });

        /*
         * Paksa HTTPS jika konfigurasi force_ssl aktif.
         */
        if (gs('force_ssl')) {
            URL::forceScheme('https');
        }
    }
}
