<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vessel_infos', function (Blueprint $table) {
            $table->id();
            $table->string("imo_number", 255);
            $table->string("vessel_name", 255)->nullable();
            $table->string("ship_type", 255)->nullable();
            $table->string("flag", 255)->nullable();
            $table->string("gross_tonnage", 255)->nullable();
            $table->string("summer_deadweight_t", 255)->nullable();
            $table->string("length_overall_m", 255)->nullable();
            $table->string("beam_m", 255)->nullable();
            $table->string("year_of_built", 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessel_infos');
    }
};
