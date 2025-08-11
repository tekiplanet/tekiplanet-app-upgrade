import React from 'react';
import { Card } from '@/components/ui/card';
import { ChatNotificationBadge } from '@/components/grits/ChatNotificationBadge';
import type { Grit } from '@/services/gritService';

const GritCard = ({ grit }: { grit: Grit }) => {
  return (
    <Card className="relative">
      {grit.unread_messages > 0 && (
        <div className="absolute top-4 right-4">
          <ChatNotificationBadge count={grit.unread_messages} />
        </div>
      )}
      {/* ... rest of card content ... */}
    </Card>
  );
};

export default GritCard;