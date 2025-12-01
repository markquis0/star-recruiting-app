<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Form;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Form::where('form_type', 'project')
            ->whereNotNull('data')
            ->chunk(100, function ($forms) {
                foreach ($forms as $form) {
                    $oldData = $form->data ?? [];
                    
                    // Skip if already in new format (has project_title)
                    if (isset($oldData['project_title'])) {
                        continue;
                    }
                    
                    // Transform old structure to new
                    $newData = [
                        'project_title' => $oldData['project_name'] ?? null,
                        'company' => null,
                        'role_title' => $oldData['role'] ?? null,
                        'timeframe' => $oldData['duration'] ?? null,
                        'summary_one_liner' => null,
                        'context' => $oldData['description'] ?? null,
                        'problem' => null,
                        'affected_audience' => null,
                        'primary_goal' => null,
                        'metrics' => [],
                        'responsibilities' => null,
                        'project_type' => null,
                        'teams_involved' => [],
                        'collaboration_example' => null,
                        'challenges' => null,
                        'challenge_response' => null,
                        'tradeoffs' => null,
                        'outcome_summary' => null,
                        'impact' => null,
                        'recognition' => null,
                        'learning' => null,
                        'retro' => null,
                    ];
                    
                    // Preserve technologies if it exists - convert to teams_involved
                    if (isset($oldData['technologies'])) {
                        if (is_array($oldData['technologies'])) {
                            $newData['teams_involved'] = $oldData['technologies'];
                        } else {
                            // Split comma-separated string
                            $newData['teams_involved'] = array_filter(
                                array_map('trim', explode(',', $oldData['technologies']))
                            );
                        }
                    }
                    
                    $form->data = $newData;
                    $form->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert new structure back to old structure
        Form::where('form_type', 'project')
            ->whereNotNull('data')
            ->chunk(100, function ($forms) {
                foreach ($forms as $form) {
                    $newData = $form->data ?? [];
                    
                    // Skip if already in old format (has project_name)
                    if (isset($newData['project_name'])) {
                        continue;
                    }
                    
                    // Transform new structure back to old
                    $oldData = [
                        'project_name' => $newData['project_title'] ?? null,
                        'description' => $newData['context'] ?? null,
                        'technologies' => !empty($newData['teams_involved']) 
                            ? (is_array($newData['teams_involved']) 
                                ? implode(', ', $newData['teams_involved']) 
                                : $newData['teams_involved'])
                            : null,
                        'duration' => $newData['timeframe'] ?? null,
                        'role' => $newData['role_title'] ?? null,
                    ];
                    
                    // Remove null values
                    $oldData = array_filter($oldData, fn($value) => $value !== null);
                    
                    $form->data = $oldData;
                    $form->save();
                }
            });
    }
};
