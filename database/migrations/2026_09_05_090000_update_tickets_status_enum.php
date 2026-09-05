<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY status ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        // Chamados com status 'resolved' viram 'in_progress' antes de remover o valor do enum
        DB::statement("UPDATE tickets SET status = 'in_progress' WHERE status = 'resolved'");
        DB::statement("ALTER TABLE tickets MODIFY status ENUM('open', 'in_progress', 'closed') NOT NULL DEFAULT 'open'");
    }
};
