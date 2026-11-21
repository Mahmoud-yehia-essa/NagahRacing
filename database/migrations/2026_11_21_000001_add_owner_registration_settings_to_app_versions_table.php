<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('app_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('app_versions', 'app_type')) {
                $table->string('app_type')->nullable()->default('user');
            }
            if (!Schema::hasColumn('app_versions', 'update_required')) {
                $table->string('update_required')->nullable()->default('no');
            }
            if (!Schema::hasColumn('app_versions', 'stop_owner_registration')) {
                $table->boolean('stop_owner_registration')->default(false);
            }
            if (!Schema::hasColumn('app_versions', 'whatsapp_contact')) {
                $table->string('whatsapp_contact')->nullable()->default('+96551673464');
            }
            if (!Schema::hasColumn('app_versions', 'inactive_owner_message')) {
                $table->text('inactive_owner_message')->nullable();
            }
        });

        // Ensure default settings record (id = 1) exists
        if (!DB::table('app_versions')->where('id', 1)->exists()) {
            DB::table('app_versions')->insert([
                'id'                      => 1,
                'version'                 => '1.0.0',
                'des'                     => 'Nagah Racing',
                'android'                 => '',
                'ios'                     => '',
                'app_type'                => 'user',
                'update_required'         => 'no',
                'stop_owner_registration' => false,
                'whatsapp_contact'        => '+96551673464',
                'inactive_owner_message'  => 'حسابك غير مفعل حالياً، الرجاء التواصل مع الإدارة عبر الواتساب لتفعيل الحساب.',
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_versions', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('app_versions', 'stop_owner_registration')) {
                $columns[] = 'stop_owner_registration';
            }
            if (Schema::hasColumn('app_versions', 'whatsapp_contact')) {
                $columns[] = 'whatsapp_contact';
            }
            if (Schema::hasColumn('app_versions', 'inactive_owner_message')) {
                $columns[] = 'inactive_owner_message';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
