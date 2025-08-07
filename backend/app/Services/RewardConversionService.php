<?php

namespace App\Services;

use App\Models\User;
use App\Models\ConversionTask;
use App\Models\UserConversionTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class RewardConversionService
{
    /**
     * Initiate a reward conversion for a user.
     * Deducts points, assigns a random eligible task, and creates a user_conversion_tasks record.
     *
     * @param User $user
     * @return UserConversionTask
     * @throws Exception
     */
    public function initiateConversion(User $user)
    {
        return DB::transaction(function () use ($user) {
            $userPoints = $user->learn_rewards;

            // Fetch eligible tasks
            $eligibleTasks = ConversionTask::where('min_points', '<=', $userPoints)
                ->where('max_points', '>=', $userPoints)
                ->get();

            if ($eligibleTasks->isEmpty()) {
                throw new Exception('No eligible conversion tasks available for your points.');
            }

            // Select a random task
            $task = $eligibleTasks->random();

            // Deduct points (use min_points for deduction)
            if ($user->learn_rewards < $task->min_points) {
                throw new Exception('Insufficient learning rewards for this conversion.');
            }
            $user->learn_rewards -= $task->min_points;
            $user->save();

            // Create user_conversion_tasks record
            $userTask = UserConversionTask::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'conversion_task_id' => $task->id,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            return $userTask;
        });
    }
}
