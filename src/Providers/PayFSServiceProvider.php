<?php

namespace FriendsOfBotble\PayFS\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;

class PayFSServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('plugins/fob-payfs');
    }

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        $this
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadHelpers()
            ->publishAssets();

        $this->app->register(HookServiceProvider::class);
    }
}
