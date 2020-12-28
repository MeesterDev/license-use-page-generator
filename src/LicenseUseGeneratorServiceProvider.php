<?php

namespace MeesterDev\LicenseUseGenerator;

use Illuminate\Support\ServiceProvider;
use MeesterDev\LicenseUseGenerator\Commands\LicenseUseGenerateCommand;

class LicenseUseGeneratorServiceProvider extends ServiceProvider {
    public function boot() {
        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'license-generator');

        if ($this->app->runningInConsole()) {
            $this->commands(LicenseUseGenerateCommand::class);
        }
    }
}