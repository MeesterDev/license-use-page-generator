<?php

namespace MeesterDev\LicenseUsePageGenerator;

use Illuminate\Support\ServiceProvider;
use MeesterDev\LicenseUsePageGenerator\Commands\LicenseUseGenerateCommand;

class LicenseUsePageGeneratorServiceProvider extends ServiceProvider {
    public function boot() {
        if ($this->app->runningInConsole()) {
            $this->commands(LicenseUseGenerateCommand::class);
        }
    }
}