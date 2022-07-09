<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 250)->nullable(false);
            $table->integer('language_id')->unsigned()->nullable(false);
            $table->year('year')->nullable(false);
            $table->integer('pages')->unsigned()->nullable();
            $table->text('description')->nullable();
            $table->string('cover')->nullable(false);
            $table->timestamps();

            $table->foreign('language_id')->references('id')->on('languages')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
};
