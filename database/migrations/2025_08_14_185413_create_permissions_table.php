<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // уникальный идентификатор права, например 'view_admin_panel'
            $table->string('name'); // читаемое название права
        });


        DB::table('permissions')->insert([
                                             [
                                                 'slug' => 'view_admin_panel',
                                                 'name' => 'Доступ в административный раздел',
                                             ],
                                             ['slug' => 'view_news', 'name' => 'Просмотр новостей',],
                                             ['slug' => 'create_news', 'name' => 'Создание новостей',],
                                             ['slug' => 'update_news', 'name' => 'Редактирование новостей',],
                                             ['slug' => 'delete_news', 'name' => 'Удаление новостей',],
                                             ['slug' => 'view_categories', 'name' => 'Просмотр категорий',],
                                             ['slug' => 'create_categories', 'name' => 'Создание категорий',],
                                             ['slug' => 'update_categories', 'name' => 'Редактирование категорий',],
                                             ['slug' => 'delete_categories', 'name' => 'Удаление категорий',],
                                             ['slug' => 'view_comments', 'name' => 'Просмотр комментариев',],
                                             ['slug' => 'update_comments', 'name' => 'Редактирование комментариев',],
                                             ['slug' => 'delete_comments', 'name' => 'Удаление комментариев',],
                                             ['slug' => 'view_users', 'name' => 'Просмотр пользователей',],
                                             ['slug' => 'create_users', 'name' => 'Создание пользователей',],
                                             ['slug' => 'update_users', 'name' => 'Редактирование пользователей',],
                                             ['slug' => 'delete_users', 'name' => 'Удаление пользователей',],
                                         ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
