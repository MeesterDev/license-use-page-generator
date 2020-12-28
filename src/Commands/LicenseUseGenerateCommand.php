<?php

namespace MeesterDev\LicenseUseGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use MeesterDev\FileWrapper\File;
use MeesterDev\PackageParser\Entities\PackageInformation;
use MeesterDev\PackageParser\Parsers\ParserFactory;
use MeesterDev\PackageParser\Parsers\AbstractParser;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;

class LicenseUseGenerateCommand extends Command {
    protected               $signature = 'license-page:generate {source* : Files to use as source. } {--keep-public-domain} {--title=} {--view=}';
    private OutputFormatter $formatter;

    public function __construct() {
        $this->formatter = new OutputFormatter(defined('STDERR')); // for now assume that you use a decent CLI (if there is a CLI)

        parent::__construct();
    }

    public function handle() {
        $sources = collect($this->argument('source'));

        $parsers  = $sources
            ->map(
                function (string $source) {
                    $parser = ParserFactory::createForFile(new File($source));
                    if ($this->option('keep-public-domain')) {
                        $parser->includePublicDomainLicenses();
                    }

                    return $parser;
                }
            );

        $packages = $parsers
            ->map(
                function (AbstractParser $parser): Collection {
                    return collect($parser->parse());
                }
            )
            ->flatten()
            ->unique(
                function (PackageInformation $package): string {
                    return $package->name . '|||' . $package->source . '|||' . $package->licenseType;
                }
            )
            ->sortBy('name');

        $this->output->write(
            view($this->option('view') ?? 'license-generator::licenses', ['packages' => $packages, 'title' => $this->option('title')])
        );

        $debugOut = defined('STDERR') ? STDERR : fopen('php://output', 'w');
        if ($debugOut !== false) {
            if ($this->getOutput()->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
                $skippedPackages = $parsers->map(function (AbstractParser $parser): Collection { return collect($parser->skippedPackages); })
                                           ->flatten();
                if ($skippedPackages->isNotEmpty()) {
                    fwrite($debugOut, $this->formatter->format('<info>Skipped the following packages:</info>') . PHP_EOL);
                    static::printPackageList($debugOut, $skippedPackages);
                }
            }

            if ($this->getOutput()->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $failedPackages = $parsers->map(function (AbstractParser $parser): Collection { return collect($parser->failedPackages); })->flatten(
                );
                if ($failedPackages->isNotEmpty()) {
                    fwrite($debugOut, $this->formatter->format('<error>Loading failed for the following packages:</error>') . PHP_EOL);
                    static::printPackageList($debugOut, $failedPackages);
                }
            }
        }

        return 0;
    }

    private static function printPackageList($output, Collection $packages) {
        $packages = $packages->sortBy('name')->unique(
            function (PackageInformation $package): string {
                return (string) $package;
            }
        );
        /** @var PackageInformation $package */
        foreach ($packages as $package) {
            fwrite($output, "  - $package" . PHP_EOL);
        }
    }
}
