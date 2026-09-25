<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'برنامج خادم الحرمين الشريفين للابتعاث | المنصة الرسمية')</title>
    <meta name="description" content="البوابة الرقمية الموحدة لبرنامج خادم الحرمين الشريفين للابتعاث الخارجي: استكشاف المسارات، الفرز بالذكاء الاصطناعي، الجامعات العالمية، ومتابعة رحلة الابتعاث برؤية 2030.">
    <meta property="og:title" content="برنامج خادم الحرمين الشريفين للابتعاث | المنصة الرسمية">
    <meta property="og:description" content="البوابة الرقمية الموحدة لبرنامج خادم الحرمين الشريفين للابتعاث الخارجي: استكشاف المسارات، الفرز بالذكاء الاصطناعي، الجامعات العالمية، ومتابعة رحلة الابتعاث برؤية 2030.">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body-class', 'bg-cream font-sans text-ink antialiased')">
    @yield('content')
</body>
</html>
