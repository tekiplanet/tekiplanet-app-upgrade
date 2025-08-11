<?php

namespace App\Services;

use App\Models\Grit;
use App\Models\User;
use App\Models\GritEscrowTransaction;
use Illuminate\Support\Facades\DB;

class GritEscrowService
{
    /**
     * Freezes the initial 20% of the budget when a professional is approved.
     *
     * @param Grit $grit
     * @return GritEscrowTransaction
     */
    public function freezeInitialAmount(Grit $grit): GritEscrowTransaction
    {
        $owner = $grit->user;
        $amountToFreeze = $grit->professional_budget * 0.20;

        return $this->holdFunds($grit, $owner, $amountToFreeze);
    }

    /**
     * Processes the start of the project: refunds the initial hold, freezes the full budget,
     * and releases the first payment to the professional.
     *
     * @param Grit $grit
     * @return void
     */
    public function processProjectStart(Grit $grit): void
    {
        DB::transaction(function () use ($grit) {
            $owner = $grit->user;
            $professional = $grit->assignedProfessional;
            $totalBudget = $grit->professional_budget;

            // 1. Refund the initial 20%
            $initialHold = $totalBudget * 0.20;
            $this->refundFunds($grit, $owner, $initialHold);

            // 2. Freeze the full 100% of the budget
            $this->holdFunds($grit, $owner, $totalBudget);

            // 3. Release the first 40% to the professional
            $firstPayment = $totalBudget * 0.40;
            $this->releaseFunds($grit, $professional, $firstPayment);
        });
    }

    /**
     * Releases a subsequent payment to the professional.
     * Ensures the total released amount does not exceed 80% of the budget before final payment.
     *
     * @param Grit $grit
     * @param float $amount
     * @return GritEscrowTransaction
     */
    public function releasePayment(Grit $grit, float $amount): GritEscrowTransaction
    {
        return DB::transaction(function () use ($grit, $amount) {
            $professional = $grit->assignedProfessional;
            $totalBudget = $grit->professional_budget;
            $paymentLimit = $totalBudget * 0.80;

            $totalReleased = $grit->escrowTransactions()
                ->where('transaction_type', 'release')
                ->sum('amount');

            if (($totalReleased + $amount) > $paymentLimit) {
                throw new \Exception('Payment exceeds the 80% limit before final release.');
            }

            return $this->releaseFunds($grit, $professional, $amount);
        });
    }

    /**
     * Freezes additional funds when the project budget is increased.
     *
     * @param Grit $grit
     * @param float $increaseAmount
     * @return GritEscrowTransaction
     */
    public function handleBudgetIncrease(Grit $grit, float $increaseAmount): GritEscrowTransaction
    {
        $owner = $grit->user;
        return $this->holdFunds($grit, $owner, $increaseAmount);
    }

    /**
     * Processes the final payment, releasing all remaining funds to the professional.
     *
     * @param Grit $grit
     * @return GritEscrowTransaction
     */
    public function processFinalPayment(Grit $grit): GritEscrowTransaction
    {
        return DB::transaction(function () use ($grit) {
            $professional = $grit->assignedProfessional;

            $amountHeld = $grit->escrowTransactions()->where('transaction_type', 'hold')->sum('amount');
            $amountReleased = $grit->escrowTransactions()->where('transaction_type', 'release')->sum('amount');
            $amountRefunded = $grit->escrowTransactions()->where('transaction_type', 'refund')->sum('amount');

            $remainingBalance = $amountHeld - $amountReleased - $amountRefunded;

            if ($remainingBalance <= 0) {
                throw new \Exception('No remaining funds to release.');
            }

            return $this->releaseFunds($grit, $professional, $remainingBalance);
        });
    }

    /**
     * Moves funds from a user's wallet to their frozen balance for a specific grit.
     *
     * @param Grit $grit
     * @param User $user
     * @param float $amount
     * @return GritEscrowTransaction
     */
    protected function holdFunds(Grit $grit, User $user, float $amount): GritEscrowTransaction
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
    protected function releaseFunds(Grit $grit, User $professional, float $amount): GritEscrowTransaction
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
    protected function refundFunds(Grit $grit, User $user, float $amount): GritEscrowTransaction
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
