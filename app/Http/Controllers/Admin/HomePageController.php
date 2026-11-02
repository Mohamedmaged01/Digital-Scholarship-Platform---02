<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\SiteSetting;
use App\Support\DefaultContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** الواجهة الرئيسية: العنوان والنص (عربي/إنجليزي)، رابط «ابدأ التقديم»، ومعرض الصور. */
class HomePageController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.home', [
            'settings' => collect(array_keys(config('kasp.home_defaults')))->mapWithKeys(fn ($k) => [$k => SiteSetting::get($k)])->all(),
            'slides' => HeroSlide::ordered()->get(),
            'editing' => $request->filled('edit') ? HeroSlide::find($request->query('edit')) : null,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        SiteSetting::put($request->validate([
            'apply_url' => ['required', 'url:https', 'max:500'],
            'hero_title_ar' => ['required', 'string', 'max:120'],
            'hero_highlight_ar' => ['nullable', 'string', 'max:80'],
            'hero_text_ar' => ['required', 'string', 'max:700'],
            'hero_title_en' => ['nullable', 'string', 'max:120'],
            'hero_highlight_en' => ['nullable', 'string', 'max:80'],
            'hero_text_en' => ['nullable', 'string', 'max:700'],
        ], ['apply_url.url' => 'يجب أن يكون رابط التقديم رابطًا كاملًا يبدأ بـ https://']));

        return redirect()->route('admin.home.index')->with('status', 'تم حفظ نصوص الواجهة');
    }

    public function storeSlide(Request $request): RedirectResponse
    {
        $data = $this->validatedSlide($request);
        if (! $request->hasFile('image_file') && blank($data['image'])) {
            throw ValidationException::withMessages(['image_file' => 'اختر صورة للشريحة أو أدخل مسار صورة']);
        }
        $data['image'] = $this->storeUpload($request) ?? $data['image'];
        HeroSlide::create($data);

        return redirect()->route('admin.home.index')->with('status', 'تمت إضافة الصورة إلى المعرض');
    }

    public function updateSlide(Request $request, HeroSlide $slide): RedirectResponse
    {
        $data = $this->validatedSlide($request);
        $upload = $this->storeUpload($request);
        $data['image'] = $upload ?? ($data['image'] ?: $slide->image);
        if ($data['image'] !== $slide->image && $slide->hasUploadedImage()) {
            File::delete(public_path($slide->image));
        }
        $slide->update($data);

        return redirect()->route('admin.home.index')->with('status', 'تم حفظ التعديلات');
    }

    public function destroySlide(HeroSlide $slide): RedirectResponse
    {
        if ($slide->hasUploadedImage()) {
            File::delete(public_path($slide->image));
        }
        $slide->delete();

        return redirect()->route('admin.home.index')->with('status', 'تم حذف الصورة من المعرض');
    }

    public function resetSlides(): RedirectResponse
    {
        DefaultContent::resetHeroSlides();

        return redirect()->route('admin.home.index')->with('status', 'تمت استعادة المعرض الافتراضي');
    }

    private function validatedSlide(Request $request): array
    {
        $data = $request->validate([
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            // صورة من صور المنصة (images/...) أو رابط https
            'image' => ['nullable', 'string', 'max:500', 'regex:/^(\/?images\/|\/?uploads\/|https:\/\/)\S+$/i'],
            'title_ar' => ['required', 'string', 'max:120'],
            'title_en' => ['nullable', 'string', 'max:120'],
            'subtitle_ar' => ['nullable', 'string', 'max:160'],
            'subtitle_en' => ['nullable', 'string', 'max:160'],
            // قسم في الصفحة (#tracks) أو صفحة في المنصة (tracks/rowad) أو رابط https
            'link' => ['nullable', 'string', 'max:500', 'regex:/^(#[\w-]+|\/?[\w\-\/]+|https:\/\/\S+)$/i'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'image_file.image' => 'الملف المختار ليس صورة', 'image_file.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت',
            'image.regex' => 'مسار الصورة يبدأ بـ images/ أو رابط https://', 'link.regex' => 'الرابط: #قسم أو tracks/rowad أو https://…',
        ]);

        return [
            'image' => ltrim((string) ($data['image'] ?? ''), '/'),
            'title_ar' => $data['title_ar'], 'title_en' => $data['title_en'] ?? '',
            'subtitle_ar' => $data['subtitle_ar'] ?? '', 'subtitle_en' => $data['subtitle_en'] ?? '',
            'link' => $data['link'] ?? '',
            'overlay' => $request->boolean('overlay'),
            'is_active' => $request->boolean('is_active'),
            'sort' => $data['sort'] ?? 0,
        ];
    }

    /** الصورة تُحفظ داخل public مباشرة فتعمل دون storage:link على الاستضافة */
    private function storeUpload(Request $request): ?string
    {
        if (! $request->hasFile('image_file')) {
            return null;
        }
        $file = $request->file('image_file');
        $name = Str::lower(Str::random(24)).'.'.$file->guessExtension();
        $file->move(public_path(HeroSlide::UPLOAD_DIR), $name);

        return HeroSlide::UPLOAD_DIR.'/'.$name;
    }
}
