<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('برنامج خادم الحرمين الشريفين للابتعاث | البوابة التعريفية والإرشادية'))</title>
    <meta name="description" content="{{ __('بوابة تعريفية وإرشادية لبرنامج خادم الحرمين الشريفين للابتعاث الخارجي: المسارات وشروطها، والمؤسسات التعليمية، ورحلة المستفيد. المعلومات تعريفية ويُرجع للمصادر الرسمية.') }}">
    <meta property="og:title" content="{{ __('برنامج خادم الحرمين الشريفين للابتعاث | البوابة التعريفية والإرشادية') }}">
    <meta property="og:description" content="{{ __('بوابة تعريفية وإرشادية لبرنامج خادم الحرمين الشريفين للابتعاث الخارجي: المسارات وشروطها، والمؤسسات التعليمية، ورحلة المستفيد. المعلومات تعريفية ويُرجع للمصادر الرسمية.') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon-64.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta property="og:image" content="{{ asset('images/brand/kasp-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @php
        // نصوص تولّدها الواجهة في JavaScript — تُترجم من ملف اللغة نفسه
        $jsStrings = collect([
            'لم أحدد بعد — اعرض كل المسارات', ':count تخصص محدد في هذا المجال',
            'تخصصات محددة في هذا المجال — راجع الشروط الخاصة', ':scope — تحقق من الدليل الاسترشادي',
            'تعذّر الوصول إلى المساعد حاليًا. يمكنك التواصل مع الإدارة مباشرة عبر القنوات التالية:',
        ])->mapWithKeys(fn ($k) => [$k => __($k)]);
    @endphp
    <script>window.KASP_I18N = @json(app()->getLocale() === 'en' ? $jsStrings : new stdClass);</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body-class', 'bg-cream font-sans text-ink antialiased')">
    @yield('content')
</body>
</html>
