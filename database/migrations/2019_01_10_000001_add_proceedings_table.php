<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddProceedingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proceedings', function (Blueprint $table) {
            $table->increments('id');

            $table->string('number')->index();

            $table->string('court')->index();

            $table->string('instance')->index();

            $table->json('scraped');

            $table->string('search_term', 1024)->index();

            $table->integer('year')->index();

            $table
                ->timestamp('ignored_at')
                ->index()
                ->nullable();

            $table
                ->integer('ignored_by_id')
                ->unsigned()
                ->nullable();

            $table
                ->timestamp('imported_at')
                ->index()
                ->nullable();

            $table
                ->integer('imported_by_id')
                ->unsigned()
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('proceedings');
    }
}
