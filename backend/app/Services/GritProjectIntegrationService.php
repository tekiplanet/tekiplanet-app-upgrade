<?php

namespace App\Services;

use App\Models\Grit;
use App\Models\Project; // Assuming a Project model exists

class GritProjectIntegrationService
{
    /**
     * Creates a new project from an approved and started Grit.
     *
     * @param Grit $grit
     * @return Project
     */
    public function createProjectFromGrit(Grit $grit): Project
    {
        $owner = $grit->user;

        return Project::create([
            'business_id' => $owner->businessProfile->id, // Assumes User has one-to-one with BusinessProfile
            'name' => $grit->title,
            'description' => $grit->description,
            'client_name' => $owner->name,
            'start_date' => now(),
            'end_date' => $grit->deadline,
            'status' => 'pending',
            'progress' => 0,
            'budget' => $grit->professional_budget,
        ]);
    }
}
