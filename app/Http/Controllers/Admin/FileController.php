<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManagedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FileController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        return view('admin.files', [
            'items' => ManagedFile::latest()->get()
                ->filter(fn (ManagedFile $f) => $q === '' || str_contains(mb_strtolower($f->name), mb_strtolower($q))),
            'total' => ManagedFile::count(),
            'highlight' => (int) $request->query('highlight'),
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'max:10'],
            'files.*' => ['file', 'max:'.config('kasp.max_upload_kb'), 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt'],
        ]);

        foreach ($request->file('files') as $file) {
            ManagedFile::create([
                'name' => $file->getClientOriginalName(),
                'path' => $file->store('uploads', 'public'),
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('admin.files.index')->with('status', 'تم رفع الملفات');
    }

    public function destroy(ManagedFile $file): RedirectResponse
    {
        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('status', 'تم حذف الملف');
    }
}
