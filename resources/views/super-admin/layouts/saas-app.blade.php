<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">


    <title> {{ __(isset($seoDetail) ? $seoDetail->seo_title : $pageTitle) }} | {{ global_setting()->global_app_name}}</title>

    <meta name="description" content="{{ isset($seoDetail) ? $seoDetail->seo_description : '' }}">
    <meta name="author" content="{{ isset($seoDetail) ? $seoDetail->seo_author : '' }}">
    <meta name="keywords" content="{{ isset($seoDetail) ? $seoDetail->seo_keywords : '' }}">

    <meta property="og:title" content="{{ isset($seoDetail) ? $seoDetail->seo_title : '' }}">
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="{{global_setting()->global_app_name}}"/>
    <meta property="og:description" content="{{ isset($seoDetail) ? $seoDetail->seo_description : '' }}">
    <meta property="og:image" content="{{ isset($seoDetail) ? $seoDetail->og_image_url : '' }}"/>

    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ global_setting()->favicon_url }}">
    <meta name="msapplication-TileImage" content="{{ global_setting()->favicon_url }}">

    <!-- Bootstrap CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('saas/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('saas/vendor/animate-css/animate.min.css') }}">
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('saas/vendor/slick/slick.css') }}">
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('saas/vendor/slick/slick-theme.css') }}">
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('saas/fonts/flaticon/flaticon.css') }}">
    <link href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" rel="stylesheet">
    <!-- Template CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('saas/css/main.css') }}">
    <!-- Template Font Family  -->
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Rubik:wght@400;500&display=swap"
        rel="stylesheet">
    <link type="text/css" rel="stylesheet" media="all"
          href="{{ asset('saas/vendor/material-design-iconic-font/css/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('saas/css/cookieconsent.css') }}" media="print" onload="this.media='all'">

    <script src="https://www.google.com/recaptcha/api.js"></script>
    <style>

        {!! $frontDetail->custom_css_theme_two !!}
        :root {
            --main-color: {{ $frontDetail->primary_color }};
            --main-home-background: {{ $frontDetail->light_color }};
        }

        /*To be removed to next 3.6.8 update. Added so as cached main.css to show background image on load*/
        .section-hero .banner::after {
            position: absolute;
            content: '';
            left: 0;
            top: 0;
            z-index: -1;
            width: 100%;
            height: 100%;
            background: #fff;
            background: linear-gradient(to bottom, #ffffff 0%, #fffdfd 50%, #fff2f3 100%);
            opacity: 0.95;
            padding-bottom: 400px;
        }

        .section-hero .banner {
            background: url("{{ global_setting()->login_background_url }}") center center/cover no-repeat !important;
        }

        .breadcrumb-section::after {
            background: url("{{ global_setting()->login_background_url }}") center center/cover no-repeat !important;
        }

        .help-block {
            color: #8a1f11 !important;
        }

        .login-box h5 {
            padding: 35px 40px 15px;
            font-size: 21px;
            text-align: center;
            font-weight: 600;
        }

        @media (max-width: 767px) {
            .login-box form {
                padding: 10px;
            }

            .input-group-text {
                font-size: 13px;
            }

            .login-box h5 {
                padding: 35px 15px 15px;
                font-size: 21px;
                text-align: center;
                font-weight: 600;
            }
        }
    </style>

    @foreach ($frontWidgets as $item)
        @if(!is_null($item->header_script))
            {!! $item->header_script !!}
        @endif

    @endforeach

    @stack('head-script')

</head>

<body id="home">

    @include('super-admin.sections.saas.saas_header')

    @yield('header-section')

    @yield('content')

    @include('super-admin.saas.section.cta')

    @include('super-admin.sections.saas.saas_footer')

    <!-- Scripts -->
    <script src="{{ asset('saas/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('saas/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('saas/vendor/slick/slick.min.js') }}"></script>
    <script src="{{ asset('saas/vendor/wowjs/wow.min.js') }}"></script>
    <script src="{{ asset('saas/js/main.js') }}"></script>
    <script src="{{ asset('front/plugin/froiden-helper/helper.js') }}"></script>

    <!-- Global Required JS -->
    @foreach ($frontWidgets as $item)
        @if(!is_null($item->footer_script))
            {!! $item->footer_script !!}
        @endif

    @endforeach

    @stack('footer-script')

    @includeIf('super-admin.sections.cookie-consent')
</body>
</html>
