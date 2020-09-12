<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultitenancyImpersonateTables extends Migration
{
    public function up()
    {
        Schema::create('impersonate_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token');
            $table->unsignedBigInteger('impersonator_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('redirect_url')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->string('auth_guard')->nullable();
            $table->dateTime('impersonated_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('impersonate_tokens');
    }
}
