<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{{ $title ?? 'Open Source Licenses' }}</title>
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Raleway:ital@0;1&family=Roboto+Mono:ital@0;1&display=swap" rel="stylesheet">
        <style>
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
                background: #282828;
                color: #FFF;
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
                border-right: 1px solid #000;
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

                position: sticky;
                top: -1rem;
                background: rgb(40,40,40);
                background: linear-gradient(0, rgba(40,40,40,0) 0%, rgb(40,40,40) 0.9rem, rgb(40,40,40) 100%);
                margin: 0;
                padding: 1rem 0;
            }

            blockquote {
                margin: 0 0 0 1rem;
                border-left: 1px solid #CCC;
                padding: 0.5rem 0.5rem 0.5rem 1rem;
                background: #111;
                border-radius: 2px;
            }

            blockquote p:first-child {
                margin-top: 0;
            }

            blockquote p:last-child {
                margin-bottom: 0;
            }

            a:link {
                color: #AAF;
            }

            a:visited {
                color: #F7F;
            }

            a:hover {
                color: #F3F;
            }

            a:active {
                color: #FAA;
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
            @foreach($packages as $i => $package)
                <a href="#{{ $package->name }}--{{ $i }}">{{ $package->name }}</a>
            @endforeach
        </div>
        <div class="content">
            <h1>Open source licenses</h1>
            <?php /** @var $packages MeesterDev\PackageParser\Entities\PackageInformation[] */ ?>
            @foreach($packages as $i => $package)
                <section id="{{ $package->name }}--{{ $i }}">
                <!-- loaded through {{ $package->source }} -->
                <!-- description: {{ $package->description }} -->
                    <h2>
                        @if ($package->homepage)
                            <a target="_blank" href="{{ $package->homepage }}">{{ $package->name }}</a> <span>🔗</span>
                        @else
                            {{ $package->name }}
                        @endif
                    </h2>
                    @if ($package->licenseFileLocation && $licenseHtml = \MeesterDev\LicenseUsePageGenerator\Licenses\HtmlGenerator::getHtml($package->licenseFileLocation))
                        {!! $licenseHtml !!}
                    @elseif (\Illuminate\Support\Facades\View::exists('license-generator::licenses.' . str_replace('.', '_' , $package->licenseType)))
                        @include('license-generator::licenses.' . str_replace('.', '_' , $package->licenseType), ['package' => $package])
                        <small><b>Note:</b> the above license was generated and not supplied by the package maintainer. Some placeholders may be present in the license text.</small>
                    @else
                        License: {{ $package->licenseType }}
                    @endif
                </section>
            @endforeach
        </div>
    </body>
</html>