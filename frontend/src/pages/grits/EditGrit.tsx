import React, { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { motion } from 'framer-motion';
import { 
  ArrowLeft,
  Save,
  Loader2,
  AlertCircle,
  CheckCircle,
  XCircle,
  Calendar as CalendarIcon
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
import { TagInput } from '@/components/ui/tag-input';
import { toast } from 'sonner';
import { gritService, type Grit } from '@/services/gritService';
import { formatCurrency } from '@/lib/utils';
import { format } from 'date-fns';

const EditGrit = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [currentStep, setCurrentStep] = useState(0);

  const { data, isLoading, error } = useQuery({
    queryKey: ['grit', id],
    queryFn: () => gritService.getGritDetails(id!),
    enabled: !!id
  });

  const { data: categories } = useQuery({
    queryKey: ['professional-categories'],
    queryFn: gritService.getCategories
  });

  const grit = data?.grit;

  // Check if GRIT can be edited
  const canEdit = grit && !grit.assigned_professional_id && grit.status === 'open';

  const updateMutation = useMutation({
    mutationFn: (gritData: any) => gritService.updateGrit(id!, gritData),
    onSuccess: () => {
      toast.success('GRIT updated successfully! It will need admin approval again.');
      queryClient.invalidateQueries({ queryKey: ['grit', id] });
      queryClient.invalidateQueries({ queryKey: ['my-grits'] });
      navigate(`/dashboard/grits/${id}`);
    },
    onError: (err: any) => {
      toast.error(err.response?.data?.message || 'Failed to update GRIT');
    }
  });

  const handleSubmit = (formData: any) => {
    if (!canEdit) {
      toast.error('This GRIT cannot be edited. It may have a professional assigned or be in progress.');
      return;
    }

    // Reset admin approval status when edited by creator
    const updateData = {
      ...formData,
      admin_approval_status: 'pending' // Reset to pending for admin approval
    };

    updateMutation.mutate(updateData);
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="flex items-center gap-2">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          <span>Loading GRIT details...</span>
        </div>
      </div>
    );
  }

  if (error || !grit) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">GRIT Not Found</h2>
          <p className="text-muted-foreground mb-4">The GRIT you're looking for doesn't exist or you don't have permission to edit it.</p>
          <Button onClick={() => navigate('/dashboard/grits/mine')}>
            Back to My GRITs
          </Button>
        </div>
      </div>
    );
  }

  if (!canEdit) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <XCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-semibold mb-2">Cannot Edit GRIT</h2>
          <p className="text-muted-foreground mb-4">
            {grit.assigned_professional_id 
              ? 'This GRIT cannot be edited because a professional has been assigned to it.'
              : 'This GRIT cannot be edited because it is not in an editable state.'
            }
          </p>
          <Button onClick={() => navigate(`/dashboard/grits/${id}`)}>
            Back to GRIT Details
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      <div className="container mx-auto px-4 py-6">
        {/* Header */}
        <motion.div 
          className="mb-6"
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div>
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  Edit GRIT
                </h1>
                <p className="text-gray-600 dark:text-gray-400">
                  Update your GRIT details. Changes will require admin approval.
                </p>
              </div>
            </div>
          </div>
        </motion.div>

        {/* Warning Banner */}
        <motion.div
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-6"
        >
          <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div className="flex items-center gap-2">
              <AlertCircle className="h-5 w-5 text-yellow-600" />
              <div>
                <p className="text-yellow-800 dark:text-yellow-200 font-medium">
                  Important: Admin Approval Required
                </p>
                <p className="text-yellow-700 dark:text-yellow-300 text-sm">
                  Any changes to your GRIT will reset the approval status to "Pending" and require admin approval before it becomes visible to professionals again.
                </p>
              </div>
            </div>
          </div>
        </motion.div>

        {/* Edit Form */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          <Card>
            <CardHeader>
              <CardTitle>GRIT Details</CardTitle>
            </CardHeader>
            <CardContent>
              <EditGritForm 
                grit={grit}
                categories={categories || []}
                onSubmit={handleSubmit}
                isLoading={updateMutation.isPending}
              />
            </CardContent>
          </Card>
        </motion.div>
      </div>
    </div>
  );
};

interface EditGritFormProps {
  grit: Grit;
  categories: any[];
  onSubmit: (data: any) => void;
  isLoading: boolean;
}

const EditGritForm: React.FC<EditGritFormProps> = ({ grit, categories, onSubmit, isLoading }) => {
  const navigate = useNavigate();
  const [isDeadlineOpen, setIsDeadlineOpen] = useState(false);
  const [formData, setFormData] = useState({
    title: grit.title || '',
    description: grit.description || '',
    category_id: grit.category?.id || '',
    owner_budget: grit.owner_budget || 0,
    deadline: grit.deadline ? new Date(grit.deadline).toISOString().split('T')[0] : '',
    requirements: grit.requirements || '',
    skills_required: Array.isArray(grit.skills_required) ? grit.skills_required : []
  });

  const handleInputChange = (field: string, value: any) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Basic Information */}
      <div className="space-y-2">
        <Label htmlFor="title">Grit Title</Label>
        <Input
          id="title"
          value={formData.title}
          onChange={(e) => handleInputChange('title', e.target.value)}
          placeholder="Enter the Grit title"
          required
        />
      </div>

      <div className="space-y-2">
        <Label htmlFor="category_id">Category</Label>
        <Select
          value={formData.category_id}
          onValueChange={(value) => handleInputChange('category_id', value)}
          required
        >
          <SelectTrigger>
            <SelectValue placeholder="Select a category" />
          </SelectTrigger>
          <SelectContent>
            {categories.map((category) => (
              <SelectItem key={category.id} value={category.id}>
                {category.name}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

             <div className="space-y-2">
         <Label htmlFor="description">Description</Label>
         <Textarea
           id="description"
           value={formData.description}
           onChange={(e) => handleInputChange('description', e.target.value)}
           placeholder="Describe the Grit in detail"
           rows={8}
           required
         />
       </div>

       <div className="space-y-2">
         <Label>Skills Required</Label>
         <div data-tag-input>
           <TagInput
             tags={formData.skills_required}
             onTagsChange={(tags) => handleInputChange('skills_required', tags)}
             placeholder="Add skills and press Enter"
           />
         </div>
         <p className="text-sm text-muted-foreground">
           Enter skills, requirements, or any other project specifications. Press Enter to add each skill.
         </p>
       </div>

      {/* Budget & Timeline */}
      <div className="space-y-2">
        <Label htmlFor="owner_budget">Budget</Label>
        <Input
          type="number"
          id="owner_budget"
          min="0"
          step="0.01"
          value={formData.owner_budget}
          onChange={(e) => handleInputChange('owner_budget', parseFloat(e.target.value) || 0)}
          placeholder="e.g., 50000"
          required
        />
      </div>

      <div className="space-y-2 relative">
        <Label htmlFor="deadline">Deadline</Label>
        <Button
          variant="outline"
          className="w-full justify-between"
          type="button"
          onClick={() => setIsDeadlineOpen((v) => !v)}
        >
          {formData.deadline ? (
            <span>{format(new Date(formData.deadline), 'PPP')}</span>
          ) : (
            <span>Pick a date</span>
          )}
          <CalendarIcon className="ml-auto h-4 w-4 opacity-50" />
        </Button>
        {isDeadlineOpen && (
          <div 
            className="absolute top-[calc(100%+4px)] left-0 z-50 rounded-md border bg-popover p-0 text-popover-foreground shadow-md outline-none"
            onClick={(e) => e.stopPropagation()}
          >
            <CalendarComponent
              mode="single"
              selected={formData.deadline ? new Date(formData.deadline) : undefined}
              onSelect={(date) => {
                if (date) {
                  handleInputChange('deadline', format(date, 'yyyy-MM-dd'));
                }
                setIsDeadlineOpen(false);
              }}
              disabled={(date) => date < new Date(new Date().setHours(0, 0, 0, 0))}
              initialFocus
            />
          </div>
        )}
      </div>

      {/* Submit Button */}
      <div className="flex justify-end gap-3 pt-6 border-t">
        <Button
          type="button"
          variant="outline"
          onClick={() => navigate(`/dashboard/grits/${grit.id}`)}
          disabled={isLoading}
        >
          Cancel
        </Button>
        <Button type="submit" disabled={isLoading}>
          {isLoading ? (
            <>
              <Loader2 className="h-4 w-4 mr-2 animate-spin" />
              Updating...
            </>
          ) : (
            <>
              <Save className="h-4 w-4 mr-2" />
              Update GRIT
            </>
          )}
        </Button>
      </div>
    </form>
  );
};

export default EditGrit;
