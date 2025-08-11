import React from 'react';
import { useForm, Controller } from 'react-hook-form';
import { motion } from 'framer-motion';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card } from '@/components/ui/card';

import { useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { gritService, type Category, type CreateGritData } from '@/services/gritService';
import { Switch } from '@/components/ui/switch';

interface GritFormData extends Omit<CreateGritData, 'skills_required' | 'category_id'> {
  skills_required: string; // Comma-separated
  category_id: number;
}

export default function CreateGrit() {
  const navigate = useNavigate();
  const { register, handleSubmit, formState: { errors }, control } = useForm<GritFormData>({
    defaultValues: {
      currency: 'NGN',
      is_public: true,
      owner_budget: 0,
      professional_budget: 0
    }
  });
  const [loading, setLoading] = React.useState(false);

  const { data: categories, isLoading: isLoadingCategories } = useQuery({
    queryKey: ['professional-categories'],
    queryFn: gritService.getCategories
  });

  const onSubmit = async (data: GritFormData) => {
    try {
      setLoading(true);
      const gritData: CreateGritData = {
        ...data,
        category_id: String(data.category_id),
        skills_required: data.skills_required.split(',').map(skill => skill.trim()),
      };
      await gritService.createGrit(gritData);
      toast.success('Grit created successfully');
      navigate('/dashboard/grits');
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to create Grit');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-background p-4">
      <Card className="max-w-2xl mx-auto">
        <div className="p-6">
          <h1 className="text-2xl font-bold mb-6">Create a New Grit</h1>
          
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="title">Grit Title</Label>
              <Input
                id="title"
                {...register('title', { required: 'Title is required' })}
                placeholder="Enter the Grit title"
              />
              {errors.title && (
                <p className="text-sm text-red-500">{errors.title.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="description">Description</Label>
              <Textarea
                id="description"
                {...register('description', { required: 'Description is required' })}
                placeholder="Describe the Grit in detail"
                rows={4}
              />
              {errors.description && (
                <p className="text-sm text-red-500">{errors.description.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="category_id">Category</Label>
              <Controller
                name="category_id"
                control={control}
                rules={{ required: 'Category is required' }}
                render={({ field }) => (
                  <Select
                    onValueChange={(value) => field.onChange(parseInt(value, 10))}
                    defaultValue={field.value ? String(field.value) : undefined}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a category" />
                    </SelectTrigger>
                    <SelectContent>
                      {isLoadingCategories ? (
                        <SelectItem value="loading" disabled>Loading...</SelectItem>
                      ) : (
                        categories?.data.map((category: Category) => (
                          <SelectItem key={category.id} value={String(category.id)}>
                            {category.name}
                          </SelectItem>
                        ))
                      )}
                    </SelectContent>
                  </Select>
                )}
              />
              {errors.category_id && (
                <p className="text-sm text-red-500">{errors.category_id.message}</p>
              )}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
               <div className="space-y-2">
                <Label htmlFor="professional_budget">Professional's Budget</Label>
                <Input
                  type="number"
                  id="professional_budget"
                  {...register('professional_budget', { 
                    required: 'Budget is required',
                    valueAsNumber: true,
                    min: { value: 0, message: 'Budget cannot be negative' }
                  })}
                  placeholder="e.g., 50000"
                />
                {errors.professional_budget && (
                  <p className="text-sm text-red-500">{errors.professional_budget.message}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="owner_budget">Owner's Budget</Label>
                <Input
                  type="number"
                  id="owner_budget"
                  {...register('owner_budget', { 
                    required: 'Budget is required',
                    valueAsNumber: true,
                    min: { value: 0, message: 'Budget cannot be negative' }
                  })}
                  placeholder="e.g., 55000"
                />
                {errors.owner_budget && (
                  <p className="text-sm text-red-500">{errors.owner_budget.message}</p>
                )}
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="deadline">Deadline</Label>
              <Input
                type="date"
                id="deadline"
                min={new Date().toISOString().split('T')[0]}
                {...register('deadline', { 
                  required: 'Deadline is required',
                  validate: (value) => 
                    new Date(value) > new Date() || 
                    'Deadline must be a future date'
                })}
              />
              {errors.deadline && (
                <p className="text-sm text-red-500">{errors.deadline.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="skills_required">Skills Required (comma-separated)</Label>
              <Textarea
                id="skills_required"
                {...register('skills_required', { required: 'Skills are required' })}
                placeholder="e.g., React, TypeScript, Node.js"
                rows={3}
              />
              {errors.skills_required && (
                <p className="text-sm text-red-500">{errors.skills_required.message}</p>
              )}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="currency">Currency</Label>
                <Controller
                  name="currency"
                  control={control}
                  rules={{ required: 'Currency is required' }}
                  render={({ field }) => (
                    <Select onValueChange={field.onChange} defaultValue={field.value}>
                      <SelectTrigger>
                        <SelectValue placeholder="Select currency" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="NGN">NGN</SelectItem>
                        <SelectItem value="USD">USD</SelectItem>
                        <SelectItem value="GBP">GBP</SelectItem>
                      </SelectContent>
                    </Select>
                  )}
                />
                {errors.currency && (
                  <p className="text-sm text-red-500">{errors.currency.message}</p>
                )}
              </div>

              <div className="space-y-2 flex flex-col justify-center">
                <Label htmlFor="is_public" className="mb-2">Visibility</Label>
                <div className="flex items-center space-x-2">
                  <Controller
                    name="is_public"
                    control={control}
                    render={({ field }) => (
                      <Switch
                        id="is_public"
                        checked={field.value}
                        onCheckedChange={field.onChange}
                      />
                    )}
                  />
                  <Label htmlFor="is_public">Public</Label>
                </div>
              </div>
            </div>

            <div className="flex gap-4">
              <Button
                type="button"
                variant="outline"
                onClick={() => navigate(-1)}
              >
                Cancel
              </Button>
              <Button
                type="submit"
                disabled={loading}
                className="flex-1"
              >
                {loading ? 'Submitting...' : 'Create Grit'}
              </Button>
            </div>
          </form>
        </div>
      </Card>
    </div>
  );
}
