<?php

namespace App\Data\Models;

class Log extends BaseModel
{
    protected $table = 'log';

    protected $fillable = ['search_term', 'imported'];
}
