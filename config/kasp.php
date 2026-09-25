<?php

/*
|--------------------------------------------------------------------------
| إعدادات منصّة برنامج خادم الحرمين الشريفين للابتعاث
|--------------------------------------------------------------------------
| المحتوى الثابت (الإحصاءات، الأسئلة الشائعة، روابط التنقل…) يُقرأ من
| database/data/site.json — المصدر نفسه الذي يستخدمه مُعبّئ قاعدة البيانات.
*/

$site = json_decode(file_get_contents(database_path('data/site.json')), true);

return [

    'stats' => $site['stats'],
    'marquee' => $site['marqueeUnis'],
    'faqs' => $site['faqs'],
    'nav' => $site['navLinks'],
    'contact_methods' => $site['contactMethods'],
    'suggested_questions' => $site['suggestedQuestions'],

    'degrees' => [
        'bachelor' => 'بكالوريوس',
        'master' => 'ماجستير',
        'phd' => 'دكتوراه',
    ],

    'regions' => [
        'na' => 'أمريكا الشمالية',
        'europe' => 'أوروبا',
        'asia' => 'آسيا',
        'oceania' => 'أوقيانوسيا',
    ],

    'news_categories' => json_decode(file_get_contents(database_path('data/news_categories.json')), true),

    'roles' => [
        'admin' => [
            'label' => 'مدير النظام',
            'description' => 'صلاحيات كاملة: إدارة المحتوى والمستخدمين وكلمات المرور',
            'badge' => 'border-gold-500/50 bg-gold-500/15 text-gold-700',
            'icon' => 'shield-check',
        ],
        'editor' => [
            'label' => 'محرّر المحتوى',
            'description' => 'إضافة وتعديل جميع عناصر المحتوى دون الحذف أو إدارة المستخدمين',
            'badge' => 'border-forest-600/40 bg-forest-50 text-forest-700',
            'icon' => 'user-cog',
        ],
        'viewer' => [
            'label' => 'مطّلع',
            'description' => 'استعراض المحتوى فقط دون أي تعديل',
            'badge' => 'border-slate-400/40 bg-slate-100 text-slate-600',
            'icon' => 'user-round',
        ],
    ],

    /* أقسام لوحة التحكم: route => [label, icon, adminOnly] */
    'admin_sections' => [
        'admin.dashboard' => ['label' => 'نظرة عامة', 'icon' => 'layout-dashboard'],
        'admin.news.index' => ['label' => 'الأخبار والإعلانات', 'icon' => 'newspaper', 'count' => 'news'],
        'admin.kb.index' => ['label' => 'قاعدة المعرفة', 'icon' => 'book-open-text', 'count' => 'kb'],
        'admin.pending.index' => ['label' => 'أسئلة بلا إجابة', 'icon' => 'inbox', 'count' => 'pending'],
        'admin.universities.index' => ['label' => 'الجامعات', 'icon' => 'globe', 'count' => 'universities'],
        'admin.tracks.index' => ['label' => 'المسارات الدراسية', 'icon' => 'graduation-cap', 'count' => 'tracks'],
        'admin.stations.index' => ['label' => 'خارطة الطريق', 'icon' => 'route', 'count' => 'stations'],
        'admin.files.index' => ['label' => 'مركز الملفات', 'icon' => 'cloud-upload', 'count' => 'files'],
        'admin.users.index' => ['label' => 'المستخدمون', 'icon' => 'users', 'count' => 'users', 'admin_only' => true],
    ],

    // الحد الأقصى لحجم الملف المرفوع (كيلوبايت)
    'max_upload_kb' => 10240,
];
