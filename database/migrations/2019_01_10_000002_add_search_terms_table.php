<?php

use App\Data\Models\SearchTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSearchTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_terms', function (Blueprint $table) {
            $table->increments('id');

            $table
                ->string('court')
                ->index()
                ->nullable();

            $table->string('text', 1024)->index();

            $table
                ->timestamp('last_searched_at')
                ->index()
                ->nullable();

            $table->timestamps();
        });

        SearchTerm::create(['text' => 'exmo sr presidente']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('search_terms');
    }
}
