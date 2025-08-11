import React from 'react';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { UserCheck, Loader2 } from 'lucide-react';

interface ApplyGritDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
  isLoading: boolean;
  gritTitle: string;
}

const ApplyGritDialog = ({
  isOpen,
  onClose,
  onConfirm,
  isLoading,
  gritTitle
}: ApplyGritDialogProps) => {
  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Apply for Grit</DialogTitle>
          <DialogDescription>
            Are you sure you want to apply for "{gritTitle}"? 
            Your profile information will be shared with the admin.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="ghost" onClick={onClose} disabled={isLoading}>
            Cancel
          </Button>
          <Button onClick={onConfirm} disabled={isLoading}>
            {isLoading ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Submitting...
              </>
            ) : (
              <>
                <UserCheck className="mr-2 h-4 w-4" />
                Confirm Application
              </>
            )}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

export default ApplyGritDialog;