<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * مستخدمو لوحة التحكم — الصلاحيات: admin / editor / viewer
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'editor', 'viewer'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** إنشاء وتعديل المحتوى: المدير والمحرّر */
    public function canEditContent(): bool
    {
        return $this->is_active && in_array($this->role, ['admin', 'editor'], true);
    }

    /** الحذف وإدارة المستخدمين: المدير فقط */
    public function canDeleteContent(): bool
    {
        return $this->is_active && $this->isAdmin();
    }

    public function roleLabel(): string
    {
        return config("kasp.roles.{$this->role}.label", $this->role);
    }

    public function initial(): string
    {
        return mb_substr(trim($this->name), 0, 1) ?: '؟';
    }
}
