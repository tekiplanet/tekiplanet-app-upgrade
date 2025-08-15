<?php

namespace App\Services;

use App\Models\Grit;
use App\Models\GritMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GritSystemMessageService
{
    /**
     * Create a system message for GRIT actions
     */
    public static function createSystemMessage(
        Grit $grit,
        string $action,
        array $metadata = [],
        ?User $triggeredBy = null
    ): GritMessage {
        $message = self::generateSystemMessage($action, $metadata);
        
        return GritMessage::create([
            'grit_id' => $grit->id,
            'user_id' => $triggeredBy ? $triggeredBy->id : null,
            'message' => $message,
            'sender_type' => 'system',
            'is_read' => false
        ]);
    }

    /**
     * Generate system message text based on action
     */
    private static function generateSystemMessage(string $action, array $metadata = []): string
    {
        switch ($action) {
            case 'grit_created':
                return "🎯 GRIT created successfully and is pending admin approval.";
                
            case 'grit_approved':
                return "✅ GRIT has been approved by admin and is now visible to professionals.";
                
            case 'grit_rejected':
                $reason = $metadata['reason'] ?? 'No reason provided';
                return "❌ GRIT was rejected by admin. Reason: {$reason}";
                
            case 'application_submitted':
                $professionalName = $metadata['professional_name'] ?? 'A professional';
                return "📝 {$professionalName} has applied for this GRIT.";
                
            case 'application_approved':
                $professionalName = $metadata['professional_name'] ?? 'Professional';
                return "🎉 {$professionalName} has been assigned to this GRIT!";
                
            case 'application_rejected':
                $professionalName = $metadata['professional_name'] ?? 'Professional';
                $reason = $metadata['reason'] ?? 'No reason provided';
                return "❌ {$professionalName}'s application was rejected. Reason: {$reason}";
                
            case 'application_withdrawn':
                $professionalName = $metadata['professional_name'] ?? 'Professional';
                return "👋 {$professionalName} has withdrawn their application.";
                
            case 'project_started':
                $professionalName = $metadata['professional_name'] ?? 'Professional';
                return "🚀 Project has been started by {$professionalName}.";
                
            case 'payment_released':
                $amount = $metadata['amount'] ?? 0;
                $currency = $metadata['currency'] ?? 'USD';
                $percentage = $metadata['percentage'] ?? 0;
                return "💰 Payment of {$amount} {$currency} ({$percentage}%) has been released.";
                
            case 'budget_increased':
                $oldAmount = $metadata['old_amount'] ?? 0;
                $newAmount = $metadata['new_amount'] ?? 0;
                $currency = $metadata['currency'] ?? 'USD';
                return "📈 Budget increased from {$oldAmount} {$currency} to {$newAmount} {$currency}.";
                
            case 'terms_modified':
                $modifierName = $metadata['modifier_name'] ?? 'Owner';
                return "📝 Terms have been modified by {$modifierName}.";
                
            case 'completion_requested':
                $professionalName = $metadata['professional_name'] ?? 'Professional';
                return "✅ {$professionalName} has marked the project as complete and is awaiting approval.";
                
            case 'project_completed':
                $rating = $metadata['rating'] ?? null;
                $ratingText = $rating ? " with a rating of {$rating}/5" : '';
                return "🎊 Project has been completed{$ratingText}!";
                
            case 'dispute_raised':
                $raiserName = $metadata['raiser_name'] ?? 'A party';
                $reason = $metadata['reason'] ?? 'No reason provided';
                return "⚠️ Dispute raised by {$raiserName}. Reason: {$reason}";
                
            case 'dispute_resolved':
                $resolution = $metadata['resolution'] ?? 'Resolved';
                return "✅ Dispute has been resolved: {$resolution}";
                
            case 'escrow_frozen':
                $amount = $metadata['amount'] ?? 0;
                $currency = $metadata['currency'] ?? 'USD';
                $percentage = $metadata['percentage'] ?? 0;
                return "🔒 {$amount} {$currency} ({$percentage}%) has been frozen in escrow.";
                
            case 'escrow_released':
                $amount = $metadata['amount'] ?? 0;
                $currency = $metadata['currency'] ?? 'USD';
                $percentage = $metadata['percentage'] ?? 0;
                return "🔓 {$amount} {$currency} ({$percentage}%) has been released from escrow.";
                
            case 'negotiation_started':
                $initiatorName = $metadata['initiator_name'] ?? 'Owner';
                return "🤝 {$initiatorName} has initiated terms negotiation.";
                
            case 'negotiation_completed':
                return "✅ Terms negotiation has been completed and accepted.";
                
            default:
                Log::warning('Unknown system message action', ['action' => $action]);
                return "System notification: {$action}";
        }
    }

    /**
     * Create system message for GRIT creation
     */
    public static function gritCreated(Grit $grit, User $createdBy): GritMessage
    {
        return self::createSystemMessage($grit, 'grit_created', [], $createdBy);
    }

    /**
     * Create system message for GRIT approval
     */
    public static function gritApproved(Grit $grit, User $approvedBy): GritMessage
    {
        return self::createSystemMessage($grit, 'grit_approved', [], $approvedBy);
    }

    /**
     * Create system message for GRIT rejection
     */
    public static function gritRejected(Grit $grit, User $rejectedBy, string $reason): GritMessage
    {
        return self::createSystemMessage($grit, 'grit_rejected', ['reason' => $reason], $rejectedBy);
    }

    /**
     * Create system message for application submission
     */
    public static function applicationSubmitted(Grit $grit, string $professionalName): GritMessage
    {
        return self::createSystemMessage($grit, 'application_submitted', ['professional_name' => $professionalName]);
    }

    /**
     * Create system message for application approval
     */
    public static function applicationApproved(Grit $grit, string $professionalName, User $approvedBy): GritMessage
    {
        return self::createSystemMessage($grit, 'application_approved', ['professional_name' => $professionalName], $approvedBy);
    }

    /**
     * Create system message for application rejection
     */
    public static function applicationRejected(Grit $grit, string $professionalName, string $reason, User $rejectedBy): GritMessage
    {
        return self::createSystemMessage($grit, 'application_rejected', [
            'professional_name' => $professionalName,
            'reason' => $reason
        ], $rejectedBy);
    }

    /**
     * Create system message for application withdrawal
     */
    public static function applicationWithdrawn(Grit $grit, string $professionalName): GritMessage
    {
        return self::createSystemMessage($grit, 'application_withdrawn', ['professional_name' => $professionalName]);
    }

    /**
     * Create system message for project start
     */
    public static function projectStarted(Grit $grit, string $professionalName): GritMessage
    {
        return self::createSystemMessage($grit, 'project_started', ['professional_name' => $professionalName]);
    }

    /**
     * Create system message for payment release
     */
    public static function paymentReleased(Grit $grit, float $amount, string $currency, int $percentage): GritMessage
    {
        return self::createSystemMessage($grit, 'payment_released', [
            'amount' => $amount,
            'currency' => $currency,
            'percentage' => $percentage
        ]);
    }

    /**
     * Create system message for budget increase
     */
    public static function budgetIncreased(Grit $grit, float $oldAmount, float $newAmount, string $currency): GritMessage
    {
        return self::createSystemMessage($grit, 'budget_increased', [
            'old_amount' => $oldAmount,
            'new_amount' => $newAmount,
            'currency' => $currency
        ]);
    }

    /**
     * Create system message for terms modification
     */
    public static function termsModified(Grit $grit, string $modifierName): GritMessage
    {
        return self::createSystemMessage($grit, 'terms_modified', ['modifier_name' => $modifierName]);
    }

    /**
     * Create system message for completion request
     */
    public static function completionRequested(Grit $grit, string $professionalName): GritMessage
    {
        return self::createSystemMessage($grit, 'completion_requested', ['professional_name' => $professionalName]);
    }

    /**
     * Create system message for project completion
     */
    public static function projectCompleted(Grit $grit, ?int $rating = null): GritMessage
    {
        return self::createSystemMessage($grit, 'project_completed', ['rating' => $rating]);
    }

    /**
     * Create system message for dispute raised
     */
    public static function disputeRaised(Grit $grit, string $raiserName, string $reason): GritMessage
    {
        return self::createSystemMessage($grit, 'dispute_raised', [
            'raiser_name' => $raiserName,
            'reason' => $reason
        ]);
    }

    /**
     * Create system message for dispute resolution
     */
    public static function disputeResolved(Grit $grit, string $resolution): GritMessage
    {
        return self::createSystemMessage($grit, 'dispute_resolved', ['resolution' => $resolution]);
    }

    /**
     * Create system message for escrow freeze
     */
    public static function escrowFrozen(Grit $grit, float $amount, string $currency, int $percentage): GritMessage
    {
        return self::createSystemMessage($grit, 'escrow_frozen', [
            'amount' => $amount,
            'currency' => $currency,
            'percentage' => $percentage
        ]);
    }

    /**
     * Create system message for escrow release
     */
    public static function escrowReleased(Grit $grit, float $amount, string $currency, int $percentage): GritMessage
    {
        return self::createSystemMessage($grit, 'escrow_released', [
            'amount' => $amount,
            'currency' => $currency,
            'percentage' => $percentage
        ]);
    }

    /**
     * Create system message for negotiation start
     */
    public static function negotiationStarted(Grit $grit, string $initiatorName): GritMessage
    {
        return self::createSystemMessage($grit, 'negotiation_started', ['initiator_name' => $initiatorName]);
    }

    /**
     * Create system message for negotiation completion
     */
    public static function negotiationCompleted(Grit $grit): GritMessage
    {
        return self::createSystemMessage($grit, 'negotiation_completed');
    }
}
