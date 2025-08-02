<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    /**
     * Step 1: Update account type
     */
    public function updateAccountType(Request $request)
    {
        $user = $request->user();
        
        $validatedData = $request->validate([
            'account_type' => ['required', Rule::in(['student', 'business', 'professional'])]
        ]);

        try {
            $user->account_type = $validatedData['account_type'];
            $user->save();

            Log::info('Account type updated during onboarding', [
                'user_id' => $user->id,
                'account_type' => $validatedData['account_type']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account type updated successfully',
                'user' => $user->fresh(),
                'next_step' => 'profile'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update account type during onboarding', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update account type'
            ], 500);
        }
    }

    /**
     * Step 2: Update profile information (name and avatar)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $user->first_name = $validatedData['first_name'];
            $user->last_name = $validatedData['last_name'];

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            $user->save();

            Log::info('Profile updated during onboarding', [
                'user_id' => $user->id,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(),
                'onboarding_complete' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update profile during onboarding', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    /**
     * Get onboarding status
     */
    public function getOnboardingStatus(Request $request)
    {
        $user = $request->user();
        
        // Onboarding is complete if user has first/last name (regardless of account type)
        // Once onboarding is complete, user should never need to go through it again
        $isComplete = !empty($user->first_name) && !empty($user->last_name);

        return response()->json([
            'is_complete' => $isComplete,
            'current_step' => $this->getCurrentStep($user),
            'user' => $user
        ]);
    }

    /**
     * Determine current onboarding step
     */
    private function getCurrentStep(User $user)
    {
        // If user has completed onboarding (has first_name and last_name), they're done
        if (!empty($user->first_name) && !empty($user->last_name)) {
            return 'complete';
        }
        
        // Step 1: Select account type (if still default 'student' and no name)
        if ($user->account_type === 'student') {
            return 'account_type';
        }
        
        // Step 2: Complete profile (if account type selected but missing name)
        if (empty($user->first_name) || empty($user->last_name)) {
            return 'profile';
        }
        
        // Step 3: Complete
        return 'complete';
    }
} 