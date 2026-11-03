<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** رسالة من نموذج «اتصل بنا» (أو من زر «تواصل مع الموظف المسؤول» في المساعد الذكي). */
class ContactMessage extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'source'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    /** «رسالة جديدة / رسالتان جديدتان / 3 رسائل جديدة / 11 رسالة جديدة» */
    public static function countLabel(int $n): string
    {
        return match (true) {
            $n === 1 => 'رسالة جديدة',
            $n === 2 => 'رسالتان جديدتان',
            $n >= 3 && $n <= 10 => "{$n} رسائل جديدة",
            default => "{$n} رسالة جديدة",
        };
    }

    /** الرسائل التي وصلت بعد آخر اطلاع للمستخدم على صندوق الرسائل */
    public function scopeNewFor(Builder $query, User $user): Builder
    {
        return $user->messages_seen_at ? $query->where('created_at', '>', $user->messages_seen_at) : $query;
    }
}
