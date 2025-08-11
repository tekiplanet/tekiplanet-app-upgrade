<?php

namespace App\Services;

use App\Models\Grit;
use App\Models\GritNegotiation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GritNegotiationService
{
    /**
     * Allows a user (owner or professional) to propose negotiation terms.
     *
     * @param Grit $grit
     * @param User $proposer
     * @param array $terms
     * @return GritNegotiation
     */
    public function proposeTerms(Grit $grit, User $proposer, array $terms): GritNegotiation
    {
        return GritNegotiation::create([
            'grit_id' => $grit->id,
            'user_id' => $proposer->id,
            'proposed_budget' => $terms['proposed_budget'] ?? null,
            'proposed_deadline' => $terms['proposed_deadline'] ?? null,
            'message' => $terms['message'] ?? '',
            'status' => 'proposed',
        ]);
    }

    /**
     * Accepts the latest negotiation terms.
     *
     * @param GritNegotiation $negotiation
     * @return GritNegotiation
     */
    public function acceptTerms(GritNegotiation $negotiation): GritNegotiation
    {
        return DB::transaction(function () use ($negotiation) {
            $negotiation->update(['status' => 'accepted']);

            $grit = $negotiation->grit;
            $grit->update([
                'professional_budget' => $negotiation->proposed_budget,
                'deadline' => $negotiation->proposed_deadline,
            ]);

            return $negotiation;
        });
    }

    /**
     * Rejects the latest negotiation terms.
     *
     * @param GritNegotiation $negotiation
     * @return GritNegotiation
     */
    public function rejectTerms(GritNegotiation $negotiation): GritNegotiation
    {
        $negotiation->update(['status' => 'rejected']);

        return $negotiation;
    }

    /**
     * Proposes a counter-offer to existing negotiation terms.
     *
     * @param GritNegotiation $originalNegotiation
     * @param User $proposer
     * @param array $newTerms
     * @return GritNegotiation
     */
    public function counterOffer(GritNegotiation $originalNegotiation, User $proposer, array $newTerms): GritNegotiation
    {
        return DB::transaction(function () use ($originalNegotiation, $proposer, $newTerms) {
            $this->rejectTerms($originalNegotiation);

            return $this->proposeTerms($originalNegotiation->grit, $proposer, $newTerms);
        });
    }
}
