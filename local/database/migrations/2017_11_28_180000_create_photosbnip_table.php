<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosbnipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photosbnip', function (Blueprint $table) {
            $table->increments('id');
            $table->string('filenamemax')->nullable()->default(Null);
            $table->string('pathmax')->nullable()->default(Null);
            // $table->string('typemax')->nullable()->default(Null);
            // $table->string('photomax_url')->nullable()->default(Null); 
            $table->integer('bnip_id')->unsigned()->default(1);
            $table->foreign('bnip_id')->references('id')->on('bnip');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('photosbnip');
    }
}
