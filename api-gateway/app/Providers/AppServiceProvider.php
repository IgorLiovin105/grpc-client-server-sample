<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Grpc\GrpcurlClient;
use App\Services\Grpc\HelloRpc;
use App\Services\Grpc\FileRpc;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GrpcurlClient::class, function () {
            return new GrpcurlClient(
                grpcurlPath: env('GRPCURL_PATH', 'D:/grpcurl/grpcurl.exe'),
                host: env('GRPC_HOST', '127.0.0.1:50051')
            );
        });

        $this->app->singleton(HelloRpc::class);
        $this->app->singleton(FileRpc::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
