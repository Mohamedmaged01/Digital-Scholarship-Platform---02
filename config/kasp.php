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

    'faqs' => $site['faqs'],
    'nav' => $site['navLinks'],
    // القيم الافتراضية فقط — تُدار من لوحة التحكم (جدول contact_methods)
    'contact_methods' => $site['contactMethods'],
    'suggested_questions' => $site['suggestedQuestions'],

    /* الدرجات الست المعتمدة (§I) — تطابق جدول academic_degrees. */
    'degrees' => [
        'bachelor' => 'بكالوريوس',
        'master' => 'ماجستير',
        'phd' => 'دكتوراه',
        'fellowship' => 'زمالة',
        'diploma' => 'دبلوم',
        'professional_doctorate' => 'دكتوراه مهنية',
    ],

    /* حالة التقديم على المسار — حلّت محل "أدنى معدل مطلوب" (§F). */
    'application_statuses' => [
        'open' => 'متاح للتقديم',
        'not_started' => 'لم يبدأ التقديم',
        'closed' => 'انتهت فترة التقديم',
        'unavailable' => 'غير متاح حاليًا',
    ],

    'regions' => [
        'na' => 'أمريكا الشمالية',
        'europe' => 'أوروبا',
        'asia' => 'آسيا',
        'oceania' => 'أوقيانوسيا',
    ],

    /* حالة برامج مسار واعد */
    'waed_statuses' => [
        'open' => ['label' => 'متاح للتقديم', 'class' => 'border-emerald-400/60 bg-emerald-500/10 text-emerald-700'],
        'upcoming' => ['label' => 'قريبًا', 'class' => 'border-amber-400/60 bg-amber-500/10 text-amber-700'],
        'closed' => ['label' => 'انتهت فترة التقديم', 'class' => 'border-red-300 bg-red-50 text-red-600'],
        'archived' => ['label' => 'برنامج سابق', 'class' => 'border-slate-300 bg-slate-100 text-slate-500'],
    ],
    'waed_types' => [
        'scholarship' => 'ابتعاث دراسي',
        'coop' => 'تعاوني مزدوج',
        'training' => 'تدريبي',
        'fellowship' => 'زمالة بحثية',
    ],
    'waed_condition_categories' => [
        'general' => 'شرط عام',
        'academic' => 'شرط أكاديمي',
        'age' => 'شرط السن',
        'experience' => 'خبرة مطلوبة',
        'admission' => 'نوع القبول',
        'other' => 'ضابط آخر',
    ],
    'waed_sectors' => [
        'الفضاء والطيران', 'الطاقة والاستدامة', 'السياحة والترفيه', 'التقنية والذكاء الاصطناعي',
        'الصحة والعلوم الحيوية', 'الصناعة والتصنيع', 'المال والاقتصاد',
    ],

    /* قيود التخصصات */
    'constraint_types' => [
        'degree_restriction' => 'قيد الدرجة العلمية',
        'degree_exclusion' => 'استثناء الدرجة',
        'admission_type' => 'نوع القبول',
        'prerequisite_degree' => 'شرط الدرجة السابقة',
        'nationality' => 'قيد الجنسية',
        'age_limit' => 'قيد العمر',
        'experience' => 'خبرة مطلوبة',
        'language_requirement' => 'متطلب لغوي خاص',
        'note' => 'ملاحظة',
    ],
    'constraint_severities' => [
        'required' => ['label' => 'إلزامي', 'class' => 'border-red-300 bg-red-50 text-red-700'],
        'preferred' => ['label' => 'مُفضَّل', 'class' => 'border-amber-300 bg-amber-50 text-amber-700'],
        'note' => ['label' => 'ملاحظة', 'class' => 'border-blue-200 bg-blue-50 text-blue-700'],
    ],

    'guide_types' => ['pdf' => 'ملف PDF', 'link' => 'رابط إلكتروني', 'document' => 'وثيقة'],
    'guide_statuses' => ['active' => 'نشط', 'archived' => 'مؤرشف', 'draft' => 'مسودة'],

    'contact_icons' => ['phone' => 'phone', 'mail' => 'mail', 'chat' => 'message-circle', 'map-pin' => 'map-pin'],
    'statistic_locations' => ['general' => 'شريط الإحصاءات', 'hero' => 'الواجهة الرئيسية'],

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
        'admin.guides.index' => ['label' => 'الأدلة الاسترشادية', 'icon' => 'file-text', 'count' => 'guides'],
        'admin.waed.index' => ['label' => 'برامج واعد', 'icon' => 'satellite', 'count' => 'waed'],
        'admin.relations.index' => ['label' => 'إدارة العلاقات', 'icon' => 'link-2', 'admin_only' => true],
        'admin.stations.index' => ['label' => 'خارطة الطريق', 'icon' => 'route', 'count' => 'stations'],
        'admin.files.index' => ['label' => 'مركز الملفات', 'icon' => 'cloud-upload', 'count' => 'files'],
        'admin.statistics.index' => ['label' => 'الإحصاءات والأرقام', 'icon' => 'list-ordered', 'count' => 'statistics'],
        'admin.contact.index' => ['label' => 'وسائل الاتصال', 'icon' => 'phone', 'count' => 'contact'],
        'admin.pages.index' => ['label' => 'السياسات والشروط', 'icon' => 'scale', 'count' => 'pages'],
        'admin.users.index' => ['label' => 'المستخدمون', 'icon' => 'users', 'count' => 'users', 'admin_only' => true],
    ],

    // الحد الأقصى لحجم الملف المرفوع (كيلوبايت)
    'max_upload_kb' => 10240,
];
