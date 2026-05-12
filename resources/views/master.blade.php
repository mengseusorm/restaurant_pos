<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- REQUIRED META TAGS -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(app()->environment(['local', 'development']))
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    @endif

    <!-- FONTS -->
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/fontawesome/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/lab/lab.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/typography/public/public.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/typography/rubik/rubik.css') }}">

    <!-- CUSTOM STYLE -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/css/custom.css') }}?v={{ filemtime(public_path('themes/default/css/custom.css')) }}">
    <!-- PAGE TITLE -->
    <title>{{ Settings::group('company')->get('company_name') }}</title>

    <!-- FAV ICON -->
    <link rel="icon" type="image" href="{{ $favicon }}">


    @if (!blank($analytics))
        @foreach ($analytics as $analytic)
            @if (!blank($analytic->analyticSections))
                @foreach ($analytic->analyticSections as $section)
                    @if ($section->section == \App\Enums\AnalyticSection::HEAD)
                        {!! $section->data !!}
                    @endif
                @endforeach
            @endif
        @endforeach
    @endif
</head>

<body>
@if (!blank($analytics))
    @foreach ($analytics as $analytic)
        @if (!blank($analytic->analyticSections))
            @foreach ($analytic->analyticSections as $section)
                @if ($section->section == \App\Enums\AnalyticSection::BODY)
                    {!! $section->data !!}
                @endif
            @endforeach
        @endif
    @endforeach
@endif

<div id="app">
    <default-component/>
</div>

@if (!blank($analytics))
    @foreach ($analytics as $analytic)
        @if (!blank($analytic->analyticSections))
            @foreach ($analytic->analyticSections as $section)
                @if ($section->section == \App\Enums\AnalyticSection::FOOTER)
                    {!! $section->data !!}
                @endif
            @endforeach
        @endif
    @endforeach
@endif

<script>
    const APP_URL = "{{ env('MIX_HOST') }}";
    const APP_KEY = "{{ env('MIX_API_KEY') }}";
    const GOOGLE_TOKEN = "{{ env('MIX_GOOGLE_MAP_KEY') }}";
    const APP_DEMO = "{{ env('MIX_DEMO') }}";
</script>

<script src="{{ mix('js/app.js') }}"></script>
<script src="{{ asset('themes/default/js/jquery-v3.7.1.min.js') }}?v={{ filemtime(public_path('themes/default/js/jquery-v3.7.1.min.js')) }}"></script>
<script src="{{ asset('themes/default/js/drawer.js') }}?v={{ filemtime(public_path('themes/default/js/drawer.js')) }}"></script>
<script src="{{ asset('themes/default/js/modal.js') }}?v={{ filemtime(public_path('themes/default/js/modal.js')) }}"></script>
<script src="{{ asset('themes/default/js/customScript.js') }}?v={{ filemtime(public_path('themes/default/js/customScript.js')) }}"></script>
<script src="{{ asset('themes/default/js/jqueryScript.js') }}?v={{ filemtime(public_path('themes/default/js/jqueryScript.js')) }}"></script>
<script src="{{ asset('themes/default/js/tabs.js') }}?v={{ filemtime(public_path('themes/default/js/tabs.js')) }}"></script>
<script src="{{ asset('themes/default/js/jqueryDropdown.js') }}?v={{ filemtime(public_path('themes/default/js/jqueryDropdown.js')) }}"></script>
<script src="{{ asset('themes/default/js/apexcharts/apexcharts.min.js') }}?v={{ filemtime(public_path('themes/default/js/apexcharts/apexcharts.min.js')) }}"></script>
</body>

</html>
