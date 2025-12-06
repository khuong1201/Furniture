<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('promotions')) {
            Schema::table('promotions', function (Blueprint $table) {
                if (!Schema::hasColumn('promotions', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index();
                }
                if (!Schema::hasColumn('promotions', 'start_date')) {
                    $table->timestamp('start_date')->nullable()->index();
                }
                if (!Schema::hasColumn('promotions', 'end_date')) {
                    $table->timestamp('end_date')->nullable()->index();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('promotions')) {
            Schema::table('promotions', function (Blueprint $table) {
                if (Schema::hasColumn('promotions', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('promotions', 'start_date')) {
                    $table->dropColumn('start_date');
                }
                if (Schema::hasColumn('promotions', 'end_date')) {
                    $table->dropColumn('end_date');
                }
            });
        }
    }
};
