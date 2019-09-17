<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Data\Models\SearchTerm;
use Illuminate\Database\Migrations\Migration;

class AddSearchTerm extends Migration
{
    static $text = 'MESA DIRETORA DA ASSEMBLEIA LEGISLATIVA DO ESTADO DO RIO DE JANEIRO';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SearchTerm::create(['text' => static::$text]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SearchTerm::where('text', static::$text)->first()->delete();
    }
}
