import React from 'react';
import { useQuery } from '@tanstack/react-query';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { useNavigate } from 'react-router-dom';
import { gritService, type Grit } from '@/services/gritService';

const MyGrits = () => {
  const navigate = useNavigate();
  const { data, isLoading } = useQuery({
    queryKey: ['my-grits'],
    queryFn: gritService.getMyGrits
  });

  const grits: Grit[] = data ?? [];

  return (
    <div className="p-4">
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-2xl font-bold">My Grits</h1>
        <Button onClick={() => navigate('/dashboard/grits/create')}>Create Grit</Button>
      </div>

      {isLoading ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[...Array(6)].map((_, i) => (
            <Skeleton key={i} className="h-[200px] rounded-xl" />
          ))}
        </div>
      ) : grits.length === 0 ? (
        <div className="text-center py-12">
          <h3 className="text-lg font-semibold">You haven't created any grits yet</h3>
          <p className="text-muted-foreground mt-2">Click "Create Grit" to get started</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {grits.map((grit) => (
            <Card key={grit.id} className="p-6 space-y-4 cursor-pointer" onClick={() => navigate(`/dashboard/grits/${grit.id}`)}>
              <Badge variant="secondary">{grit.category?.name}</Badge>
              <div>
                <h3 className="font-semibold text-lg line-clamp-2">{grit.title}</h3>
                <p className="text-muted-foreground text-sm line-clamp-3">{grit.description}</p>
              </div>
              <div className="text-sm text-muted-foreground">Deadline: {grit.deadline}</div>
              <div className="text-sm">Status: {grit.admin_approval_status}</div>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
};

export default MyGrits;