<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $codesToDelete = ['hospitalization_consent', 'procedures_consent'];

        $documentIds = DB::table('documents')
            ->whereIn('code', $codesToDelete)
            ->pluck('id');

        if ($documentIds->isNotEmpty()) {
            DB::table('stay_documents')
                ->whereIn('document_id', $documentIds)
                ->delete();

            DB::table('documents')
                ->whereIn('id', $documentIds)
                ->delete();
        }
    }

    public function down(): void
    {
        // Not reversible — re-run the seeder with the old version if needed.
    }
};
