import React, { useState, useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Card, 
  CardContent, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  Search, 
  Filter, 
  Plus, 
  Calendar, 
  DollarSign, 
  Users, 
  Clock, 
  CheckCircle, 
  XCircle, 
  AlertCircle,
  Play,
  Pause,
  CheckSquare,
  MessageSquare,
  Eye,
  Edit,
  MoreHorizontal,
  TrendingUp,
  TrendingDown,
  Zap
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { gritService, type Grit } from '@/services/gritService';
import { formatDate, cn } from '@/lib/utils';
import { useCurrencyFormat } from '@/lib/currency';

const MyGrits = () => {
  const navigate = useNavigate();
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [sortBy, setSortBy] = useState('latest');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

  const { data, isLoading } = useQuery({
    queryKey: ['my-grits'],
    queryFn: gritService.getMyGrits
  });

  const grits: Grit[] = data ?? [];

  // Filter and sort grits
  const filteredAndSortedGrits = useMemo(() => {
    let filtered = grits.filter(grit => {
      const matchesSearch = grit.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                           grit.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
                           grit.category?.name.toLowerCase().includes(searchTerm.toLowerCase());
      
      const matchesStatus = statusFilter === 'all' || grit.status === statusFilter;
      
      return matchesSearch && matchesStatus;
    });

    // Sort grits
    switch (sortBy) {
      case 'latest':
        filtered.sort((a, b) => new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime());
        break;
      case 'deadline':
        filtered.sort((a, b) => new Date(a.deadline).getTime() - new Date(b.deadline).getTime());
        break;
      case 'budget-high':
        filtered.sort((a, b) => (b.owner_budget || 0) - (a.owner_budget || 0));
        break;
      case 'budget-low':
        filtered.sort((a, b) => (a.owner_budget || 0) - (b.owner_budget || 0));
        break;
      case 'applications':
        filtered.sort((a, b) => (b.applications_count || 0) - (a.applications_count || 0));
        break;
    }

    return filtered;
  }, [grits, searchTerm, statusFilter, sortBy]);

  const getStatusConfig = (status: string, adminStatus: string) => {
    if (adminStatus === 'rejected') {
      return {
        label: 'Rejected',
        color: 'destructive',
        icon: XCircle,
        bgColor: 'bg-red-50 dark:bg-red-950/20',
        textColor: 'text-red-700 dark:text-red-400'
      };
    }

    if (adminStatus === 'pending') {
      return {
        label: 'Pending Approval',
        color: 'secondary',
        icon: Clock,
        bgColor: 'bg-yellow-50 dark:bg-yellow-950/20',
        textColor: 'text-yellow-700 dark:text-yellow-600'
      };
    }

    switch (status) {
      case 'open':
        return {
          label: 'Open',
          color: 'default',
          icon: Zap,
          bgColor: 'bg-green-50 dark:bg-green-950/20',
          textColor: 'text-green-700 dark:text-green-400'
        };
      case 'negotiation':
        return {
          label: 'In Negotiation',
          color: 'secondary',
          icon: TrendingUp,
          bgColor: 'bg-blue-50 dark:bg-blue-950/20',
          textColor: 'text-blue-700 dark:text-blue-400'
        };
      case 'in_progress':
        return {
          label: 'In Progress',
          color: 'default',
          icon: Play,
          bgColor: 'bg-purple-50 dark:bg-purple-950/20',
          textColor: 'text-purple-700 dark:text-purple-400'
        };
      case 'pending_completion_approval':
        return {
          label: 'Pending Completion',
          color: 'secondary',
          icon: CheckSquare,
          bgColor: 'bg-orange-50 dark:bg-orange-950/20',
          textColor: 'text-orange-700 dark:text-orange-400'
        };
      case 'completed':
        return {
          label: 'Completed',
          color: 'default',
          icon: CheckCircle,
          bgColor: 'bg-emerald-50 dark:bg-emerald-950/20',
          textColor: 'text-emerald-700 dark:text-emerald-400'
        };
      case 'disputed':
        return {
          label: 'Disputed',
          color: 'destructive',
          icon: AlertCircle,
          bgColor: 'bg-red-50 dark:bg-red-950/20',
          textColor: 'text-red-700 dark:text-red-400'
        };
      case 'closed':
        return {
          label: 'Closed',
          color: 'secondary',
          icon: Pause,
          bgColor: 'bg-gray-50 dark:bg-gray-950/20',
          textColor: 'text-gray-700 dark:text-gray-400'
        };
      default:
        return {
          label: 'Unknown',
          color: 'secondary',
          icon: AlertCircle,
          bgColor: 'bg-gray-50 dark:bg-gray-950/20',
          textColor: 'text-gray-700 dark:text-gray-400'
        };
    }
  };

  const getProgressPercentage = (status: string) => {
    switch (status) {
      case 'open': return 25;
      case 'negotiation': return 50;
      case 'in_progress': return 75;
      case 'pending_completion_approval': return 90;
      case 'completed': return 100;
      case 'disputed': return 0;
      case 'closed': return 100;
      default: return 0;
    }
  };

  const BudgetAmount = ({ amount, currencyCode }: { amount: number | string; currencyCode?: string }) => {
    const { formattedAmount } = useCurrencyFormat(amount, currencyCode);
    return <>{formattedAmount}</>;
  };

  const GritCard = ({ grit }: { grit: Grit }) => {
    const statusConfig = getStatusConfig(grit.status, grit.admin_approval_status);
    const progressPercentage = getProgressPercentage(grit.status);
    const StatusIcon = statusConfig.icon;

    return (
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.3 }}
        whileHover={{ y: -4 }}
        className="group"
      >
        <Card className="h-full overflow-hidden border-0 shadow-sm hover:shadow-lg transition-all duration-300 bg-gradient-to-br from-white to-gray-50/50 dark:from-gray-900 dark:to-gray-800/50">
          <CardHeader className="pb-3">
            <div className="flex items-start justify-between">
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-2">
                  <Badge 
                    variant="outline" 
                    className="text-xs font-medium px-2 py-1 bg-white/80 dark:bg-gray-800/80"
                  >
                    {grit.category?.name}
                  </Badge>
                  <div className={cn(
                    "flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium",
                    statusConfig.bgColor,
                    statusConfig.textColor
                  )}>
                    <StatusIcon className="w-3 h-3" />
                    {statusConfig.label}
                  </div>
                </div>
                <CardTitle className="text-lg font-semibold leading-tight line-clamp-2 group-hover:text-primary transition-colors">
                  {grit.title}
                </CardTitle>
              </div>
              {/* Only show edit button if GRIT can be edited */}
              {!grit.assigned_professional_id && 
               grit.status === 'open' && 
               (grit.applications_count || 0) === 0 && (
                <Button
                  variant="ghost"
                  size="sm"
                  className="opacity-0 group-hover:opacity-100 transition-opacity"
                  onClick={(e) => {
                    e.stopPropagation();
                    navigate(`/dashboard/grits/${grit.id}/edit`);
                  }}
                >
                  <Edit className="w-4 h-4" />
                </Button>
              )}
            </div>
          </CardHeader>

          <CardContent className="pt-0 space-y-4">
            <p className="text-sm text-muted-foreground line-clamp-2 leading-relaxed">
              {grit.description}
            </p>

            {/* Progress Bar */}
            <div className="space-y-2">
              <div className="flex items-center justify-between text-xs text-muted-foreground">
                <span>Progress</span>
                <span>{progressPercentage}%</span>
              </div>
              <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div 
                  className="h-2 bg-gradient-to-r from-primary to-primary/70 rounded-full transition-all duration-500"
                  style={{ width: `${progressPercentage}%` }}
                />
              </div>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-2 gap-3 text-sm">
              <div className="flex items-center gap-2 text-muted-foreground">
                <DollarSign className="w-4 h-4" />
                <span className="font-medium">
                  <BudgetAmount amount={grit.owner_budget || 0} currencyCode={(grit as any).owner_currency || grit.currency} />
                </span>
              </div>
              <div className="flex items-center gap-2 text-muted-foreground">
                <Calendar className="w-4 h-4" />
                <span className="font-medium">
                  {formatDate(grit.deadline)}
                </span>
              </div>
              <div className="flex items-center gap-2 text-muted-foreground">
                <Users className="w-4 h-4" />
                <span className="font-medium">
                  {grit.applications_count || 0} applications
                </span>
              </div>
              <div className="flex items-center gap-2 text-muted-foreground">
                <MessageSquare className="w-4 h-4" />
                <span className="font-medium">
                  {grit.unread_messages_count || 0} unread
                </span>
              </div>
            </div>

            {/* Skills */}
            {grit.skills_required && grit.skills_required.length > 0 && (
              <div className="flex flex-wrap gap-1">
                {grit.skills_required.slice(0, 3).map((skill, index) => (
                  <Badge key={index} variant="secondary" className="text-xs px-2 py-1">
                    {skill}
                  </Badge>
                ))}
                {grit.skills_required.length > 3 && (
                  <Badge variant="outline" className="text-xs px-2 py-1">
                    +{grit.skills_required.length - 3} more
                  </Badge>
                )}
              </div>
            )}

            {/* Action Buttons */}
            <div className="flex gap-2 pt-2">
              <Button 
                variant="outline" 
                size="sm" 
                className="flex-1"
                onClick={(e) => {
                  e.stopPropagation();
                  navigate(`/dashboard/grits/${grit.id}`);
                }}
              >
                <Eye className="w-4 h-4 mr-2" />
                View Details
              </Button>
              {grit.applications_count && grit.applications_count > 0 && (
                <Button 
                  variant="secondary" 
                  size="sm"
                  onClick={(e) => {
                    e.stopPropagation();
                    navigate(`/dashboard/grits/${grit.id}/applications`);
                  }}
                >
                  <Users className="w-4 h-4 mr-2" />
                  {grit.applications_count}
                </Button>
              )}
            </div>
          </CardContent>
        </Card>
      </motion.div>
    );
  };

  const GritList = ({ grits }: { grits: Grit[] }) => (
    <div className="space-y-4">
      {grits.map((grit) => (
        <motion.div
          key={grit.id}
          initial={{ opacity: 0, x: -20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.3 }}
          whileHover={{ x: 4 }}
        >
          <Card className="cursor-pointer hover:shadow-md transition-all duration-300" onClick={() => navigate(`/dashboard/grits/${grit.id}`)}>
            <CardContent className="p-6">
              <div className="flex items-start gap-4">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-3 mb-3">
                    <Badge variant="outline">{grit.category?.name}</Badge>
                    <div className={cn(
                      "flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium",
                      getStatusConfig(grit.status, grit.admin_approval_status).bgColor,
                      getStatusConfig(grit.status, grit.admin_approval_status).textColor
                    )}>
                      {React.createElement(getStatusConfig(grit.status, grit.admin_approval_status).icon, { className: "w-4 h-4" })}
                      {getStatusConfig(grit.status, grit.admin_approval_status).label}
                    </div>
                  </div>
                  <h3 className="text-xl font-semibold mb-2 line-clamp-1">{grit.title}</h3>
                  <p className="text-muted-foreground mb-4 line-clamp-2">{grit.description}</p>
                  
                  <div className="grid grid-cols-4 gap-4 text-sm">
                    <div className="flex items-center gap-2">
                      <DollarSign className="w-4 h-4 text-muted-foreground" />
                      <span className="font-medium">
                        <BudgetAmount amount={grit.owner_budget || 0} currencyCode={(grit as any).owner_currency || grit.currency} />
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Calendar className="w-4 h-4 text-muted-foreground" />
                      <span className="font-medium">{formatDate(grit.deadline)}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Users className="w-4 h-4 text-muted-foreground" />
                      <span className="font-medium">{grit.applications_count || 0} applications</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <MessageSquare className="w-4 h-4 text-muted-foreground" />
                      <span className="font-medium">{grit.unread_messages_count || 0} unread</span>
                    </div>
                  </div>
                </div>
                <Button variant="ghost" size="sm">
                  <MoreHorizontal className="w-4 h-4" />
                </Button>
              </div>
            </CardContent>
          </Card>
        </motion.div>
      ))}
    </div>
  );

  return (
    <div className="min-h-screen bg-gradient-to-b from-background to-primary/5 p-2 sm:p-4 md:p-6">
      <div className="max-w-7xl mx-auto">
        {/* Header Section */}
        <div className="mb-8">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h1 className="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-primary to-primary/70 bg-clip-text text-transparent">
                My Grits
              </h1>
              <p className="text-muted-foreground mt-2">
                Manage and track your project requests
              </p>
            </div>
            <Button 
              onClick={() => navigate('/dashboard/grits/create')}
              className="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 shadow-lg hover:shadow-xl transition-all duration-300"
            >
              <Plus className="w-4 h-4 mr-2" />
              Create New Grit
            </Button>
          </div>

          {/* Stats Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {[
              { label: 'Total Grits', value: grits.length, icon: Zap, color: 'from-blue-500 to-blue-600' },
              { label: 'Active Projects', value: grits.filter(g => g.status === 'in_progress').length, icon: Play, color: 'from-green-500 to-green-600' },
              { label: 'Pending Approval', value: grits.filter(g => g.admin_approval_status === 'pending').length, icon: Clock, color: 'from-yellow-500 to-yellow-600' },
              { label: 'Total Applications', value: grits.reduce((sum, g) => sum + (g.applications_count || 0), 0), icon: Users, color: 'from-purple-500 to-purple-600' }
            ].map((stat, index) => (
              <motion.div
                key={stat.label}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.3, delay: index * 0.1 }}
              >
                <Card className="relative overflow-hidden border-none bg-gradient-to-br backdrop-blur-xl hover:shadow-lg transition-all dark:shadow-none rounded-2xl dark:bg-background/50">
                  <CardContent className="p-4">
                    <div className="flex items-center gap-3">
                      <div className={`p-2 rounded-lg bg-gradient-to-r ${stat.color} text-white`}>
                        <stat.icon className="w-5 h-5" />
                      </div>
                      <div>
                        <p className="text-2xl font-bold">{stat.value}</p>
                        <p className="text-sm text-muted-foreground">{stat.label}</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>
        </div>

        {/* Filters and Controls */}
        <Card className="mb-6 border-none bg-background/50 backdrop-blur-xl">
          <CardContent className="p-4">
            <div className="flex flex-col lg:flex-row gap-4 items-start lg:items-center">
              {/* Search */}
              <div className="relative flex-1 max-w-md">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                <Input
                  placeholder="Search grits..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10"
                />
              </div>

              {/* Status Filter */}
              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger className="w-full lg:w-48">
                  <Filter className="w-4 h-4 mr-2" />
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Statuses</SelectItem>
                  <SelectItem value="open">Open</SelectItem>
                  <SelectItem value="negotiation">In Negotiation</SelectItem>
                  <SelectItem value="in_progress">In Progress</SelectItem>
                  <SelectItem value="pending_completion_approval">Pending Completion</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="disputed">Disputed</SelectItem>
                  <SelectItem value="closed">Closed</SelectItem>
                </SelectContent>
              </Select>

              {/* Sort */}
              <Select value={sortBy} onValueChange={setSortBy}>
                <SelectTrigger className="w-full lg:w-48">
                  <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="latest">Latest First</SelectItem>
                  <SelectItem value="deadline">Deadline</SelectItem>
                  <SelectItem value="budget-high">Budget (High to Low)</SelectItem>
                  <SelectItem value="budget-low">Budget (Low to High)</SelectItem>
                  <SelectItem value="applications">Most Applications</SelectItem>
                </SelectContent>
              </Select>

              {/* View Mode Toggle */}
              <div className="flex items-center gap-2">
                <Button
                  variant={viewMode === 'grid' ? 'default' : 'outline'}
                  size="sm"
                  onClick={() => setViewMode('grid')}
                  className="px-3"
                >
                  Grid
                </Button>
                <Button
                  variant={viewMode === 'list' ? 'default' : 'outline'}
                  size="sm"
                  onClick={() => setViewMode('list')}
                  className="px-3"
                >
                  List
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Content */}
        {isLoading ? (
          <div className={cn(
            "grid gap-6",
            viewMode === 'grid' 
              ? "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" 
              : "grid-cols-1"
          )}>
            {[...Array(8)].map((_, i) => (
              <Skeleton key={i} className={cn(
                "rounded-xl",
                viewMode === 'grid' ? "h-[400px]" : "h-[120px]"
              )} />
            ))}
          </div>
        ) : filteredAndSortedGrits.length === 0 ? (
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            className="text-center py-16"
          >
            <div className="mx-auto w-24 h-24 bg-gradient-to-r from-primary/10 to-primary/20 rounded-full flex items-center justify-center mb-6">
              <Zap className="w-12 h-12 text-primary" />
            </div>
            <h3 className="text-xl font-semibold mb-2">No grits found</h3>
            <p className="text-muted-foreground mb-6 max-w-md mx-auto">
              {searchTerm || statusFilter !== 'all' 
                ? "Try adjusting your search or filters"
                : "You haven't created any grits yet. Start by creating your first project request."
              }
            </p>
            {!searchTerm && statusFilter === 'all' && (
              <Button onClick={() => navigate('/dashboard/grits/create')}>
                <Plus className="w-4 h-4 mr-2" />
                Create Your First Grit
              </Button>
            )}
          </motion.div>
        ) : (
          <AnimatePresence mode="wait">
            {viewMode === 'grid' ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {filteredAndSortedGrits.map((grit) => (
                  <GritCard key={grit.id} grit={grit} />
                ))}
              </div>
            ) : (
              <GritList grits={filteredAndSortedGrits} />
            )}
          </AnimatePresence>
        )}
      </div>
    </div>
  );
};

export default MyGrits;