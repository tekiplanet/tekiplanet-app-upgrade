import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { 
  Search, 
  Filter, 
  MessageSquare, 
  Clock, 
  CheckCircle,
  AlertCircle,
  Loader2,
  Plus,
  MoreVertical,
  User,
  Calendar,
  DollarSign
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { gritService } from '@/services/gritService';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';

const ChatListPage = () => {
  const navigate = useNavigate();
  const [searchTerm, setSearchTerm] = useState('');
  const [filter, setFilter] = useState<'all' | 'unread' | 'recent'>('all');

  // Get user's GRITs (both created and applied)
  const { data: myGrits, isLoading } = useQuery({
    queryKey: ['my-grits'],
    queryFn: gritService.getMyGrits
  });

  // Get professional applications
  const { data: applications } = useQuery({
    queryKey: ['my-grit-applications'],
    queryFn: gritService.getMyGritApplications,
    enabled: true
  });

  // Combine and filter conversations
  const conversations = React.useMemo(() => {
    const allConversations = [];

    // Add GRITs created by user (business owner perspective)
    if (myGrits) {
      myGrits.forEach(grit => {
        if (grit.assigned_professional_id || grit.applications_count > 0) {
          allConversations.push({
            id: grit.id,
            type: 'owned',
            title: grit.title,
            status: grit.status,
            unreadCount: grit.unread_messages_count || 0,
            lastMessage: grit.last_message,
            lastMessageTime: grit.last_message_time,
            user: grit.assigned_professional || grit.user,
            budget: grit.owner_budget,
            currency: grit.owner_currency || 'USD',
            category: grit.category,
            isActive: grit.status === 'open' || grit.status === 'in_progress'
          });
        }
      });
    }

    // Add GRITs where user applied (professional perspective)
    if (applications) {
      applications.forEach(app => {
        if (app.grit && app.status === 'approved') {
          allConversations.push({
            id: app.grit.id,
            type: 'applied',
            title: app.grit.title,
            status: app.grit.status,
            unreadCount: app.unread_messages_count || 0,
            lastMessage: app.last_message,
            lastMessageTime: app.last_message_time,
            user: app.grit.user,
            budget: app.grit.owner_budget,
            currency: app.grit.owner_currency || 'USD',
            category: app.grit.category,
            isActive: app.grit.status === 'open' || app.grit.status === 'in_progress'
          });
        }
      });
    }

    // Filter by search term
    let filtered = allConversations;
    if (searchTerm) {
      filtered = filtered.filter(conv => 
        conv.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        conv.user?.name?.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }

    // Filter by type
    if (filter === 'unread') {
      filtered = filtered.filter(conv => conv.unreadCount > 0);
    } else if (filter === 'recent') {
      filtered = filtered.filter(conv => conv.isActive);
    }

    // Sort by last message time (most recent first)
    return filtered.sort((a, b) => {
      if (!a.lastMessageTime && !b.lastMessageTime) return 0;
      if (!a.lastMessageTime) return 1;
      if (!b.lastMessageTime) return -1;
      return new Date(b.lastMessageTime).getTime() - new Date(a.lastMessageTime).getTime();
    });
  }, [myGrits, applications, searchTerm, filter]);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'open': return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200';
      case 'in_progress': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200';
      case 'completed': return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
      case 'disputed': return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200';
      default: return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'open': return <CheckCircle className="h-4 w-4" />;
      case 'in_progress': return <Clock className="h-4 w-4" />;
      case 'completed': return <CheckCircle className="h-4 w-4" />;
      case 'disputed': return <AlertCircle className="h-4 w-4" />;
      default: return <Clock className="h-4 w-4" />;
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="flex items-center gap-3">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
          <span className="text-lg">Loading conversations...</span>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <div className="sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b">
        <div className="max-w-4xl mx-auto p-4">
          <div className="flex items-center justify-between mb-4">
            <div>
              <h1 className="text-2xl font-bold">Messages</h1>
              <p className="text-muted-foreground">
                {conversations.length} conversation{conversations.length !== 1 ? 's' : ''}
              </p>
            </div>
            <Button onClick={() => navigate('/dashboard/grits')}>
              <Plus className="h-4 w-4 mr-2" />
              Browse GRITs
            </Button>
          </div>

          {/* Search and Filters */}
          <div className="flex items-center gap-3">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Search conversations..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
            <div className="flex items-center gap-2">
              <Button
                variant={filter === 'all' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilter('all')}
              >
                All
              </Button>
              <Button
                variant={filter === 'unread' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilter('unread')}
              >
                Unread
              </Button>
              <Button
                variant={filter === 'recent' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilter('recent')}
              >
                Active
              </Button>
            </div>
          </div>
        </div>
      </div>

      {/* Conversations List */}
      <div className="max-w-4xl mx-auto p-4">
        {conversations.length === 0 ? (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center py-12"
          >
            <MessageSquare className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
            <h3 className="text-lg font-semibold mb-2">No conversations yet</h3>
            <p className="text-muted-foreground mb-6">
              {searchTerm 
                ? 'No conversations match your search.'
                : 'Start by creating a GRIT or applying to one to begin conversations.'
              }
            </p>
            <Button onClick={() => navigate('/dashboard/grits')}>
              Browse GRITs
            </Button>
          </motion.div>
        ) : (
          <div className="space-y-2">
            {conversations.map((conversation, index) => (
              <motion.div
                key={conversation.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: index * 0.05 }}
              >
                <Card 
                  className={cn(
                    "cursor-pointer transition-all hover:shadow-md hover:scale-[1.02]",
                    conversation.unreadCount > 0 && "ring-2 ring-primary/20 bg-primary/5"
                  )}
                  onClick={() => navigate(`/dashboard/grits/${conversation.id}/chat`)}
                >
                  <CardContent className="p-4">
                    <div className="flex items-start gap-4">
                      {/* Avatar */}
                      <Avatar className="h-12 w-12 shrink-0">
                        <AvatarImage src={conversation.user?.avatar} />
                        <AvatarFallback>
                          {conversation.user?.name?.charAt(0).toUpperCase()}
                        </AvatarFallback>
                      </Avatar>

                      {/* Content */}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-start justify-between gap-2">
                          <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 mb-1">
                              <h3 className="font-semibold truncate">{conversation.title}</h3>
                              <Badge variant="outline" className="text-xs shrink-0">
                                {conversation.type === 'owned' ? 'Owner' : 'Professional'}
                              </Badge>
                            </div>
                            
                            <div className="flex items-center gap-2 text-sm text-muted-foreground mb-2">
                              <span>{conversation.user?.name}</span>
                              <span>•</span>
                              <span>{conversation.category?.name}</span>
                              <span>•</span>
                              <span className="flex items-center gap-1">
                                <DollarSign className="h-3 w-3" />
                                {conversation.budget?.toLocaleString()} {conversation.currency}
                              </span>
                            </div>

                            {conversation.lastMessage && (
                              <p className="text-sm text-muted-foreground truncate">
                                {conversation.lastMessage}
                              </p>
                            )}
                          </div>

                          {/* Status and Time */}
                          <div className="flex flex-col items-end gap-2 shrink-0">
                            {conversation.lastMessageTime && (
                              <span className="text-xs text-muted-foreground">
                                {format(new Date(conversation.lastMessageTime), 'MMM d')}
                              </span>
                            )}
                            
                            <div className="flex items-center gap-2">
                              <Badge 
                                variant="secondary" 
                                className={cn("text-xs", getStatusColor(conversation.status))}
                              >
                                {getStatusIcon(conversation.status)}
                                <span className="ml-1">{conversation.status.replace('_', ' ')}</span>
                              </Badge>
                              
                              {conversation.unreadCount > 0 && (
                                <Badge className="h-6 w-6 rounded-full p-0 text-xs">
                                  {conversation.unreadCount}
                                </Badge>
                              )}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default ChatListPage;
