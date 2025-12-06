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
        if (Schema::hasTable('refresh_tokens') && !Schema::hasColumn('refresh_tokens', 'is_revoked')) {
            Schema::table('refresh_tokens', function (Blueprint $table) {
                $table->boolean('is_revoked')->default(false)->after('user_agent');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('refresh_tokens') && Schema::hasColumn('refresh_tokens', 'is_revoked')) {
            Schema::table('refresh_tokens', function (Blueprint $table) {
                $table->dropColumn('is_revoked');
            });
        }
    }
};
