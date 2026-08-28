<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('icon')->default('pi pi-money-bill');
            $table->string('color')->default('#2881e4');
            $table->string('name_revenue');
            $table->decimal('value_revenue', 12, 2);
            $table->date('date_revenue');
            $table->text('anotation')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date_revenue']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
