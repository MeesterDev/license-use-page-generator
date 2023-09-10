<?php

use MeesterDev\PackageParser\Entities\PackageInformation;

function escape(string $s): string {
    return htmlspecialchars($s);
}

/**
 * @var PackageInformation[] $packages
 */

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= $title ?? 'Open Source Licenses' ?></title>
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Raleway:ital@0;1&family=Roboto+Mono:ital@0;1&display=swap" rel="stylesheet">
        <style>
            :root {
                --text-color: #FFF;
                --background-color: #282828;
                --background-color-transparent: #28282800;
                --background-color-blockquote: #111;
                --border-color: #000;
                --border-color-blockquote: #CCC;
                --link-link-color: #AAF;
                --link-visited-color: #F7F;
                --link-hover-color: #F3F;
                --link-active-color: #FAA;
            }

            @media (prefers-color-scheme: light) {
                :root {
                    --text-color: #000;
                    --background-color: #FFF;
                    --background-color-transparent: #FFFFFF00;
                    --background-color-blockquote: #CCC;
                    --border-color: #888;
                    --border-color-blockquote: #555;
                    --link-link-color: #88C;
                    --link-visited-color: #D5D;
                    --link-hover-color: #D1D;
                    --link-active-color: #D88;
                }
            }

            * {
                box-sizing: border-box;
            }

            body {
                display: grid;
                grid-template-columns: 24rem 1fr;
                padding: 0;
                margin: 0;
                font-family: 'Raleway', sans-serif;
                overflow: hidden;
                background: var(--background-color);
                color: var(--text-color);
            }

            pre, code {
                font-family: 'Roboto Mono', monospace;
                white-space: pre-wrap;
            }

            body > div {
                padding: 1rem;
                height: 100vh;
                overflow: auto;
            }

            div.toc {
                border-right: 1px solid var(--border-color);
            }

            div.toc a {
                display: block;
                margin: 0.5rem 0;
                text-decoration: none;
            }

            div.toc a:hover {
                text-decoration: underline;
            }

            h2 > span {
                font-size: 11pt;
                vertical-align: 2px;
            }

            section {
                font-size: 12pt;
            }

            section pre, section code {
                font-size: 10pt;
            }

            section h2 {
                font-size: 18pt;
                margin: 0;
                padding: 1rem 0;
            }

            section h2.package-header {
                position: sticky;
                top: -1rem;
                background: var(--background-color);
                background: linear-gradient(0, var(--background-color-transparent) 0%, var(--background-color) 0.9rem, var(--background-color) 100%);
            }

            blockquote {
                margin: 0 0 0 1rem;
                border-left: 1px solid var(--border-color-blockquote);
                padding: 0.5rem 0.5rem 0.5rem 1rem;
                background: var(--background-color-blockquote);
                border-radius: 2px;
            }

            blockquote p:first-child {
                margin-top: 0;
            }

            blockquote p:last-child {
                margin-bottom: 0;
            }

            a:link {
                color: var(--link-link-color);
            }

            a:visited {
                color: var(--link-visited-color);
            }

            a:hover {
                color: var(--link-hover-color);
            }

            a:active {
                color: var(--link-active-color);
            }

            @media screen and (max-width: 768px) {
                body {
                    display: block;
                }

                div.toc {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="toc">
            <?php foreach ($packages as $i => $package) { ?>
                <a href="#<?= escape($package->name) ?>--<?= $i ?>"><?= escape($package->name) ?></a>
            <?php } ?>
        </div>
        <div class="content">
            <h1>Open source licenses</h1>
            <?php foreach ($packages as $i => $package) { ?>
                <section id="<?= escape($package->name) ?>--<?= escape($i) ?>">
                    <!-- loaded through <?= escape($package->source) ?> -->
                    <!-- description: <?= $package->description ? escape($package->description) : '' ?> -->
                    <h2 class="package-header">
                        <?php if ($package->homepage) { ?>
                            <a target="_blank" href="<?= escape($package->homepage) ?>"><?= escape($package->name) ?></a> <span>&#128279;</span>
                        <?php } else { ?>
                            <?= escape($package->name) ?>
                        <?php } ?>
                    </h2>
                    <?php if ($package->licenseFileLocation && $licenseHtml = \MeesterDev\LicenseUsePageGenerator\Licenses\HtmlGenerator::getHtml($package->licenseFileLocation)) { ?>
                        <?= $licenseHtml ?>
                    <?php } elseif (file_exists($file = __DIR__ . DIRECTORY_SEPARATOR . 'licences' . DIRECTORY_SEPARATOR . str_replace('.', '_', $package->licenseType) . '.html')) { ?>
                        <?php include $file; ?>
                        <small><b>Note:</b> the above license was generated and not supplied by the package maintainer. Some placeholders may be present in the license text.</small>
                    <?php } else { ?>
                        License: <?= escape($package->licenseType) ?>
                    <?php } ?>
                </section>
            <?php } ?>
        </div>
    </body>
</html>