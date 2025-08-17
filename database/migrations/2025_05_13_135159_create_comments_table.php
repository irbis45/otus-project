<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Comment;
use App\Models\User;
use App\Models\News;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Comment::class, 'parent_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->foreignIdFor(User::class, 'author_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->foreignIdFor(News::class)
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->text('text');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
