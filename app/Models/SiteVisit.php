<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
protected $fillable = [
    'visit_date',
    'visitor_key',
    'page_views',
    'os',
    'browser',
];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
        ];
    }
}
