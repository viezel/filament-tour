<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tour_history', function (Blueprint $table) {
            $table->id();
            $table->char('tour_id', 255);
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('status')->nullable();
            $table->timestamps();

            $table->unique(['tour_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tour_history');
    }
};
