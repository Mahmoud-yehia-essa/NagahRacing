<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn("camel_workers", "online_status")) {
            Schema::table("camel_workers", function (Blueprint $table) {
                $table->string("online_status")->default("offline")->after("is_online");
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn("camel_workers", "online_status")) {
            Schema::table("camel_workers", function (Blueprint $table) {
                $table->dropColumn("online_status");
            });
        }
    }
};
