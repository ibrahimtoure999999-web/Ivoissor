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
        Schema::create('citoyens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nni', 11)->nullable()->unique();
            $table->string('nom', 100);
            $table->string('prenoms', 150);
            $table->date('date_naissance');
            $table->string('lieu_naissance', 100);
            $table->char('genre', 1);
            $table->string('pays_residence', 100);
            $table->string('adresse_residence', 255);
            $table->string('telephone', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citoyens');
    }
};
