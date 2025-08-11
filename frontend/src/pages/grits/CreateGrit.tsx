import React from 'react';
import { useForm, Controller } from 'react-hook-form';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
import { Calendar as CalendarIcon } from 'lucide-react';
import { format } from 'date-fns';

import { useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { TagInput } from '@/components/ui/tag-input';
import { gritService, type Category, type CreateGritData } from '@/services/gritService';

interface GritFormData extends Omit<CreateGritData, 'skills_required' | 'category_id'> {
  skills_required: string[];
  category_id: number;
}

export default function CreateGrit() {
  const navigate = useNavigate();
  const { register, handleSubmit, formState: { errors }, control, trigger, watch, setValue } = useForm<GritFormData>({
    defaultValues: {
      owner_budget: 0,
      skills_required: []
    }
  });
  const [loading, setLoading] = React.useState(false);
  const [currentStep, setCurrentStep] = React.useState(0);
  const [isDeadlineOpen, setIsDeadlineOpen] = React.useState(false);

  const { data: categories, isLoading: isLoadingCategories } = useQuery({
    queryKey: ['professional-categories'],
    queryFn: gritService.getCategories
  });

  const steps: Array<{ title: string; fields: Array<keyof GritFormData> }> = [
    {
      title: 'Basic Info',
      fields: ['title', 'category_id']
    },
    {
      title: 'Requirements',
      fields: ['description', 'skills_required']
    },
    {
      title: 'Budget & Timeline',
      fields: ['owner_budget', 'deadline']
    }
  ];

  const handleNext = async () => {
    const valid = await trigger(steps[currentStep].fields as any);
    if (!valid) return;
    setCurrentStep((s) => Math.min(s + 1, steps.length - 1));
  };

  const handleBack = () => {
    setCurrentStep((s) => Math.max(s - 1, 0));
  };

  const onSubmit = async (data: GritFormData) => {
    try {
      setLoading(true);
      const gritData: CreateGritData = {
        ...data,
        category_id: String(data.category_id),
        skills_required: data.skills_required,
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
          <h1 className="text-2xl font-bold mb-2">Create a New GRIT</h1>
          <div className="mb-4">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm text-muted-foreground">Step {currentStep + 1} of {steps.length}</p>
              <p className="text-sm font-medium">{steps[currentStep].title}</p>
            </div>
            <Progress value={((currentStep + 1) / steps.length) * 100} />
          </div>
          
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            {currentStep === 0 && (
              <>
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
                            categories?.map((category: Category) => (
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
              </>
            )}

            {currentStep === 1 && (
              <>
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
                  <Label>Skills Required</Label>
                  <Controller
                    name="skills_required"
                    control={control}
                    rules={{ validate: (vals) => (vals && vals.length > 0) || 'Please add at least one skill' }}
                    render={({ field }) => (
                      <TagInput
                        tags={field.value || []}
                        onTagsChange={field.onChange}
                        placeholder="Add skills and press Enter"
                      />
                    )}
                  />
                  {errors.skills_required && (
                    <p className="text-sm text-red-500">{String(errors.skills_required.message)}</p>
                  )}
                </div>
              </>
            )}

            {currentStep === 2 && (
              <>
                <div className="space-y-2">
                  <Label htmlFor="owner_budget">Budget</Label>
                  <Input
                    type="number"
                    id="owner_budget"
                    {...register('owner_budget', { 
                      required: 'Budget is required',
                      valueAsNumber: true,
                      min: { value: 0, message: 'Budget cannot be negative' }
                    })}
                    placeholder="e.g., 50000"
                  />
                  {errors.owner_budget && (
                    <p className="text-sm text-red-500">{errors.owner_budget.message}</p>
                  )}
                </div>
                <div className="space-y-2 relative">
                  <Label htmlFor="deadline">Deadline</Label>
                  <Button
                    variant="outline"
                    className="w-full justify-between"
                    type="button"
                    onClick={() => setIsDeadlineOpen((v) => !v)}
                  >
                    {watch('deadline') ? (
                      <span>{format(new Date(watch('deadline')), 'PPP')}</span>
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
                        selected={watch('deadline') ? new Date(watch('deadline')) : undefined}
                        onSelect={(date) => {
                          if (date) {
                            setValue('deadline', format(date, 'yyyy-MM-dd'), { shouldValidate: true });
                          }
                          setIsDeadlineOpen(false);
                        }}
                        disabled={(date) => date < new Date(new Date().setHours(0, 0, 0, 0))}
                        initialFocus
                      />
                    </div>
                  )}
                  {errors.deadline && (
                    <p className="text-sm text-red-500">{errors.deadline.message}</p>
                  )}
                </div>
              </>
            )}

            <div className="flex gap-2 pt-2">
              <Button type="button" variant="outline" onClick={handleBack} disabled={currentStep === 0}>
                Back
              </Button>
              {currentStep < steps.length - 1 ? (
                <Button type="button" onClick={handleNext}>
                  Next
                </Button>
              ) : (
                <Button type="submit" disabled={loading} className="ml-auto">
                  {loading ? 'Submitting...' : 'Create Grit'}
                </Button>
              )}
            </div>
          </form>
        </div>
      </Card>
    </div>
  );
}
