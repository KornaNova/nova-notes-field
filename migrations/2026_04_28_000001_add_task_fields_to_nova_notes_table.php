<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Outl1ne\NovaNotesField\NotesFieldServiceProvider;

class AddTaskFieldsToNovaNotesTable extends Migration
{
    public function up()
    {
        Schema::table(NotesFieldServiceProvider::getTableName(), function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('text');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('due_date');
            $table->timestamp('completed_at')->nullable()->after('pinned_at');

            $table->index('assigned_to');
            $table->index('due_date');
            $table->index('completed_at');
        });
    }

    public function down()
    {
        Schema::table(NotesFieldServiceProvider::getTableName(), function (Blueprint $table) {
            $tableName = NotesFieldServiceProvider::getTableName();
            $table->dropIndex([$tableName . '_assigned_to_index']);
            $table->dropIndex([$tableName . '_due_date_index']);
            $table->dropIndex([$tableName . '_completed_at_index']);
            $table->dropColumn(['due_date', 'assigned_to', 'completed_at']);
        });
    }
}
