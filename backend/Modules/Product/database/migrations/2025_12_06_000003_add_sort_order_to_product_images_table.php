<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('product_images') && !Schema::hasColumn('product_images', 'sort_order')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('is_primary');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('product_images') && Schema::hasColumn('product_images', 'sort_order')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
