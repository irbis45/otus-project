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
        Schema::table('news', function (Blueprint $table) {
            $table->text('excerpt')->nullable();
            $table->integer('views')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->string('slug')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('excerpt');
            $table->dropColumn('views');
            $table->dropColumn('active');
            $table->dropColumn('featured');
            $table->dropColumn('slug');
        });
    }
};
