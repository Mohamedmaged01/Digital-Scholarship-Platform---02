<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** حساب تواصل اجتماعي يظهر أيقونةً في تذييل المنصة. */
class SocialLink extends Model
{
    protected $fillable = ['name', 'platform', 'url', 'sort'];

    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }
}
