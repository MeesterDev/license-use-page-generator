<?php

namespace MeesterDev\LicenseUsePageGenerator\Licenses;

use League\CommonMark\CommonMarkConverter;
use MeesterDev\FileWrapper\File;

class HtmlGenerator {
    private static function getSimpleLicense(File $file): string {
        return view('license-generator::license', ['text' => $file->contents()]);
    }

    private static function getHtmlLicense(File $file): string {
        return view('license-generator::license', ['text' => strip_tags($file->contents())]);
    }

    private static function getMarkdownLicense(File $file): string {
        $converter = new CommonMarkConverter(
            [
                'html_input'         => 'strip',
                'allow_unsafe_links' => false,
            ]
        );

        return $converter->convertToHtml($file->contents());
    }

    public static function getHtml(File $file): ?string {
        if ($file->isReadable()) {
            switch (strtolower($file->getExtension())) {
                case 'txt':
                    return static::getSimpleLicense($file);
                case 'md':
                case 'mkd':
                case 'markdown':
                    return static::getMarkdownLicense($file);
                case 'htm':
                case 'html':
                    return static::getHtmlLicense($file);
                default:
                    // we don't know the exact format... but it's _a_ license file
                    return static::getSimpleLicense($file);
            }
        }

        return null;
    }
}