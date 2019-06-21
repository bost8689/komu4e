<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersVKTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('usersvk', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('firstname')->nullable()->default(Null);
            $table->string('lastname')->nullable()->default(Null);
            $table->string('comments')->nullable()->default(Null);
            $table->string('photo')->nullable()->default(Null);
            // $table->timestamps();
             // Schema::table('users', function ($table) { $table->string('email'); });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('usersvk');
    }
}
