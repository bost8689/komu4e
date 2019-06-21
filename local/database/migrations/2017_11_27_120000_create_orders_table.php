<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {        	
            $table->increments('id');
            $table->integer('usersvk_id')->unsigned()->default(1);
            $table->foreign('usersvk_id')->references('id')->on('usersvk');  
            $table->string('type')->nullable()->default(Null);         
            $table->string('price')->nullable()->default(Null);
            $table->string('ordered')->nullable()->default(Null); 
            $table->string('executed')->nullable()->default(Null);
            $table->string('status')->nullable()->default(Null);
            $table->string('comments')->nullable()->default(Null);
            $table->integer('users_id')->unsigned()->default(1);
            $table->foreign('users_id')->references('id')->on('users');            
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
        Schema::drop('orders');
    }
}
