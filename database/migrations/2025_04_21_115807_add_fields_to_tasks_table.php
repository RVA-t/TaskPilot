<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->boolean('urgent')->default(false)->after('description');
            $table->unsignedBigInteger('curator_id')->nullable()->after('urgent');
            $table->unsignedBigInteger('assignee_id')->nullable()->after('curator_id');

            $table->foreign('curator_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['curator_id']);
            $table->dropForeign(['assignee_id']);

            $table->dropColumn(['description', 'urgent', 'curator_id', 'assignee_id']);
        });
    }
};

