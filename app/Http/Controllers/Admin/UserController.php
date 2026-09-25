<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $all = User::orderByRaw("case role when 'admin' then 0 when 'editor' then 1 else 2 end")->orderBy('created_at')->get();

        return view('admin.users', [
            'items' => $all->filter(fn (User $u) => $q === '' || str_contains("{$u->name} {$u->email} {$u->roleLabel()}", $q)),
            'stats' => [
                'الإجمالي' => $all->count(),
                'مديرون' => $all->where('role', 'admin')->count(),
                'محرّرون' => $all->where('role', 'editor')->count(),
                'مطّلعون' => $all->where('role', 'viewer')->count(),
                'نشطون' => $all->where('is_active', true)->count(),
            ],
            'editing' => $request->filled('edit') ? User::find($request->query('edit')) : null,
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request));

        return redirect()->route('admin.users.index')->with('status', 'تمت إضافة المستخدم');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && ($data['role'] !== $user->role || ! $data['is_active'])) {
            throw ValidationException::withMessages(['role' => 'لا يمكنك تغيير صلاحية حسابك الحالي أو إيقافه']);
        }
        $this->guardLastAdmin($user, $data['role'], $data['is_active']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'تم حفظ التعديلات');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'لا يمكنك إيقاف حسابك الحالي']);
        }
        $this->guardLastAdmin($user, $user->role, ! $user->is_active);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active ? 'تم تفعيل الحساب' : 'تم إيقاف الحساب');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'لا يمكنك حذف حسابك الحالي أثناء استخدامك للجلسة']);
        }
        $this->guardLastAdmin($user, 'viewer', false);
        $user->delete();

        return back()->with('status', 'تم حذف الحساب');
    }

    /** ضمان بقاء حساب مدير نشط واحد على الأقل */
    private function guardLastAdmin(User $user, string $newRole, bool $newActive): void
    {
        $wasActiveAdmin = $user->role === 'admin' && $user->is_active;
        $staysActiveAdmin = $newRole === 'admin' && $newActive;

        if ($wasActiveAdmin && ! $staysActiveAdmin
            && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'يجب أن يبقى حساب مدير نشط واحد على الأقل في النظام']);
        }
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:190'],
        ], [
            'email.unique' => 'هذا البريد الإلكتروني مستخدم مسبقًا لحساب آخر',
        ]);

        return [...$data, 'email' => mb_strtolower($data['email']), 'is_active' => $request->boolean('is_active')];
    }
}
