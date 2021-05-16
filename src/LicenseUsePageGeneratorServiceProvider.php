<?php

namespace MeesterDev\LicenseUsePageGenerator;

use Illuminate\Support\ServiceProvider;
use MeesterDev\LicenseUsePageGenerator\Commands\LicenseUseGenerateCommand;

class LicenseUsePageGeneratorServiceProvider extends ServiceProvider {
    public function boot() {
        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'license-generator');

        if ($this->app->runningInConsole()) {
            $this->commands(LicenseUseGenerateCommand::class);
        }
    }
}