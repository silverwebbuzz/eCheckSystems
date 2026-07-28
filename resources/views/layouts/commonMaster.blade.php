<!DOCTYPE html>
@php
    $menuFixed =
        $configData['layout'] === 'vertical'
            ? $menuFixed ?? ''
            : ($configData['layout'] === 'front'
                ? ''
                : $configData['headerType']);
    $navbarType =
        $configData['layout'] === 'vertical'
            ? $configData['navbarType'] ?? ''
            : ($configData['layout'] === 'front'
                ? 'layout-navbar-fixed'
                : '');
    $isFront = ($isFront ?? '') == true ? 'Front' : '';
    $contentLayout = isset($container) ? ($container === 'container-xxl' ? 'layout-compact' : 'layout-wide') : '';
@endphp

<html lang="{{ session()->get('locale') ?? app()->getLocale() }}"
    class="{{ $configData['style'] }}-style {{ $contentLayout ?? '' }} {{ $navbarType ?? '' }} {{ $menuFixed ?? '' }} {{ $menuCollapsed ?? '' }} {{ $menuFlipped ?? '' }} {{ $menuOffcanvas ?? '' }} {{ $footerFixed ?? '' }} {{ $customizerHidden ?? '' }}"
    dir="{{ $configData['textDirection'] }}" data-theme="{{ $configData['theme'] }}"
    data-assets-path="{{ asset('/assets') . '/' }}" data-base-url="{{ url('/') }}" data-framework="laravel"
    data-template="{{ $configData['layout'] . '-menu-' . $configData['themeOpt'] . '-' . $configData['styleOpt'] }}"
    data-style="{{ $configData['styleOptVal'] }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title') |
        {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }} -
        {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
    </title>
    <meta name="description"
        content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
    <meta name="keywords"
        content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
    <!-- laravel CRUD token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />


    <!-- Include Styles -->
    <!-- $isFront is used to append the front layout styles only on the front layout otherwise the variable will be blank -->
    @include('layouts/sections/styles' . $isFront)

    <!-- Include Scripts for customizer, helper, analytics, config -->
    <!-- $isFront is used to append the front layout scriptsIncludes only on the front layout otherwise the variable will be blank -->
    @include('layouts/sections/scriptsIncludes' . $isFront)

    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"> --}}

    {{-- <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <link type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/south-street/jquery-ui.css"
        rel="stylesheet">

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


    {{-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}

    <script type="text/javascript" src="{{ asset('assets/js/signature.js') }}"></script>



    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/signature.css') }}">
    <style>
        table thead th {
            text-transform: none !important;
        }

        .pricing-table {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 50px 20px;
        }

        .pricing-card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 20px;
            width: 320px;
            overflow: hidden;
            position: relative;
        }

        .pricing-card.popular::before {
            content: "POPULAR";
            background-color: #000000;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 5px 10px;
            position: absolute;
            top: 13px;
            right: -25px;
            border-radius: 3px;
            transform: rotate(45deg);
            width: 100px;
        }

        .pricing-card h3 {
            background-color: #00a7cf;
            color: #ffffff;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .price {
            font-size: 40px;
            color: #7e57c2;
            margin: 10px 0;
            font-weight: 600;
        }

        .price span {
            font-size: 16px;
            color: #555;
        }

        .features {
            list-style: none;
            padding: 0;
            margin: 30px 0;
            text-align: left;
        }

        .features li {
            margin: 15px 0;
            color: #555;
        }

        .features li::before {
            content: "\2713";
            color: #4caf50;
            font-weight: bold;
            margin-right: 10px;
        }

        .pricing-card:hover {
            transform: scale(1.05);
            transition: 0.3s ease-in-out;
        }

        .pricing-card .plan-button {
            background-color: #7367f0 !important;
            color: #fff !important;
            border: none !important;
            border-radius: 5px !important;
            padding: 10px 15px !important;
            font-size: 1em !important;
            cursor: pointer !important;
            transition: 0.3s !important;
            text-decoration: none !important;
            margin-top: 20px !important;
        }

        .pricing-card .plan-button:hover {
            background-color: #7367f0 !important;
        }

        .selected-plan {
            border: 5px solid green !important;
            border-radius: 20px !important;
        }

        .current-plan {
            font-weight: bold;
            color: green;
            font-size: 16px;
            border: 1px solid green;
            padding: 10px;
        }

        #payment-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255,255,255,0.7);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.spinner {
    border: 6px solid #f3f3f3;
    border-top: 6px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.animated-alert {
            display: block;
            animation: shake 1.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-20px); }
            50% { transform: translateX(20px); }
            75% { transform: translateX(-20px); }
        }
    </style>

</head>

<body>

    <!-- Layout Content -->
    @yield('layoutContent')
    <!--/ Layout Content -->



    <!-- Include Scripts -->
    <!-- $isFront is used to append the front layout scripts only on the front layout otherwise the variable will be blank -->
    @include('layouts/sections/scripts' . $isFront)
    <div id="payment-loader" style="display:none;">
        <div class="spinner"></div>
         <p class="m-0 text-dark fs-5" style="margin-left: 20px !important;">Please hold on while we complete your transaction. Do not refresh or close the page.</p>
    </div>
</body>

</html>
