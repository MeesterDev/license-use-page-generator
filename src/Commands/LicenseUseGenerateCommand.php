<?php

namespace MeesterDev\LicenseUsePageGenerator\Commands;

use Illuminate\Console\Command;
use MeesterDev\LicenseUsePageGenerator\LicenseUseGenerator;
use Symfony\Component\Console\Output\OutputInterface;

class LicenseUseGenerateCommand extends Command {
    protected $signature = 'license-page:generate {source* : Files to use as source. } {--keep-public-domain} {--title=} {--exclude=*}';

    public function handle() {
        $sources   = $this->argument('source');
        $generator = new LicenseUseGenerator($sources, $this->option('keep-public-domain') ?? false, $this->option('title') ?? 'Open Source Licenses', $this->option('exclude') ?? []);
        $generator->run();

        if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $generator->printDebugOutput();
        }

        return 0;
    }
}
