<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBnipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bnip', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_id')->nullable()->default(Null);
            $table->string('type_source')->nullable()->default(Null);
            $table->string('name_source')->nullable()->default(Null);
            $table->integer('post_id')->nullable()->default(Null);
            $table->string('type_post')->nullable()->default(Null);
            $table->string('name_post')->nullable()->default(Null);
            $table->integer('usersvk_id')->unsigned()->default(1);
            $table->foreign('usersvk_id')->references('id')->on('usersvk');
            $table->text('text')->nullable()->default(Null);            
            // $table->timestamp('date')->nullable()->default(Null);
            $table->integer('user_id')->unsigned()->default(1);
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('status')->nullable()->default(Null);
            $table->string('type_status')->nullable()->default(Null);
            $table->timestamps();;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bnip');
    }
}
