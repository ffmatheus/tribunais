<?php

namespace App\Data\Models;

class Proceeding extends BaseModel
{
    protected $fillable = [
        'court',
        'instance',
        'number',
        'scraped',
        'search_term',
        'year',
        'imported_at',
        'imported_by_id',
    ];
}
