import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { 
  ArrowLeft, 
  Send, 
  Loader2, 
  Smile, 
  Paperclip,
  MoreVertical,
  User,
  Calendar,
  Clock,
  CheckCircle,
  AlertCircle
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import { gritService, type Grit } from '@/services/gritService';
import { format } from 'date-fns';
import { useGritChat } from '@/hooks/useGritChat';
import { useAuthStore } from '@/store/useAuthStore';
import { usePresence } from '@/hooks/usePresence';
import { PresenceIndicator } from '@/components/ui/PresenceIndicator';
import { ReplyMessage } from '@/components/ui/ReplyMessage';
import { cn } from '@/lib/utils';
import EmojiPicker from 'emoji-picker-react';

const ChatPage = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { user: currentUser } = useAuthStore();
  const [message, setMessage] = useState('');
  const [showEmojiPicker, setShowEmojiPicker] = useState(false);
  const [replyingTo, setReplyingTo] = useState<any>(null);
  const scrollRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const typingTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const isTypingRef = useRef(false);

  // Get GRIT details
  const { data: gritData, isLoading: gritLoading } = useQuery({
    queryKey: ['grit', id],
    queryFn: () => gritService.getGritDetails(id!),
    enabled: !!id
  });

  const grit = gritData?.grit;

  // Get messages
  const { data: messages, isLoading: messagesLoading } = useQuery({
    queryKey: ['grit-messages', id],
    queryFn: () => gritService.getGritMessages(id!),
    enabled: !!id
  });

  // Send message mutation
  const sendMessageMutation = useMutation({
    mutationFn: ({ message, replyToMessageId }: { message: string; replyToMessageId?: string }) => 
      gritService.sendGritMessage(id!, message, replyToMessageId),
    onSuccess: () => {
      setMessage('');
      setReplyingTo(null);
      queryClient.invalidateQueries({ queryKey: ['grit-messages', id] });
      // Stop typing indicator when message is sent
      handleStopTyping();
    },
    onError: () => {
      toast.error('Failed to send message');
    },
  });

  // Use real-time chat hook
  const { typingUsers } = useGritChat(id!);

  // Use presence hook
  const { usersPresence, getUsersPresence } = usePresence();
  
  // Helper function to get the correct other user ID
  const getOtherUserId = useCallback(() => {
    if (!grit || !currentUser) return undefined;
    
    let otherUserId: string | undefined;
    
    if (grit.assigned_professional_id === currentUser.id) {
      // Current user is the assigned professional, so get the business owner's user ID
      otherUserId = grit.user?.id;
    } else if (grit.assigned_professional_id && grit.assigned_professional?.user_id) {
      // Current user is the business owner, so get the professional's user ID
      otherUserId = grit.assigned_professional.user_id;
    }
    
    // Validate that we're not accidentally using a professional ID
    if (otherUserId && otherUserId === grit.assigned_professional_id) {
      // Try to get the user ID from the assigned_professional relationship
      if (grit.assigned_professional?.user_id) {
        otherUserId = grit.assigned_professional.user_id;
      } else {
        otherUserId = undefined;
      }
    }
    
    // Final validation - ensure we have a valid user ID
    if (otherUserId && otherUserId === grit.assigned_professional_id) {
      otherUserId = undefined;
    }
    
    return otherUserId;
  }, [grit, currentUser]);
  
  // Get the other user's presence (not current user)
  const otherUserId = getOtherUserId();
  
  const otherUserPresence = otherUserId ? usersPresence[otherUserId] : null;
  
  // Fetch presence for the other user when component mounts
  useEffect(() => {
    if (otherUserId) {
      // Additional validation before making the API call
      if (otherUserId === grit?.assigned_professional_id) {
        return;
      }
      getUsersPresence([otherUserId]);
    }
  }, [otherUserId, getUsersPresence, grit?.assigned_professional_id]);

  // Mark messages as read when chat is opened
  useEffect(() => {
    if (id) {
      gritService.markGritMessagesAsRead(id);
    }
  }, [id]);

  // Auto-scroll to bottom when new messages arrive
  useEffect(() => {
    if (scrollRef.current) {
      scrollRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [messages]);

  // Handle typing start with debouncing
  const handleStartTyping = useCallback(async () => {
    if (!id || isTypingRef.current) return;
    
    isTypingRef.current = true;
    try {
      await gritService.startTyping(id);
    } catch (error) {
      console.error('Failed to start typing indicator:', error);
    }
  }, [id]);

  // Handle typing stop
  const handleStopTyping = useCallback(async () => {
    if (!id || !isTypingRef.current) return;
    
    isTypingRef.current = false;
    try {
      await gritService.stopTyping(id);
    } catch (error) {
      console.error('Failed to stop typing indicator:', error);
    }
  }, [id]);

  // Debounced typing handler
  const handleTyping = useCallback((value: string) => {
    setMessage(value);
    
    // Clear existing timeout
    if (typingTimeoutRef.current) {
      clearTimeout(typingTimeoutRef.current);
    }
    
    // Start typing indicator
    if (value.trim() && !isTypingRef.current) {
      handleStartTyping();
    }
    
    // Set timeout to stop typing indicator
    typingTimeoutRef.current = setTimeout(() => {
      if (isTypingRef.current) {
        handleStopTyping();
      }
    }, 1000); // Stop typing indicator after 1 second of no input
  }, [handleStartTyping, handleStopTyping]);

  // Cleanup typing timeout on unmount
  useEffect(() => {
    return () => {
      if (typingTimeoutRef.current) {
        clearTimeout(typingTimeoutRef.current);
      }
      if (isTypingRef.current) {
        handleStopTyping();
      }
    };
  }, [handleStopTyping]);

  // Close emoji picker when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (showEmojiPicker && !event.target) return;
      const target = event.target as Element;
      if (!target.closest('.emoji-picker-container') && !target.closest('button[onclick*="setShowEmojiPicker"]')) {
        setShowEmojiPicker(false);
      }
    };
  
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [showEmojiPicker]);

  const handleSendMessage = () => {
    if (!message.trim()) return;
    sendMessageMutation.mutate({
      message: message.trim(),
      replyToMessageId: replyingTo?.id
    });
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSendMessage();
    }
  };

  const handleEmojiSelect = (emojiObject: any) => {
    setMessage(prev => prev + emojiObject.emoji);
    setShowEmojiPicker(false);
    inputRef.current?.focus();
  };

  if (gritLoading || messagesLoading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="flex items-center gap-3">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
          <span className="text-lg">Loading conversation...</span>
        </div>
      </div>
    );
  }

  // Check if chat is accessible (professional must be assigned)
  if (grit && !grit.assigned_professional_id) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">Chat Not Available</h2>
          <p className="text-muted-foreground mb-4">
            Chat is only available after a professional has been assigned to this GRIT.
          </p>
          <Button onClick={() => navigate(`/dashboard/grits/${id}`)}>
            Back to GRIT Details
          </Button>
        </div>
      </div>
    );
  }

  if (!grit) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">GRIT Not Found</h2>
          <p className="text-muted-foreground mb-4">The GRIT you're looking for doesn't exist.</p>
          <Button onClick={() => navigate('/dashboard/grits/mine')}>
            Back to My GRITs
          </Button>
        </div>
      </div>
    );
  }

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'open': return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200';
      case 'in_progress': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200';
      case 'completed': return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
      default: return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
  };

  // Get typing users list
  const typingUsersList = Object.values(typingUsers);

  return (
    <div
      className="bg-background flex flex-col overflow-hidden"
      style={{ height: '100dvh' }}
    >
      {/* Fixed Header */}
      <div className="sticky top-0 flex-shrink-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b z-20">
        <div className="flex items-start justify-between px-3 py-2 pt-6">
          <div className="flex items-center gap-3 min-w-0 flex-1">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => navigate(-1)}
              className="h-10 w-10 rounded-full hover:bg-background/80"
            >
              <ArrowLeft className="h-5 w-5" />
            </Button>
            <div className="min-w-0 flex-1">
              <h1 className="font-semibold text-sm sm:text-base leading-snug break-words whitespace-normal">{grit.title}</h1>
              <div className="mt-0.5 flex items-center gap-2 text-[11px] sm:text-xs text-muted-foreground">
                <span className="inline-block w-1 h-1 rounded-full bg-muted-foreground" />
                <Badge variant="secondary" className={cn("text-[10px]", getStatusColor(grit.status))}>
                  {grit.status.replace('_', ' ')}
                </Badge>
                {otherUserPresence && (
                  <PresenceIndicator 
                    presence={otherUserPresence} 
                    size="sm" 
                    showStatus={true}
                    showLastSeen={true}
                  />
                )}
              </div>
            </div>
          </div>
          
        </div>
      </div>

      {/* Scrollable Messages Area */}
      <div className="flex-1 min-h-0 overflow-hidden">
        <ScrollArea className="h-full p-4">
          <div className="space-y-4 max-w-4xl mx-auto">
            {(!messages || messages.length === 0) && (
              <motion.div
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                className="flex flex-col items-center justify-center text-center min-h-[60vh] py-8 text-muted-foreground"
              >
                <div className="mb-4 h-12 w-12 rounded-full bg-muted/60 flex items-center justify-center">
                  <Smile className="h-6 w-6" />
                </div>
                <h3 className="text-sm font-medium text-foreground mb-1">No messages yet</h3>
                <p className="text-xs max-w-sm mb-4">
                  Start the conversation by sending the first message.
                </p>
                <Button size="sm" onClick={() => inputRef.current?.focus()}>
                  Say hello
                </Button>
              </motion.div>
            )}
            {messages?.map((msg, index) => {
              const showDate = index === 0 || 
                new Date(msg.created_at).toDateString() !== 
                new Date(messages[index - 1]?.created_at).toDateString();

              // Handle system messages differently
              if (msg.sender_type === 'system') {
                return (
                  <React.Fragment key={msg.id}>
                    {showDate && (
                      <motion.div
                        key={`date-${msg.id}`}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="flex justify-center my-6"
                      >
                        <Badge variant="outline" className="text-xs">
                          {format(new Date(msg.created_at), 'EEEE, MMMM d, yyyy')}
                        </Badge>
                      </motion.div>
                    )}
                    
                    <motion.div
                      key={msg.id}
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      className="flex justify-center my-4"
                    >
                      <div className="bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 rounded-full px-4 py-2 max-w-[80%]">
                        <p className="text-sm text-blue-700 dark:text-blue-300 text-center">
                          {msg.message}
                        </p>
                        <div className="text-xs text-blue-500 dark:text-blue-400 text-center mt-1">
                          {format(new Date(msg.created_at), 'HH:mm')}
                        </div>
                      </div>
                    </motion.div>
                  </React.Fragment>
                );
              }

              // Determine if message is from current user (ensure string comparison for UUIDs)
              const isCurrentUser = String(msg.user?.id) === String(currentUser?.id);

              return (
                <React.Fragment key={msg.id}>
                  {showDate && (
                    <motion.div
                      key={`date-${msg.id}`}
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      className="flex justify-center my-6"
                    >
                      <Badge variant="outline" className="text-xs">
                        {format(new Date(msg.created_at), 'EEEE, MMMM d, yyyy')}
                      </Badge>
                    </motion.div>
                  )}
                  
                  <motion.div
                    key={msg.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    className={cn(
                      'flex items-start gap-3 group',
                      isCurrentUser ? 'flex-row-reverse' : ''
                    )}
                  >
                    <div className="relative">
                      <Avatar className="h-8 w-8 shrink-0">
                        <AvatarImage src={msg.user?.avatar} />
                        <AvatarFallback>
                          {msg.user ? (msg.user.first_name?.[0] || msg.user.last_name?.[0] || msg.user.username?.[0] || '?') : 'S'}
                        </AvatarFallback>
                      </Avatar>
                      {/* Presence indicator for other users */}
                      {!isCurrentUser && msg.user?.id && usersPresence[msg.user.id] && (
                        <div className="absolute -bottom-1 -right-1">
                          <PresenceIndicator 
                            presence={usersPresence[msg.user.id]} 
                            size="sm" 
                            showStatus={false}
                          />
                        </div>
                      )}
                    </div>
                    
                    <div className="flex flex-col gap-1 max-w-[70%]">
                      {/* Reply preview */}
                      {msg.reply_to && (
                        <ReplyMessage 
                          replyTo={msg.reply_to} 
                          className={isCurrentUser ? 'mr-2' : 'ml-2'}
                        />
                      )}
                      
                      <div
                        className={cn(
                          'relative rounded-2xl px-4 py-3 shadow-sm',
                          isCurrentUser
                            ? 'bg-primary text-primary-foreground rounded-tr-none'
                            : 'bg-muted rounded-tl-none'
                        )}
                      >
                        <p className="text-sm whitespace-pre-wrap break-words leading-relaxed">
                          {msg.message}
                        </p>
                      </div>
                      
                      <div className={cn(
                        'flex items-center gap-2 text-xs text-muted-foreground',
                        isCurrentUser ? 'justify-end' : 'justify-start'
                      )}>
                        <span>{format(new Date(msg.created_at), 'HH:mm')}</span>
                        {msg.is_read && (
                          <CheckCircle className="h-3 w-3 text-green-500" />
                        )}
                        <Button
                          variant="ghost"
                          size="sm"
                          className="h-6 w-6 p-0 opacity-0 group-hover:opacity-100 transition-opacity"
                          onClick={() => setReplyingTo(msg)}
                        >
                          <svg className="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                          </svg>
                        </Button>
                      </div>
                    </div>
                  </motion.div>
                </React.Fragment>
              );
            })}
            
            {/* Typing indicators */}
            {typingUsersList.map((typingUser: any) => (
              <motion.div
                key={typingUser.user.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="flex items-start gap-3"
              >
                <div className="relative">
                  <Avatar className="h-8 w-8 shrink-0">
                    <AvatarImage src={typingUser.user.avatar} />
                    <AvatarFallback>
                      {typingUser.user ? (typingUser.user.first_name?.[0] || typingUser.user.last_name?.[0] || typingUser.user.username?.[0] || '?') : '?'}
                    </AvatarFallback>
                  </Avatar>
                  {/* Presence indicator for typing users */}
                  {typingUser.user?.id && usersPresence[typingUser.user.id] && (
                    <div className="absolute -bottom-1 -right-1">
                      <PresenceIndicator 
                        presence={usersPresence[typingUser.user.id]} 
                        size="sm" 
                        showStatus={false}
                      />
                    </div>
                  )}
                </div>
                <div className="bg-muted rounded-2xl rounded-tl-none px-4 py-3">
                  <div className="flex items-center gap-1">
                    <div className="flex gap-1">
                      <div className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" />
                      <div className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" style={{ animationDelay: '0.1s' }} />
                      <div className="w-2 h-2 bg-muted-foreground rounded-full animate-bounce" style={{ animationDelay: '0.2s' }} />
                    </div>
                    <span className="text-xs text-muted-foreground ml-2">typing...</span>
                  </div>
                </div>
              </motion.div>
            ))}
            
            <div ref={scrollRef} />
          </div>
        </ScrollArea>
      </div>

      {/* Fixed Message Input */}
      <div className="sticky bottom-0 flex-shrink-0 border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 z-20">
        <div className="max-w-4xl mx-auto p-4">
          {/* Reply Preview */}
          {replyingTo && (
            <div className="mb-3 p-3 bg-muted/50 rounded-lg border-l-2 border-primary/30">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <span className="text-xs font-medium text-muted-foreground">Replying to</span>
                  <span className="text-xs text-muted-foreground">
                    {replyingTo.user?.first_name} {replyingTo.user?.last_name}
                  </span>
                </div>
                <Button
                  variant="ghost"
                  size="sm"
                  className="h-6 w-6 p-0"
                  onClick={() => setReplyingTo(null)}
                >
                  <svg className="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </Button>
              </div>
              <p className="text-xs text-muted-foreground mt-1 line-clamp-2">
                {replyingTo.message}
              </p>
            </div>
          )}
          
          <div className="flex items-end gap-3">
            <div className="flex-1 relative">
              <Input
                ref={inputRef}
                placeholder="Type your message..."
                value={message}
                onChange={(e) => handleTyping(e.target.value)}
                onKeyPress={handleKeyPress}
                className="pr-20 py-4 rounded-2xl border-2 focus:border-primary"
                disabled={sendMessageMutation.isPending}
              />
              <div className="absolute right-2 bottom-2 flex items-center gap-1">
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button
                      size="icon"
                      variant="ghost"
                      className="h-8 w-8 rounded-full opacity-70 hover:opacity-100"
                    >
                      <Paperclip className="h-4 w-4" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Attach file</TooltipContent>
                </Tooltip>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button
                      size="icon"
                      variant="ghost"
                      className="h-8 w-8 rounded-full opacity-70 hover:opacity-100"
                      onClick={() => setShowEmojiPicker(!showEmojiPicker)}
                    >
                      <Smile className="h-4 w-4" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Add emoji</TooltipContent>
                </Tooltip>
              </div>
            </div>
            
            {/* Emoji Picker */}
            {showEmojiPicker && (
              <div className="absolute bottom-full right-0 mb-2 z-30 emoji-picker-container">
                <div className="bg-background border rounded-lg shadow-lg">
                  <EmojiPicker
                    onEmojiClick={handleEmojiSelect}
                    autoFocusSearch={false}
                    searchDisabled={true}
                    skinTonesDisabled={true}
                    width={window.innerWidth < 768 ? 280 : 320}
                    height={window.innerWidth < 768 ? 350 : 400}
                  />
                </div>
              </div>
            )}
            
            <Button
              size="icon"
              onClick={handleSendMessage}
              disabled={!message.trim() || sendMessageMutation.isPending}
              className="h-12 w-12 rounded-full"
            >
              {sendMessageMutation.isPending ? (
                <Loader2 className="h-5 w-5 animate-spin" />
              ) : (
                <Send className="h-5 w-5" />
              )}
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ChatPage;
