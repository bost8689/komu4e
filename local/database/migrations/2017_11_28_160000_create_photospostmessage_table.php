<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosPostmessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photospostmessage', function (Blueprint $table) {
            $table->increments('id');
            $table->string('filenamemax')->nullable()->default(Null);
            $table->string('pathmax')->nullable()->default(Null);
            $table->string('typemax')->nullable()->default(Null);
            $table->string('photomax_url')->nullable()->default(Null);            
            $table->string('filenamemin')->nullable()->default(Null);
            $table->string('pathmin')->nullable()->default(Null);
            $table->string('typemin')->nullable()->default(Null);
            $table->string('photomin_url')->nullable()->default(Null);
            $table->integer('postmessage_id')->unsigned()->default(1);
            $table->foreign('postmessage_id')->references('id')->on('postmessage');
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
        Schema::drop('photospostmessage');
    }
}
