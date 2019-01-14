<?php

namespace App\Data\Repositories;

use App\Data\Models\Proceeding;

class Proceedings extends Base
{
    public function import($numero, $tribunal, $lines, $termo, $ano)
    {
        Proceeding::firstOrCreate(
            [
                'number' => $numero,
                'court' => $tribunal,
            ],

            [
                'instance' => '2',
                'scraped' => json_encode($lines),
                'search_term' => $termo,
                'year' => $ano,
            ]
        );
    }
}
