<?php

namespace App\Services;

use App\Models\Grit;
use App\Models\User;
use App\Models\GritEscrowTransaction;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    /**
     * Moves funds from a user's wallet to their frozen balance for a specific grit.
     *
     * @param Grit $grit
     * @param User $user
     * @param float $amount
     * @return GritEscrowTransaction
     */
    public function holdFunds(Grit $grit, User $user, float $amount): GritEscrowTransaction
    {
        return DB::transaction(function () use ($grit, $user, $amount) {
            if ($user->wallet_balance < $amount) {
                throw new \Exception('Insufficient funds.');
            }

            $user->decrement('wallet_balance', $amount);
            $user->increment('frozen_balance', $amount);

            return GritEscrowTransaction::create([
                'grit_id' => $grit->id,
                'user_id' => $user->id,
                'transaction_type' => 'hold',
                'amount' => $amount,
                'description' => 'Funds held in escrow for grit project.',
            ]);
        });
    }

    /**
     * Releases funds from the owner's frozen balance to the professional's wallet.
     *
     * @param Grit $grit
     * @param User $professional
     * @param float $amount
     * @return GritEscrowTransaction
     */
    public function releaseFunds(Grit $grit, User $professional, float $amount): GritEscrowTransaction
    {
        return DB::transaction(function () use ($grit, $professional, $amount) {
            $owner = $grit->user;

            if ($owner->frozen_balance < $amount) {
                throw new \Exception('Insufficient frozen funds.');
            }

            $owner->decrement('frozen_balance', $amount);
            $professional->increment('wallet_balance', $amount);

            return GritEscrowTransaction::create([
                'grit_id' => $grit->id,
                'user_id' => $professional->id, // The user receiving the funds
                'transaction_type' => 'release',
                'amount' => $amount,
                'description' => 'Funds released to professional upon milestone completion.',
            ]);
        });
    }

    /**
     * Refunds funds from the owner's frozen balance back to their main wallet.
     *
     * @param Grit $grit
     * @param User $user
     * @param float $amount
     * @return GritEscrowTransaction
     */
    public function refundFunds(Grit $grit, User $user, float $amount): GritEscrowTransaction
    {
        return DB::transaction(function () use ($grit, $user, $amount) {
            if ($user->frozen_balance < $amount) {
                throw new \Exception('Insufficient frozen funds for refund.');
            }

            $user->decrement('frozen_balance', $amount);
            $user->increment('wallet_balance', $amount);

            return GritEscrowTransaction::create([
                'grit_id' => $grit->id,
                'user_id' => $user->id,
                'transaction_type' => 'refund',
                'amount' => $amount,
                'description' => 'Funds refunded to user from escrow.',
            ]);
        });
    }
}
