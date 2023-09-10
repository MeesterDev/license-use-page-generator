<?php

use MeesterDev\LicenseUsePageGenerator\LicenseUseGenerator;
use MeesterDev\LicenseUsePageGenerator\Util\StdErr;
use MeesterDev\PackageParser\Exceptions\UnknownPackageFileFormatException;

// make sure you can run this file standalone by including the autoloader
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';
}
else {
    function requireClass(string $class): void {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class . '.php';
    }

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    requireClass('Licenses/HtmlGenerator');
    requireClass('Licenses/Licenses');
    requireClass('Util/StdErr');
    requireClass('LicenseUseGenerator');
}

if (!isset($argc) || !is_int($argc) || !isset($argv) || !is_array($argv) || count($argv) != $argc) {
    printHelp();
}

$sources = [];
$options = [];
for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];

    if ($arg === '--') {
        $sources = array_merge($sources, array_slice($argv, $i +1));
        break;
    }

    if ($arg === '--help') {
        printHelp();
    }

    if (str_starts_with($arg, '--')) {
        $pos = strpos($arg, '=');
        if ($pos === false) {
            $options[substr($arg, 2)] = true;
        } else {
            $key = substr($arg, 2, $pos - 2);
            $value = substr($arg, $pos + 1);
            if (isset($options[$key])) {
                if (is_array($options[$key])) {
                    $options[$key][] = $value;
                } else {
                    $options[$key] = [$options[$key], $value];
                }
            } else {
                $options[$key] = $value;
            }
        }
    } else {
        $sources[] = $argv[$i];
    }
}

foreach ($options as $key => $value) {
    if (!in_array($key, ['keep-public-domain', 'title', 'exclude', 'verbose'])) {
        printHelp("Unknown option $key.");
    }
    if (in_array($key, ['keep-public-domain', 'verbose']) && $value !== true) {
        printHelp("Option $key does not take a value.");
    }
    if (in_array($key, ['title']) && !is_string($value)) {
        printHelp("Option $key must have exactly one value.");
    }
}

if (isset($options['exclude']) && !is_array($options['exclude'])) {
    $options['exclude'] = [$options['exclude']];
}

$runner = new LicenseUseGenerator($sources, $options['keep-public-domain'] ?? false, $options['title'] ?? 'Open Source Licenses', $options['exclude'] ?? []);
try {
    $runner->run();
    if (isset($options['verbose'])) {
        $runner->printDebugOutput();
    }
}
catch (UnknownPackageFileFormatException $e) {
    StdErr::print($e->getMessage());
}

function printHelp(?string $error = null): never {
    global $argv;

    $file = $argv[0] ?? __FILE__;

    if ($error) {
        StdErr::print("\033[31m$error\033[37m" . PHP_EOL);
    }

    StdErr::print("Usage: $file --keep-public-domain --title= --exclude=* [--] {source}");
    StdErr::print('  You can use multiple source files. Options:');
    StdErr::print('  --keep-public-domain    Include all public domain licenses in the output HTML');
    StdErr::print('  --title=<title>         Use <title> as the page title');
    StdErr::print('  --exclude=<exclude>     Exclude package <exclude> from the output');
    StdErr::print('  --verbose               Output information about skipped/failed packages');
    die();
}