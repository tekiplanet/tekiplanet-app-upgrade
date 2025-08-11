import React from 'react';
import { useQuery } from '@tanstack/react-query';
import { motion, AnimatePresence } from 'framer-motion';
import { useNavigate } from 'react-router-dom';
import { Search, Filter, Briefcase, Calendar, Users, ArrowRight, X } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Skeleton } from '@/components/ui/skeleton';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useDebounce } from '@/hooks/useDebounce';
import { gritService, type Grit, type Category } from '@/services/gritService';
import { formatCurrency } from '@/lib/utils';
import { settingsService } from '@/services/settingsService';

const container = {
  hidden: { opacity: 0 },
  show: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1
    }
  }
};

const item = {
  hidden: { y: 20, opacity: 0 },
  show: { y: 0, opacity: 1 }
};

const Grits = () => {
  const navigate = useNavigate();
  const [search, setSearch] = React.useState('');
  const [selectedCategory, setSelectedCategory] = React.useState<Category | null>(null);
  const debouncedSearch = useDebounce(search, 500);

  const { data: grits, isLoading } = useQuery({
    queryKey: ['grits', debouncedSearch, selectedCategory?.id],
    queryFn: () => gritService.getGrits({ 
      search: debouncedSearch,
      category_id: selectedCategory?.id 
    })
  });

  const { data: categories } = useQuery({
    queryKey: ['professional-categories'],
    queryFn: gritService.getCategories
  });

  const { data: settings } = useQuery({
    queryKey: ['settings'],
    queryFn: settingsService.fetchSettings
  });

  const handleGritClick = (id: string) => {
    navigate(`/dashboard/grits/${id}`);
  };

  const clearFilters = () => {
    setSelectedCategory(null);
  };

  return (
    <ScrollArea className="h-[calc(100vh-4rem)]">
      <div className="container mx-auto p-4 space-y-6">
        {/* Header Section */}
        <div className="space-y-4">
          {/* Title and Description */}
          <div className="space-y-2">
            <h1 className="text-2xl font-bold tracking-tight">Explore Grits</h1>
            <p className="text-muted-foreground">
              Find and apply for exciting opportunities in your field
            </p>
          </div>

          {/* My Applications Button - Full width on mobile */}
          <div className="w-full sm:flex sm:justify-end gap-4">
            <Button
              variant="outline"
              onClick={() => navigate('/dashboard/grits/applications')}
              className="w-full sm:w-auto"
            >
              <Briefcase className="h-4 w-4 mr-2" />
              My Applications
            </Button>
            <Button
              onClick={() => navigate('/dashboard/grits/create')}
              className="w-full sm:w-auto mt-2 sm:mt-0"
            >
              <Briefcase className="h-4 w-4 mr-2" />
              Create Grit
            </Button>
          </div>
        </div>

        {/* Search and Filter Section */}
        <motion.div 
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.2 }}
          className="flex flex-col sm:flex-row gap-4"
        >
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Search grits..."
              className="pl-9"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" className="sm:w-auto">
                <Filter className="h-4 w-4 mr-2" />
                {selectedCategory ? selectedCategory.name : 'Filter'}
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              {categories?.data.map((category: Category) => (
                <DropdownMenuItem 
                  key={category.id}
                  onClick={() => setSelectedCategory(category)}
                >
                  {category.name}
                </DropdownMenuItem>
              ))}
            </DropdownMenuContent>
          </DropdownMenu>
          {selectedCategory && (
            <Button variant="ghost" onClick={clearFilters} size="icon">
              <X className="h-4 w-4" />
            </Button>
          )}
        </motion.div>

        {/* No Results Message */}
        {!isLoading && grits?.data.length === 0 && (
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center py-12"
          >
            <h3 className="text-lg font-semibold">No Grits Found</h3>
            <p className="text-muted-foreground mt-2">
              Try adjusting your filters or search terms
            </p>
          </motion.div>
        )}

        {/* Grits Grid */}
        <AnimatePresence mode="wait">
          {isLoading ? (
            <motion.div
              key="loading"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            >
              {[...Array(6)].map((_, i) => (
                <Skeleton key={i} className="h-[280px] rounded-xl" />
              ))}
            </motion.div>
          ) : (
            <motion.div
              variants={container}
              initial="hidden"
              animate="show"
              className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            >
              {grits?.data.map((grit: Grit) => (
                <motion.div
                  key={grit.id}
                  variants={item}
                  whileHover={{ y: -5, transition: { duration: 0.2 } }}
                  onClick={() => handleGritClick(grit.id)}
                >
                  <Card className="relative overflow-hidden cursor-pointer group h-full">
                    <div className="p-6 space-y-4">
                      {/* Category Badge */}
                      <Badge variant="secondary" className="mb-4">
                        {grit.category.name}
                      </Badge>

                      {/* Title and Description */}
                      <div className="space-y-2">
                        <h3 className="font-semibold text-lg line-clamp-2">
                          {grit.title}
                        </h3>
                        <p className="text-muted-foreground text-sm line-clamp-3">
                          {grit.description}
                        </p>
                      </div>

                      {/* Stats */}
                      <div className="grid grid-cols-2 gap-4 pt-4">
                        <div className="flex items-center gap-2">
                          <Calendar className="h-4 w-4 text-muted-foreground" />
                          <span className="text-sm">{grit.deadline}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          <Users className="h-4 w-4 text-muted-foreground" />
                          <span className="text-sm">{grit.applications_count} applied</span>
                        </div>
                      </div>

                      {/* Budget and Apply Button */}
                      <div className="flex items-center justify-between pt-4">
                        <div className="space-y-1">
                          <p className="text-xs text-muted-foreground">Budget</p>
                          <p className="font-semibold">{formatCurrency(grit.professional_budget, settings?.default_currency)}</p>
                        </div>
                        <Button 
                          size="sm" 
                          className="opacity-0 group-hover:opacity-100 transition-opacity"
                          disabled={!grit.can_apply || grit.has_applied}
                        >
                          {grit.has_applied ? 'Applied' : 'Apply Now'}
                          <ArrowRight className="h-4 w-4 ml-2" />
                        </Button>
                      </div>
                    </div>

                    {/* Status Indicator */}
                    {grit.has_applied && (
                      <div className="absolute top-3 right-3">
                        <Badge variant="success">Applied</Badge>
                      </div>
                    )}
                  </Card>
                </motion.div>
              ))}
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </ScrollArea>
  );
};

export default Grits;