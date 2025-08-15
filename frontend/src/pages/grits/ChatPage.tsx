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
import { cn } from '@/lib/utils';

const ChatPage = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { user: currentUser } = useAuthStore();
  const [message, setMessage] = useState('');
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
    mutationFn: (message: string) => gritService.sendGritMessage(id!, message),
    onSuccess: () => {
      setMessage('');
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

  const handleSendMessage = () => {
    if (!message.trim()) return;
    sendMessageMutation.mutate(message);
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSendMessage();
    }
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
      <div className="sticky top-4 flex-shrink-0 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b z-20">
        <div className="flex items-start justify-between px-3 py-2">
          <div className="flex items-center gap-3 min-w-0 flex-1">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => navigate(-1)}
              className="h-10 w-10 rounded-full hover:bg-background/80"
            >
              <ArrowLeft className="h-5 w-5" />
            </Button>
            <div className="flex items-center gap-3 min-w-0 flex-1">
              <Avatar className="h-8 w-8">
                <AvatarImage src={grit.user?.avatar} />
                <AvatarFallback>
                  {grit.user?.name?.charAt(0).toUpperCase()}
                </AvatarFallback>
              </Avatar>
              <div className="min-w-0 flex-1">
                <h1 className="font-semibold text-sm sm:text-base leading-snug break-words whitespace-normal">{grit.title}</h1>
                <div className="mt-0.5 flex items-center gap-2 text-[11px] sm:text-xs text-muted-foreground">
                  <span className="inline-block w-1 h-1 rounded-full bg-muted-foreground" />
                  <Badge variant="secondary" className={cn("text-[10px]", getStatusColor(grit.status))}>
                    {grit.status.replace('_', ' ')}
                  </Badge>
                </div>
              </div>
            </div>
          </div>
          
        </div>
      </div>

      {/* Scrollable Messages Area */}
      <div className="flex-1 overflow-hidden">
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
                    <Avatar className="h-8 w-8 shrink-0">
                      <AvatarImage src={msg.user?.avatar} />
                      <AvatarFallback>
                        {msg.user ? (msg.user.first_name?.[0] || msg.user.last_name?.[0] || msg.user.username?.[0] || '?') : 'S'}
                      </AvatarFallback>
                    </Avatar>
                    
                    <div className="flex flex-col gap-1 max-w-[70%]">
                      
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
                <Avatar className="h-8 w-8 shrink-0">
                  <AvatarImage src={typingUser.user.avatar} />
                  <AvatarFallback>
                    {typingUser.user ? (typingUser.user.first_name?.[0] || typingUser.user.last_name?.[0] || typingUser.user.username?.[0] || '?') : '?'}
                  </AvatarFallback>
                </Avatar>
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
                    >
                      <Smile className="h-4 w-4" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Add emoji</TooltipContent>
                </Tooltip>
              </div>
            </div>
            
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
