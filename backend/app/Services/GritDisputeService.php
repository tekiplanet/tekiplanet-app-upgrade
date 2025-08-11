<?php

namespace App\Services;

use App\Models\Grit;
use App\Models\GritDispute;
use App\Models\User;

class GritDisputeService
{
    /**
     * Raises a dispute for a specific grit project.
     *
     * @param Grit $grit
     * @param User $complainant
     * @param array $details
     * @return GritDispute
     */
    public function raiseDispute(Grit $grit, User $complainant, array $details): GritDispute
    {
        return GritDispute::create([
            'grit_id' => $grit->id,
            'dispute_starter_id' => $complainant->id,
            'dispute_reason' => $details['reason'],
            'desired_outcome' => $details['outcome'],
            'status' => 'open',
        ]);
    }

    /**
     * Adds evidence to an existing dispute.
     *
     * @param GritDispute $dispute
     * @param User $user
     * @param array $evidenceDetails
     * @return GritDisputeEvidence
     */
    public function addEvidence(GritDispute $dispute, User $user, array $evidenceDetails): GritDisputeEvidence
    {
        return GritDisputeEvidence::create([
            'grit_dispute_id' => $dispute->id,
            'user_id' => $user->id,
            'evidence_type' => $evidenceDetails['type'] ?? 'text',
            'content' => $evidenceDetails['content'],
        ]);
    }

    /**
     * Resolves a dispute, finalizing the outcome.
     *
     * @param GritDispute $dispute
     * @param string $resolution
     * @param User|null $winner
     * @return GritDispute
     */
    public function resolveDispute(GritDispute $dispute, string $resolution, User $winner = null): GritDispute
    {
        $dispute->update([
            'status' => 'resolved',
            'resolution_details' => $resolution,
            'winner_id' => $winner ? $winner->id : null,
            'resolved_at' => now(),
        ]);

        return $dispute;
    }
}
