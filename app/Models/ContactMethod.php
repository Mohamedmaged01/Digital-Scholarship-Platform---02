<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMethod extends Model
{
    public const ICONS = ['phone', 'mail', 'chat', 'map-pin'];

    protected $fillable = ['label', 'value', 'href', 'icon', 'sort'];

    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }

    public function lucideIcon(): string
    {
        return config("kasp.contact_icons.{$this->icon}", 'phone');
    }
}
