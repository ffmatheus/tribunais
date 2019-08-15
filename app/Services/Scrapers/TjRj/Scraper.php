<?php

namespace App\Services\Scrapers\TjRj;

use DB;
use Tests\DuskTestCase;
use App\Data\Models\Log;
use Laravel\Dusk\Browser;
use Facebook\WebDriver\WebDriverBy;
use App\Data\Repositories\SearchTerms;
use App\Data\Repositories\Proceedings;
use Laravel\Dusk\Chrome\ChromeProcess;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

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

    public $localBrowser;

    private function __scrapeCourt($court, $search, $year)
    {
        $this->court = $court;

        $this->search = $search;

        $this->year = $year;

        $this->found = 0;

        $this->getBrowser()
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
                    $this->getBrowser()->select('pagina', $page);

                    sleep(1);
                }

                collect(
                    $this->getBrowser()
                        ->element('form[name="consultaNomeForm"]')
                        ->findElements(WebDriverBy::tagName('table'))[2]
                        ->findElements(WebDriverBy::tagName('tr'))
                )->each(function ($element) {
                    $this->addLine($element->getText());
                });
            });
    }

    private function cleanLog()
    {
        DB::table('log')
            ->where('created_at', '<', now()->subYear())
            ->delete();
    }

    protected function getBrowser()
    {
        return $this->localBrowser;
    }

    protected function getPages()
    {
        try {
            $options = $this->getBrowser()
                ->resolver->resolveForSelection('pagina')
                ->findElements(WebDriverBy::tagName('option'));
        } catch (\Exception $exception) {
            return collect([]);
        }

        return collect((array) $options)->map(function ($option) {
            return $option->getAttribute('value');
        });
    }

    protected function addLine($line)
    {
        return empty(trim($line)) ? $this->import() : ($this->buffer[] = $line);
    }

    protected function import()
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

    public function scrapeCourt($court, $search, $year)
    {
        try {
            $this->__scrapeCourt($court, $search, $year);
        } catch (\Exception $exception) {
            $this->__scrapeCourt($court, $search, $year);
        }
    }

    public function scrape()
    {
        app(SearchTerms::class)
            ->all()
            ->each(function ($searchTerm) {
                $found = $this->scrapeCourt(
                    static::COURT,
                    $searchTerm->text,
                    now()->year
                );

                dump('saving log');

                Log::create([
                    'search_term' => $searchTerm->text,

                    'found' => $found
                ]);
            });
    }

    public function makeBrowser()
    {
        static::startChromeDriver();

        $process = (new ChromeProcess())->toProcess();

        $process->start();

        $options = (new ChromeOptions())->addArguments([
            '--disable-gpu',
            '--headless'
        ]);

        $capabilities = DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY,
            $options
        );

        $driver = retry(
            5,
            function () use ($capabilities) {
                return RemoteWebDriver::create(
                    'http://localhost:9515',
                    $capabilities
                );
            },
            50
        );

        $this->localBrowser = new Browser($driver);

        //        $browser->visit('https://www.google.com');
        //
        //        $browser->quit();
        //
        //        $process->stop();

        return $this;
    }
}
