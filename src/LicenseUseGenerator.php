<?php

namespace MeesterDev\LicenseUsePageGenerator;

use JsonException;
use MeesterDev\FileWrapper\Exception\NotReadableException;
use MeesterDev\FileWrapper\File;
use MeesterDev\LicenseUsePageGenerator\Licenses\Licenses;
use MeesterDev\LicenseUsePageGenerator\Util\StdErr;
use MeesterDev\PackageParser\Entities\PackageInformation;
use MeesterDev\PackageParser\Exceptions\UnknownPackageFileFormatException;
use MeesterDev\PackageParser\Parsers\AbstractParser;
use MeesterDev\PackageParser\Parsers\ParserFactory;

class LicenseUseGenerator {
    /** @var string[] */
    private array $failedPackages = [];
    /** @var string[] */
    private array $skippedPackages = [];

    public function __construct(
        private readonly string|array $source,
        private readonly bool $keepPublicDomain = false,
        private readonly string $title = 'Open Source Licenses',
        private readonly array $excludedPackages = [],
    ) {
    }

    /**
     * @throws UnknownPackageFileFormatException
     * @throws NotReadableException
     * @throws JsonException
     */
    public function run(): void {
        $sources = is_array($this->source) ? $this->source : [$this->source];

        $parsers          = $this->getParsers($sources);
        $packages         = $this->getPackages($parsers);
        $includedPackages = [];
        foreach ($packages as $package) {
            if ($this->isPackageIncluded($package)) {
                $includedPackages[] = $package;
            }
            else {
                $this->skippedPackages[] = $package;
            }
        }

        $parsablePackages = [];
        foreach ($includedPackages as $package) {
            if ($this->canCreateLicenseTextForPackage($package)) {
                $parsablePackages[] = $package;
            }
            else {
                $this->failedPackages[] = $package;
            }
        }

        $keys     = [];
        $packages = [];
        foreach ($parsablePackages as $package) {
            if (!isset($keys[$package->name][$package->source][$package->licenseType])) {
                $keys[$package->name][$package->source][$package->licenseType] = true;
                $packages[]                                                    = $package;
            }
        }

        usort($packages, fn(PackageInformation $a, PackageInformation $b): int => strcmp($a->name, $b->name));

        $this->printOutput($packages);

        foreach ($parsers as $parser) {
            $this->failedPackages  = array_merge($this->failedPackages, array_map(fn(PackageInformation $package): string => $package->name, $parser->failedPackages));
            $this->skippedPackages = array_merge($this->skippedPackages, array_map(fn(PackageInformation $package): string => $package->name, $parser->skippedPackages));
        }
    }

    public function getFailedPackages(): array {
        return $this->failedPackages;
    }

    public function getSkippedPackages(): array {
        return $this->skippedPackages;
    }

    public function printDebugOutput(): void {
        if (!empty($this->failedPackages)) {
            StdErr::print('Failed packages:');

            foreach ($this->failedPackages as $failedPackage) {
                StdErr::print(" - $failedPackage");
            }
        }

        if (!empty($this->skippedPackages)) {
            StdErr::print('Skipped packages:');

            foreach ($this->skippedPackages as $skippedPackage) {
                StdErr::print(" - $skippedPackage");
            }
        }
    }

    private function printOutput(array $packages): void {
        $title = $this->title;
        require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'licenses.php';
    }

    /**
     * @param string[] $sources
     *
     * @return AbstractParser
     *
     * @throws JsonException
     * @throws NotReadableException
     * @throws UnknownPackageFileFormatException
     */
    private function getParsers(array $sources): array {
        return array_map(function (string $source) {
            $parser = ParserFactory::createForFile(new File($source));
            if (!$this->keepPublicDomain) {
                $parser->alsoIgnoreLicenses(Licenses::PUBLIC_DOMAIN_LICENSES);
            }

            return $parser;
        }, $sources);
    }

    /**
     * @param AbstractParser[] $parsers
     *
     * @return PackageInformation[]
     */
    private function getPackages(array $parsers): array {
        $packages = array_map(fn(AbstractParser $parser): array => $parser->parse(), $parsers);

        return array_merge(...$packages);
    }

    private function canCreateLicenseTextForPackage(PackageInformation $package): bool {
        return $package->licenseFileLocation !== null || in_array($package->licenseType, Licenses::LICENSES_WITH_TEXT_AVAILABLE);
    }

    private function isPackageIncluded(PackageInformation $package): bool {
        foreach ($this->excludedPackages as $excluded) {
            if (
                $package->name === $excluded
                || (str_ends_with($excluded, '/') && strncmp($excluded, $package->name, strlen($excluded)) === 0)
                || (str_ends_with($excluded, '/*') && strncmp($excluded, $package->name, strlen($excluded) - 1) === 0)
            ) {
                return false;
            }
        }

        return true;
    }
}
