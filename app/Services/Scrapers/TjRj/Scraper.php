<?php

namespace App\Services\Scrapers\TjRj;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Scraper extends DuskTestCase
{
    const URL = 'http://www4.tjrj.jus.br/ConsultaUnificada/consulta.do';

    public function scrape()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(static::URL)
                    ->assertSee('Consultas Processuais');
        });
    }
}
