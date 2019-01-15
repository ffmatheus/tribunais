<?php

namespace App\Services\Scrapers\TjRj;

use DB;
use Tests\DuskTestCase;
use App\Data\Models\Log;
use Laravel\Dusk\Browser;
use Facebook\WebDriver\WebDriverBy;
use App\Data\Repositories\SearchTerms;
use App\Data\Repositories\Proceedings;

class Scraper extends DuskTestCase
{
    const URL = 'http://www4.tjrj.jus.br/ConsultaUnificada/consulta.do';

    const COURT = 'TJRJ';

    public $browser;

    public $results = [];

    public $buffer = [];

    public $court;

    public $search;

    public $year;

    public $found;

    private function cleanLog()
    {
        DB::table('log')
            ->where('created_at', '<', now()->subYear())
            ->delete();
    }

    private function getPages()
    {
        try {
            $options = $this->browser->resolver
                ->resolveForSelection('pagina')
                ->findElements(WebDriverBy::tagName('option'));
        } catch (\Exception $exception) {
            return collect([]);
        }

        return collect((array) $options)->map(function ($option) {
            return $option->getAttribute('value');
        });
    }

    private function addLine($line)
    {
        return empty(trim($line)) ? $this->import() : ($this->buffer[] = $line);
    }

    private function import()
    {
        $this->cleanLog();

        $number = isset($this->buffer[0]) ? $this->buffer[0] : null;

        if ($number && $number !== 'Nenhum resultado encontrado') {
            app(Proceedings::class)->import(
                $number,
                $this->court,
                $this->buffer,
                $this->search,
                $this->year
            );

            $this->found++;
        }

        $this->buffer = [];
    }

    private function scrapeCourt($court, $search, $year)
    {
        $this->court = $court;

        $this->search = $search;

        $this->year = $year;

        $this->found = 0;

        $this->browse(function (Browser $browser) {
            $this->browser = $browser;

            $browser
                ->visit(static::URL)
                ->click("a[href='#tabs-nome-indice1']")
                ->select('origem', 2)
                ->type('nomeParte', $this->search)
                ->type('anoInicio', $this->year)
                ->type('anoFinal', $this->year)
                ->click('#pesquisa')
                ->waitForText('Resultado da pesquisa', 10);

            $this->getPages()
                ->prepend('0')
                ->each(function ($page) {
                    if ($page !== '0') {
                        $this->browser->select('pagina', $page);

                        sleep(1);
                    }

                    collect(
                        $this->browser
                            ->element('form[name="consultaNomeForm"]')
                            ->findElements(WebDriverBy::tagName('table'))[2]
                            ->findElements(WebDriverBy::tagName('tr'))
                    )->each(function ($element) {
                        $this->addLine($element->getText());
                    });
                });
        });
    }

    public function testScrape()
    {
        app(SearchTerms::class)
            ->all()
            ->each(function ($searchTerm) {
                Log::create([
                    'search_term' => $searchTerm->text,

                    'found' => $this->scrapeCourt(
                        static::COURT,
                        $searchTerm->text,
                        now()->year
                    ),
                ]);
            });
    }
}
