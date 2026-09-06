<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('statuses', 'project_id')) {
            return;
        }

        Schema::table('statuses', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        foreach (DB::table('statuses')->orderBy('id')->get() as $status) {
            $workspaceId = DB::table('projects')
                ->where('id', $status->project_id)
                ->value('workspace_id');

            if ($workspaceId) {
                DB::table('statuses')
                    ->where('id', $status->id)
                    ->update(['workspace_id' => $workspaceId]);
            }
        }

        Schema::table('statuses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::dropIfExists('projects');
    }

    public function down(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#7F77DD');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workspace_id');
        });
    }
};
