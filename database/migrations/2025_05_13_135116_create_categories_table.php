<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
        });

        DB::table('categories')->insert([
                                            ['name' => 'Экономика', 'slug' => 'economy', 'description' => 'Новости экономики и бизнеса'],
                                            ['name' => 'Политика', 'slug' => 'politics', 'description' => 'Актуальные события в политике'],
                                            ['name' => 'Общество', 'slug' => 'society', 'description' => 'Социальные и культурные новости'],
                                            ['name' => 'Здоровье', 'slug' => 'health', 'description' => 'Новости медицины и здоровья'],
                                            ['name' => 'Образование', 'slug' => 'education', 'description' => 'Новости образования и науки'],
                                            ['name' => 'Технологии и наука', 'slug' => 'technology_and_science', 'description' => 'Последние достижения в технологиях и науке'],
                                            ['name' => 'Спорт', 'slug' => 'sports', 'description' => 'Спортивные события и достижения'],
                                            ['name' => 'Культура', 'slug' => 'culture', 'description' => 'Новости культуры и искусства'],
                                            ['name' => 'Развлечения', 'slug' => 'entertainment', 'description' => 'Развлекательные события и новости'],
                                            ['name' => 'Экология', 'slug' => 'ecology', 'description' => 'Экологические проблемы и решения'],
                                        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
