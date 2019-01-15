<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log', function (Blueprint $table) {
            $table->increments('id');

            $table
                ->string('search_term', 1024)
                ->nullable()
                ->index();

            $table->integer('imported')->default(0);

            $table->timestamps();
        });

        Schema::table('log', function (Blueprint $table) {
            $table->index('created_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('log');
    }
}
