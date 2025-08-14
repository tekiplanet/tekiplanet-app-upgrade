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
  XCircle
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import { gritService, type Grit } from '@/services/gritService';
import { formatCurrency } from '@/lib/utils';

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
  const [formData, setFormData] = useState({
    title: grit.title || '',
    description: grit.description || '',
    category_id: grit.category?.id || '',
    owner_budget: grit.owner_budget || 0,
    deadline: grit.deadline ? new Date(grit.deadline).toISOString().split('T')[0] : '',
    requirements: grit.requirements || ''
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
      <div className="space-y-4">
        <div>
          <Label htmlFor="title">Title</Label>
          <Input
            id="title"
            value={formData.title}
            onChange={(e) => handleInputChange('title', e.target.value)}
            placeholder="Enter GRIT title"
            required
          />
        </div>

        <div>
          <Label htmlFor="category">Category</Label>
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

        <div>
          <Label htmlFor="description">Description</Label>
          <Textarea
            id="description"
            value={formData.description}
            onChange={(e) => handleInputChange('description', e.target.value)}
            placeholder="Describe the GRIT in detail"
            rows={4}
            required
          />
        </div>

        <div>
          <Label htmlFor="requirements">Requirements & Skills</Label>
          <Textarea
            id="requirements"
            value={formData.requirements}
            onChange={(e) => handleInputChange('requirements', e.target.value)}
            placeholder="Enter skills, requirements, or any other project specifications (e.g., PHP, Laravel, MySQL, Must have 3+ years experience, Portfolio required)"
            rows={3}
          />
          <p className="text-sm text-gray-500 mt-1">
            Enter skills, requirements, or any other project specifications. You can separate items with commas or put each on a new line.
          </p>
        </div>
      </div>

      {/* Budget & Timeline */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="budget">Budget</Label>
          <Input
            id="budget"
            type="number"
            min="0"
            step="0.01"
            value={formData.owner_budget}
            onChange={(e) => handleInputChange('owner_budget', parseFloat(e.target.value) || 0)}
            placeholder="Enter budget amount"
            required
          />
        </div>

        <div>
          <Label htmlFor="deadline">Deadline</Label>
          <Input
            id="deadline"
            type="date"
            value={formData.deadline}
            onChange={(e) => handleInputChange('deadline', e.target.value)}
            min={new Date().toISOString().split('T')[0]}
            required
          />
        </div>
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
