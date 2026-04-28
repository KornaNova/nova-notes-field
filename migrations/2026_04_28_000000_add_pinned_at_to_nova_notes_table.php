<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Outl1ne\NovaNotesField\NotesFieldServiceProvider;

class AddPinnedAtToNovaNotesTable extends Migration
{
    public function up()
    {
        Schema::table(NotesFieldServiceProvider::getTableName(), function (Blueprint $table) {
            $table->timestamp('pinned_at')->nullable()->after('created_by');
            $table->index('pinned_at');
        });
    }

    public function down()
    {
        Schema::table(NotesFieldServiceProvider::getTableName(), function (Blueprint $table) {
            $table->dropIndex([NotesFieldServiceProvider::getTableName() . '_pinned_at_index']);
            $table->dropColumn('pinned_at');
        });
    }
}
