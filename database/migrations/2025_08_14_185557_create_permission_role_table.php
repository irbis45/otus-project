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
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        DB::table('permission_role')->insert([
                                                 ['role_id' => 2, 'permission_id' => 1], // 'editor' - 'view_admin_panel'
                                                 ['role_id' => 2, 'permission_id' => 2], // 'editor' - 'view_news'
                                                 ['role_id' => 2, 'permission_id' => 3], // 'editor' - 'create_news'
                                                 ['role_id' => 2, 'permission_id' => 4], // 'editor' - 'update_news'
                                                 ['role_id' => 2, 'permission_id' => 5], // 'editor' - 'delete_news'
                                             ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
