<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('icon')->default('pi pi-receipt');
            $table->string('color')->default('#2881e4');
            $table->string('name_expense');
            $table->decimal('value_expense', 12, 2);
            $table->date('date_expense');
            $table->text('anotation')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();

            // Equivalente à antiga chave MMYYYY: consultas por usuário + mês/ano ficam rápidas
            $table->index(['user_id', 'date_expense']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
