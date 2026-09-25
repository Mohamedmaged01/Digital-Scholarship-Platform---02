# منصّة برنامج خادم الحرمين الشريفين للابتعاث — Laravel

تطبيق Laravel 12 متكامل (Blade + Tailwind CSS 4 + Alpine.js) يحل محل نسخة
React/Express السابقة. الواجهة العامة والمحتوى ولوحة التحكم كلها تُعرض من الخادوم
وتُحفظ في قاعدة البيانات مباشرة.

## المتطلبات

- PHP 8.2+ مع امتدادات `pdo_sqlite` (أو `pdo_pgsql`) و`mbstring` و`intl` و`fileinfo`
- Composer 2
- Node.js 20+

## التشغيل محليًا

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # على ويندوز: type nul > database\database.sqlite
php artisan migrate --seed
php artisan storage:link
npm run build                          # أو npm run dev أثناء التطوير
php artisan serve                      # http://127.0.0.1:8000
```

### حسابات تجريبية (من `DatabaseSeeder`)

| الدور | البريد | كلمة المرور |
| ----- | ------ | ----------- |
| مدير النظام | admin@kasp.gov.sa | Admin@2026 |
| محرّر المحتوى | editor@kasp.gov.sa | Editor@2026 |
| مطّلع | viewer@kasp.gov.sa | Viewer@2026 |

تظهر هذه الحسابات في صفحة الدخول فقط عندما يكون `APP_ENV=local`. **غيّر كلمات
المرور قبل أي نشر.**

### PostgreSQL (اختياري)

```bash
docker compose up -d
# في .env: DB_CONNECTION=pgsql مع بيانات الاتصال الموجودة في .env.example
php artisan migrate --seed
```

## البنية

| المسار | الوصف |
| ------ | ----- |
| `routes/web.php` | كل المسارات: الواجهة، المصادقة، لوحة التحكم |
| `app/Http/Controllers/Admin/*` | متحكمات لوحة التحكم (الأخبار، المعرفة، الجامعات، المسارات، المحطات، الملفات، المستخدمون) |
| `app/Support/Assistant.php` | محرّك مطابقة أسئلة المساعد الذكي مع قاعدة المعرفة |
| `app/Support/SiteSearch.php` | البحث الفوري في المنصة (Ctrl+K) |
| `app/Support/DefaultContent.php` | المحتوى الافتراضي — يستخدمه المُعبّئ وأزرار «استعادة الافتراضي» |
| `database/data/*.json` | بيانات المسارات والجامعات والمحطات والأخبار والمعرفة الافتراضية |
| `config/kasp.php` | المحتوى الثابت (الإحصاءات، الأسئلة الشائعة…) والأدوار وأقسام اللوحة |
| `resources/views/home/*` | أقسام الصفحة الرئيسية |
| `resources/views/admin/*` | صفحات لوحة التحكم |
| `resources/js/app.js` | مكوّنات Alpine (التصفية، المطابقة، المحادثة، البحث، الحركات) |

## الصلاحيات

| الإجراء | مدير | محرّر | مطّلع |
| ------- | :--: | :---: | :---: |
| استعراض لوحة التحكم | ✓ | ✓ | ✓ |
| إضافة وتعديل المحتوى ورفع الملفات | ✓ | ✓ | — |
| الحذف واستعادة الافتراضي | ✓ | — | — |
| إدارة المستخدمين | ✓ | — | — |

تُطبَّق الصلاحيات في الخادوم عبر Gates (`edit-content` و`delete-content`
و`manage-users`) في `AppServiceProvider`، وتُخفي الواجهة الأزرار غير المسموحة.
يُسجَّل خروج أي حساب يُوقف أثناء جلسته عند طلبه التالي.

## الاختبارات

```bash
php artisan test
```
