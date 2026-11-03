<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** تقييم المستفيد لأداء الخدمة (البحث والمساعد الذكي). */
class ServiceRating extends Model
{
    public const RATINGS = ['satisfied', 'unsatisfied', 'needs_improvement'];

    public const SOURCES = ['widget', 'assistant', 'search'];

    protected $fillable = ['rating', 'notes', 'source', 'locale'];
}
