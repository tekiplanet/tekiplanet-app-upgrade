<?php

namespace App\Http\Controllers;

use App\Models\Grit;
use App\Models\GritMessage;
use App\Events\NewGritMessage;
use App\Events\GritTypingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GritMessageController extends Controller
{
    /**
     * Get messages for a specific GRIT
     */
    public function getMessages($gritId)
    {
        try {
            $grit = Grit::findOrFail($gritId);
            
            // Check if user has access to this GRIT
            $user = Auth::user();
            $hasAccess = false;
            
            // Business owner can access their own GRITs
            if ($grit->created_by_user_id === $user->id) {
                $hasAccess = true;
            }
            
            // Assigned professional can access the GRIT
            if ($grit->assigned_professional_id && $grit->assignedProfessional->user_id === $user->id) {
                $hasAccess = true;
            }
            
            // Admin can access all GRITs
            if ($user->is_admin) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            $messages = GritMessage::with(['user', 'replyTo.user'])
                ->where('grit_id', $gritId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            return response()->json(['messages' => $messages]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching GRIT messages:', ['grit_id' => $gritId, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch messages'], 500);
        }
    }
    
    /**
     * Send a message for a specific GRIT
     */
    public function store(Request $request, $gritId)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
                'reply_to_message_id' => 'nullable|uuid|exists:grit_messages,id'
            ]);
            
            $grit = Grit::findOrFail($gritId);
            $user = Auth::user();
            
            // Check if user has access to this GRIT
            $hasAccess = false;
            $senderType = null;
            
            // Business owner can send messages to their own GRITs
            if ($grit->created_by_user_id === $user->id) {
                $hasAccess = true;
                $senderType = 'owner';
            }
            
            // Assigned professional can send messages to the GRIT
            if ($grit->assigned_professional_id && $grit->assignedProfessional->user_id === $user->id) {
                $hasAccess = true;
                $senderType = 'professional';
            }
            
            // Admin can send messages to all GRITs
            if ($user->is_admin) {
                $hasAccess = true;
                $senderType = 'admin';
            }
            
            if (!$hasAccess) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            $message = GritMessage::create([
                'grit_id' => $gritId,
                'user_id' => $user->id,
                'message' => $request->message,
                'sender_type' => $senderType,
                'reply_to_message_id' => $request->reply_to_message_id,
                'is_read' => false
            ]);
            
            $message->load(['user', 'replyTo.user']);
            
            // Broadcast the new message to all users in the GRIT chat
            broadcast(new NewGritMessage($message))->toOthers();
            
            return response()->json(['message' => $message], 201);
            
        } catch (\Exception $e) {
            Log::error('Error sending GRIT message:', ['grit_id' => $gritId, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to send message'], 500);
        }
    }
    
    /**
     * Mark messages as read for a specific GRIT
     */
    public function markAsRead($gritId)
    {
        try {
            $grit = Grit::findOrFail($gritId);
            $user = Auth::user();
            
            // Check if user has access to this GRIT
            $hasAccess = false;
            
            // Business owner can mark messages as read for their own GRITs
            if ($grit->created_by_user_id === $user->id) {
                $hasAccess = true;
            }
            
            // Assigned professional can mark messages as read
            if ($grit->assigned_professional_id && $grit->assignedProfessional->user_id === $user->id) {
                $hasAccess = true;
            }
            
            // Admin can mark messages as read for all GRITs
            if ($user->is_admin) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Mark all unread messages as read
            GritMessage::where('grit_id', $gritId)
                ->where('user_id', '!=', $user->id) // Don't mark own messages as read
                ->where('is_read', false)
                ->update(['is_read' => true]);
            
            return response()->json(['message' => 'Messages marked as read']);
            
        } catch (\Exception $e) {
            Log::error('Error marking GRIT messages as read:', ['grit_id' => $gritId, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to mark messages as read'], 500);
        }
    }
    
    /**
     * Start typing indicator for a specific GRIT
     */
    public function startTyping($gritId)
    {
        try {
            $grit = Grit::findOrFail($gritId);
            $user = Auth::user();
            
            // Check if user has access to this GRIT
            $hasAccess = false;
            $senderType = null;
            
            // Business owner can send typing events to their own GRITs
            if ($grit->created_by_user_id === $user->id) {
                $hasAccess = true;
                $senderType = 'owner';
            }
            
            // Assigned professional can send typing events to the GRIT
            if ($grit->assigned_professional_id && $grit->assignedProfessional->user_id === $user->id) {
                $hasAccess = true;
                $senderType = 'professional';
            }
            
            // Admin can send typing events to all GRITs
            if ($user->is_admin) {
                $hasAccess = true;
                $senderType = 'admin';
            }
            
            if (!$hasAccess) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Broadcast typing start event
            broadcast(new GritTypingEvent($gritId, $user->id, $senderType, true))->toOthers();
            
            return response()->json(['message' => 'Typing started']);
            
        } catch (\Exception $e) {
            Log::error('Error starting typing indicator:', ['grit_id' => $gritId, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to start typing indicator'], 500);
        }
    }
    
    /**
     * Stop typing indicator for a specific GRIT
     */
    public function stopTyping($gritId)
    {
        try {
            $grit = Grit::findOrFail($gritId);
            $user = Auth::user();
            
            // Check if user has access to this GRIT
            $hasAccess = false;
            $senderType = null;
            
            // Business owner can send typing events to their own GRITs
            if ($grit->created_by_user_id === $user->id) {
                $hasAccess = true;
                $senderType = 'owner';
            }
            
            // Assigned professional can send typing events to the GRIT
            if ($grit->assigned_professional_id && $grit->assignedProfessional->user_id === $user->id) {
                $hasAccess = true;
                $senderType = 'professional';
            }
            
            // Admin can send typing events to all GRITs
            if ($user->is_admin) {
                $hasAccess = true;
                $senderType = 'admin';
            }
            
            if (!$hasAccess) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Broadcast typing stop event
            broadcast(new GritTypingEvent($gritId, $user->id, $senderType, false))->toOthers();
            
            return response()->json(['message' => 'Typing stopped']);
            
        } catch (\Exception $e) {
            Log::error('Error stopping typing indicator:', ['grit_id' => $gritId, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to stop typing indicator'], 500);
        }
    }
}
