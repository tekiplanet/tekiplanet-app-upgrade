# TekiPlanet GRIT System Upgrade Plan

## Overview
This document outlines the comprehensive upgrade from the current "Hustle" system to the new "GRIT" system, introducing business owner job posting, multicurrency support, escrow payments, negotiation features, and enhanced workflow management.

## 🎯 Key Objectives

### Current System Limitations
- Only admins can create hustles
- Simple approval workflow without negotiation
- No escrow/frozen balance system
- Limited multicurrency integration
- Basic payment structure

### New GRIT System Features
- Business owners can create GRITs (pending admin approval)
- Professional profile viewing with ratings/qualifications
- Multicurrency budget display and conversion
- Escrow system with frozen balances
- Staged payment releases (20% → 40% → remaining 60%)
- Terms negotiation and modification
- Dispute resolution system
- Real-time system messages for all actions

## 🗄️ Database Schema Changes

### 1. Add Frozen Balance to Users Table
```sql
-- Migration: add_frozen_balance_to_users_table.php
ALTER TABLE users ADD COLUMN frozen_balance DECIMAL(10,2) DEFAULT 0.00 AFTER wallet_balance;
```

### 2. Rename Hustles to GRITs
```sql
-- Migration: rename_hustles_to_grits.php
RENAME TABLE hustles TO grits;
RENAME TABLE hustle_applications TO grit_applications;
RENAME TABLE hustle_messages TO grit_messages;
RENAME TABLE hustle_payments TO grit_payments;
```

### 3. Modify GRITs Table Structure
```sql
-- Migration: modify_grits_table_for_business_owners.php
ALTER TABLE grits ADD COLUMN created_by_user_id UUID NULL AFTER id;
ALTER TABLE grits ADD COLUMN admin_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER status;
ALTER TABLE grits ADD COLUMN owner_budget DECIMAL(10,2) NOT NULL AFTER budget;
ALTER TABLE grits ADD COLUMN owner_currency VARCHAR(3) NOT NULL AFTER owner_budget;
ALTER TABLE grits ADD COLUMN professional_budget DECIMAL(10,2) NOT NULL AFTER owner_currency;
ALTER TABLE grits ADD COLUMN professional_currency VARCHAR(3) NOT NULL AFTER professional_budget;
ALTER TABLE grits ADD COLUMN negotiation_status ENUM('none', 'pending', 'accepted', 'rejected') DEFAULT 'none';
ALTER TABLE grits ADD COLUMN terms_modified_at TIMESTAMP NULL;
ALTER TABLE grits ADD COLUMN project_started_at TIMESTAMP NULL;
ALTER TABLE grits ADD COLUMN completion_requested_at TIMESTAMP NULL;
ALTER TABLE grits ADD COLUMN owner_satisfaction ENUM('pending', 'satisfied', 'unsatisfied') DEFAULT 'pending';
ALTER TABLE grits ADD COLUMN owner_rating TINYINT NULL;
ALTER TABLE grits ADD COLUMN dispute_status ENUM('none', 'raised_by_owner', 'raised_by_professional', 'resolved') DEFAULT 'none';
ALTER TABLE grits ADD COLUMN project_id UUID NULL AFTER dispute_status;

-- Add foreign key for business owner
ALTER TABLE grits ADD FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL;
-- Add foreign key for project
ALTER TABLE grits ADD FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL;
```

### 4. Create GRIT Escrow Transactions Table
```sql
-- Migration: create_grit_escrow_transactions_table.php
CREATE TABLE grit_escrow_transactions (
    id UUID PRIMARY KEY,
    grit_id UUID NOT NULL,
    user_id UUID NOT NULL,
    transaction_type ENUM('freeze', 'release', 'refund') NOT NULL,
    owner_amount DECIMAL(10,2) NOT NULL,
    owner_currency VARCHAR(3) NOT NULL,
    professional_amount DECIMAL(10,2) NOT NULL,
    professional_currency VARCHAR(3) NOT NULL,
    percentage DECIMAL(5,2) NULL,
    description TEXT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (grit_id) REFERENCES grits(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 5. Create GRIT Terms Negotiations Table
```sql
-- Migration: create_grit_negotiations_table.php
CREATE TABLE grit_negotiations (
    id UUID PRIMARY KEY,
    grit_id UUID NOT NULL,
    proposed_by_user_id UUID NOT NULL,
    proposed_owner_budget DECIMAL(10,2) NOT NULL,
    proposed_owner_currency VARCHAR(3) NOT NULL,
    proposed_professional_budget DECIMAL(10,2) NOT NULL,
    proposed_professional_currency VARCHAR(3) NOT NULL,
    proposed_deadline DATE NOT NULL,
    proposed_requirements TEXT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'superseded') DEFAULT 'pending',
    response_message TEXT NULL,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (grit_id) REFERENCES grits(id) ON DELETE CASCADE,
    FOREIGN KEY (proposed_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 6. Create GRIT Disputes Table
```sql
-- Migration: create_grit_disputes_table.php
CREATE TABLE grit_disputes (
    id UUID PRIMARY KEY,
    grit_id UUID NOT NULL,
    raised_by_user_id UUID NOT NULL,
    dispute_type ENUM('payment', 'quality', 'deadline', 'scope', 'other') NOT NULL,
    description TEXT NOT NULL,
    evidence_files JSON NULL,
    status ENUM('open', 'under_review', 'resolved', 'closed') DEFAULT 'open',
    admin_notes TEXT NULL,
    resolution TEXT NULL,
    resolved_by_admin_id UUID NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (grit_id) REFERENCES grits(id) ON DELETE CASCADE,
    FOREIGN KEY (raised_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by_admin_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### 7. Enhance GRIT Messages for System Messages
```sql
-- Migration: enhance_grit_messages_table.php
ALTER TABLE grit_messages ADD COLUMN message_type ENUM('user', 'system') DEFAULT 'user' AFTER sender_type;
ALTER TABLE grit_messages ADD COLUMN system_action VARCHAR(100) NULL AFTER message_type;
ALTER TABLE grit_messages ADD COLUMN metadata JSON NULL AFTER system_action;
```

### 8. Add Professional Profile Enhancements
```sql
-- Migration: enhance_professionals_table.php
ALTER TABLE professionals ADD COLUMN completion_rate DECIMAL(5,2) DEFAULT 0.00;
ALTER TABLE professionals ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0.00;
ALTER TABLE professionals ADD COLUMN total_projects_completed INT DEFAULT 0;
ALTER TABLE professionals ADD COLUMN qualifications JSON NULL;
ALTER TABLE professionals ADD COLUMN portfolio_items JSON NULL;
ALTER TABLE professionals ADD COLUMN hourly_rate DECIMAL(8,2) NULL;
ALTER TABLE professionals ADD COLUMN availability_status ENUM('available', 'busy', 'unavailable') DEFAULT 'available';
```

## 🔧 Backend Implementation Plan

### Phase 1: Model Updates and Relationships

#### 1.1 Rename and Update Models
- Rename `Hustle.php` → `Grit.php`
- Rename `HustleApplication.php` → `GritApplication.php`
- Rename `HustleMessage.php` → `GritMessage.php`
- Rename `HustlePayment.php` → `GritPayment.php`

#### 1.2 Create New Models
```php
// app/Models/GritEscrowTransaction.php
class GritEscrowTransaction extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'grit_id', 'user_id', 'transaction_type', 'amount', 
        'currency', 'percentage', 'description', 'status'
    ];
    
    public function grit() { return $this->belongsTo(Grit::class); }
    public function user() { return $this->belongsTo(User::class); }
}

// app/Models/GritNegotiation.php
class GritNegotiation extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'grit_id', 'proposed_by_user_id', 'proposed_budget', 
        'proposed_currency', 'proposed_deadline', 'proposed_requirements',
        'status', 'response_message', 'responded_at'
    ];
    
    protected $casts = [
        'proposed_deadline' => 'date',
        'responded_at' => 'datetime'
    ];
}

// app/Models/GritDispute.php
class GritDispute extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'grit_id', 'raised_by_user_id', 'dispute_type', 'description',
        'evidence_files', 'status', 'admin_notes', 'resolution',
        'resolved_by_admin_id', 'resolved_at'
    ];
    
    protected $casts = [
        'evidence_files' => 'array',
        'resolved_at' => 'datetime'
    ];
}
```

#### 1.3 Update User Model
```php
// Add to User.php
protected $fillable = [
    // ... existing fields
    'frozen_balance'
];

protected $casts = [
    // ... existing casts
    'frozen_balance' => 'decimal:2'
];

// Add relationships
public function createdGrits()
{
    return $this->hasMany(Grit::class, 'created_by_user_id');
}

public function escrowTransactions()
{
    return $this->hasMany(GritEscrowTransaction::class);
}
```

### Phase 2: Service Layer Implementation

#### 2.1 Create GritEscrowService
```php
// app/Services/GritEscrowService.php
class GritEscrowService
{
    public function freezeInitialAmount(Grit $grit, User $owner): bool
    {
        $initialAmount = $grit->owner_budget * 0.20; // 20% of owner's budget
        
        if ($owner->wallet_balance < $initialAmount) {
            throw new InsufficientFundsException();
        }
        
        DB::transaction(function() use ($grit, $owner, $initialAmount) {
            // Deduct from wallet_balance
            $owner->decrement('wallet_balance', $initialAmount);
            // Add to frozen_balance
            $owner->increment('frozen_balance', $initialAmount);
            
            // Record escrow transaction
            GritEscrowTransaction::create([
                'grit_id' => $grit->id,
                'user_id' => $owner->id,
                'transaction_type' => 'freeze',
                'owner_amount' => $initialAmount,
                'owner_currency' => $grit->owner_currency,
                // professional_amount and currency will be calculated and stored here
                'percentage' => 20.00,
                'description' => 'Initial 20% frozen for GRIT approval',
                'status' => 'completed'
            ]);
        });
        
        return true;
    }
    
    public function processProjectStart(Grit $grit): bool
    {
        // Refund initial 20%, freeze full 100%
        // Credit 40% to professional, keep 60% frozen
    }
    
    public function releasePayment(Grit $grit, float $percentage): bool
    {
        // Release additional percentage to professional
        // Maximum 80% total before completion
    }
}
```

#### 2.2 Create GritNegotiationService
```php
// app/Services/GritNegotiationService.php
class GritNegotiationService
{
    public function proposeTerms(Grit $grit, User $proposer, array $terms): GritNegotiation
    {
        return GritNegotiation::create([
            'grit_id' => $grit->id,
            'proposed_by_user_id' => $proposer->id,
            'proposed_budget' => $terms['budget'],
            'proposed_currency' => $terms['currency'],
            'proposed_deadline' => $terms['deadline'],
            'proposed_requirements' => $terms['requirements'],
            'status' => 'pending'
        ]);
    }
    
    public function acceptTerms(GritNegotiation $negotiation): bool
    {
        // Update GRIT with new terms
        // Handle budget changes and escrow adjustments
        // Send system messages
    }
}
```

### Phase 3: Controller Updates

#### 3.1 Create Business Owner Controllers
```php
// app/Http/Controllers/Business/GritController.php
class GritController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:professional_categories,id',
            'budget' => 'required|numeric|min:0',
            'budget_currency' => 'required|string|size:3',
            'deadline' => 'required|date|after:today',
            'requirements' => 'nullable|string',
        ]);
        
        $grit = Grit::create([
            ...$validated,
            'created_by_user_id' => auth()->id(),
            'admin_approval_status' => 'pending',
            'status' => 'pending_admin_approval'
        ]);
        
        // Notify admins for approval
        // Send system message
        
        return response()->json(['grit' => $grit], 201);
    }
    
    public function approveApplication(Grit $grit, GritApplication $application)
    {
        // Freeze 20% of budget
        $escrowService = app(GritEscrowService::class);
        $escrowService->freezeInitialAmount($grit, auth()->user());
        
        // Update application and GRIT status
        // Send notifications
        // Create system message
    }
}
```

#### 3.2 Create GRIT-Project Integration Service
```php
// app/Services/GritProjectIntegrationService.php
class GritProjectIntegrationService
{
    public function createProjectFromGrit(Grit $grit): Project
    {
        DB::transaction(function () use ($grit) {
            // 1. Create the Project from the GRIT data
            $project = Project::create([
                'name' => $grit->title,
                'description' => $grit->description,
                'business_id' => $grit->user->businessProfile->id,
                'client_name' => $grit->user->businessProfile->business_name,
                'start_date' => now(),
                'end_date' => $grit->deadline,
                'budget' => $grit->owner_budget, // Project budget is from owner's perspective
                'status' => 'in_progress',
                'progress' => 0,
            ]);

            // 2. Link the Project to the GRIT
            $grit->update(['project_id' => $project->id]);

            // 3. Add the professional as a team member
            $project->teamMembers()->create([
                'professional_id' => $grit->assigned_professional_id,
                'role' => 'Lead Professional',
            ]);

            // 4. Optionally, create initial stages based on a template
            $this->createInitialProjectStages($project);

            return $project;
        });
    }

    private function createInitialProjectStages(Project $project)
    {
        // Example initial stages
        $stages = [
            ['name' => 'Project Kick-off', 'status' => 'completed'],
            ['name' => 'Phase 1 Deliverables', 'status' => 'in_progress'],
            ['name' => 'Final Review', 'status' => 'pending'],
        ];

        foreach ($stages as $stageData) {
            $project->stages()->create($stageData);
        }
    }
}
```

## 🎨 Frontend Implementation Plan

### Phase 4: Component Renaming and Updates

#### 4.1 Rename Components and Services
- `hustleService.ts` → `gritService.ts`
- `components/hustles/` → `components/grits/`
- `pages/hustles/` → `pages/grits/`
- `useHustleChat.ts` → `useGritChat.ts`

#### 4.2 Create New GRIT Components

```typescript
// components/grits/CreateGritDialog.tsx
interface CreateGritDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

const CreateGritDialog = ({ open, onOpenChange }: CreateGritDialogProps) => {
  // Form for business owners to create GRITs
  // Include budget with currency selection
  // Show estimated costs in user's currency
  // Validation for minimum budget requirements
};

// components/grits/ProfessionalProfileModal.tsx
interface ProfessionalProfileModalProps {
  professional: Professional;
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onApprove?: () => void;
}

const ProfessionalProfileModal = ({ professional, open, onOpenChange, onApprove }: ProfessionalProfileModalProps) => {
  // Display professional's full profile
  // Show ratings, completion rate, qualifications
  // Portfolio items and past work
  // Approve button for GRIT owners
};

// components/grits/NegotiationDialog.tsx
const NegotiationDialog = ({ grit, open, onOpenChange }: NegotiationDialogProps) => {
  // Terms modification interface
  // Budget adjustment with currency conversion
  // Deadline and requirements editing
  // Real-time preview of changes
};

// components/grits/EscrowStatusCard.tsx
const EscrowStatusCard = ({ grit }: { grit: Grit }) => {
  // Visual representation of payment stages
  // Progress bar showing released vs frozen amounts
  // Currency conversion display
  // Release payment buttons for owners
};

// components/grits/DisputeDialog.tsx
const DisputeDialog = ({ grit, open, onOpenChange }: DisputeDialogProps) => {
  // Dispute raising interface
  // Evidence upload functionality
  // Dispute type selection
  // Description and documentation
};
```

#### 4.3 Enhanced GRIT Service

```typescript
// services/gritService.ts
export interface Grit {
  id: string;
  title: string;
  description: string;
  category: { id: string; name: string; };
  owner_budget: number;
  owner_currency: string;
  professional_budget: number;
  professional_currency: string;
  original_budget?: number;
  deadline: string;
  requirements: string;
  status: 'pending_admin_approval' | 'open' | 'approved' | 'in_progress' | 'completed' | 'cancelled';
  admin_approval_status: 'pending' | 'approved' | 'rejected';
  created_by_user_id?: string;
  assigned_professional_id?: string;
  negotiation_status: 'none' | 'pending' | 'accepted' | 'rejected';
  project_started_at?: string;
  owner_satisfaction: 'pending' | 'satisfied' | 'unsatisfied';
  owner_rating?: number;
  dispute_status: 'none' | 'raised_by_owner' | 'raised_by_professional' | 'resolved';
  
  // Computed fields
  
  user_currency_symbol?: string;
  escrow_status?: {
    total_frozen: number;
    released_to_professional: number;
    available_to_release: number;
    max_releasable: number;
  };
}

export const gritService = {
  // Business Owner Operations
  createGrit: async (gritData: CreateGritRequest) => {
    const { data } = await api.post('/business/grits', gritData);
    return data;
  },

  getMyCreatedGrits: async () => {
    const { data } = await api.get('/business/grits');
    return data.grits;
  },

  approveApplication: async (gritId: string, applicationId: string) => {
    const { data } = await api.post(`/business/grits/${gritId}/applications/${applicationId}/approve`);
    return data;
  },

  startProject: async (gritId: string, modifiedTerms?: any) => {
    const { data } = await api.post(`/business/grits/${gritId}/start`, modifiedTerms);
    return data;
  },

  releasePayment: async (gritId: string, percentage: number) => {
    const { data } = await api.post(`/business/grits/${gritId}/release-payment`, { percentage });
    return data;
  },

  increaseBudget: async (gritId: string, additionalAmount: number) => {
    const { data } = await api.post(`/business/grits/${gritId}/increase-budget`, { amount: additionalAmount });
    return data;
  },

  markSatisfied: async (gritId: string, rating: number) => {
    const { data } = await api.post(`/business/grits/${gritId}/mark-satisfied`, { rating });
    return data;
  },

  raiseDispute: async (gritId: string, disputeData: any) => {
    const { data } = await api.post(`/business/grits/${gritId}/disputes`, disputeData);
    return data;
  },

  // Professional Operations
  getGrits: async (params?: { category_id?: string; search?: string; }) => {
    const { data } = await api.get('/grits', { params });
    return data;
  },

  applyForGrit: async (gritId: string) => {
    const { data } = await api.post(`/grits/${gritId}/apply`);
    return data;
  },

  acceptNegotiation: async (negotiationId: string) => {
    const { data } = await api.post(`/grit-negotiations/${negotiationId}/accept`);
    return data;
  },

  markProjectComplete: async (gritId: string) => {
    const { data } = await api.post(`/grits/${gritId}/mark-complete`);
    return data;
  },

  // Currency Operations
  convertBudget: async (amount: number, fromCurrency: string, toCurrency: string) => {
    const { data } = await api.get(`/currency/convert`, {
      params: { amount, from: fromCurrency, to: toCurrency }
    });
    return data;
  },

  // Admin Operations
  approveGrit: async (gritId: string) => {
    const { data } = await api.post(`/admin/grits/${gritId}/approve`);
    return data;
  },

  rejectGrit: async (gritId: string, reason: string) => {
    const { data } = await api.post(`/admin/grits/${gritId}/reject`, { reason });
    return data;
  }
};
```

### Phase 5: Page Updates

#### 5.1 Business Owner Dashboard
```typescript
// pages/business/MyGrits.tsx
const MyGrits = () => {
  const [activeTab, setActiveTab] = useState<'created' | 'active' | 'completed'>('created');
  
  // Tabs for different GRIT states
  // Created GRITs (pending admin approval)
  // Active GRITs (approved, in progress)
  // Completed GRITs (finished projects)
  
  // Each GRIT card shows:
  // - Budget in owner's currency
  // - Application count
  // - Current status
  // - Escrow status
  // - Quick actions (approve applications, release payments, etc.)
};

// pages/business/GritApplications.tsx
const GritApplications = ({ gritId }: { gritId: string }) => {
  // List of applications for a specific GRIT
  // Professional profile preview cards
  // Click to view full profile modal
  // Approve/reject buttons
  // Application status tracking
};
```

#### 5.2 Professional Interface Updates
```typescript
// pages/grits/Grits.tsx (updated from Hustles.tsx)
const Grits = () => {
  // Enhanced filtering with budget ranges
  // Currency conversion display
  // GRIT status indicators
  // Application status tracking
  // Real-time updates for new GRITs
};

// pages/grits/GritDetails.tsx
const GritDetails = ({ gritId }: { gritId: string }) => {
  // Enhanced GRIT details with:
  // - Budget in professional's currency
  // - Owner information (if assigned)
  // - Negotiation history
  // - Payment schedule visualization
  // - Project timeline
  // - Dispute options
};
```

## 🔄 Real-time Features Implementation

### Phase 6: WebSocket Integration

#### 6.1 Enhanced Chat System
```typescript
// hooks/useGritChat.ts
export const useGritChat = (gritId: string) => {
  // Enhanced to handle system messages
  // Real-time escrow status updates
  // Negotiation status changes
  // Payment release notifications
  // Dispute status updates
};

// System message types
interface SystemMessage {
  type: 'system';
  action: 'grit_created' | 'application_approved' | 'project_started' | 
          'payment_released' | 'budget_increased' | 'terms_modified' |
          'project_completed' | 'dispute_raised' | 'dispute_resolved';
  metadata: {
    amount?: number;
    currency?: string;
    percentage?: number;
    old_budget?: number;
    new_budget?: number;
    [key: string]: any;
  };
  timestamp: string;
}
```

#### 6.2 Real-time Notifications
```typescript
// Enhanced notification system for GRIT events
const gritNotifications = {
  // Business Owner Notifications
  'application_received': 'New application received for your GRIT',
  'professional_assigned': 'Professional assigned to your GRIT',
  'project_completed': 'Professional marked project as complete',
  'dispute_raised': 'Professional raised a dispute',
  
  // Professional Notifications
  'grit_approved': 'Your GRIT application was approved',
  'payment_released': 'Payment released to your wallet',
  'terms_modified': 'GRIT owner proposed new terms',
  'project_started': 'Project officially started',
  
  // Admin Notifications
  'grit_pending_approval': 'New GRIT pending approval',
  'dispute_needs_attention': 'Dispute requires admin attention'
};
```

## 📊 Payment Flow Implementation

### Phase 7: Escrow and Payment System

#### 7.1 Payment Flow Visualization
```
Initial GRIT Creation (Business Owner):
┌─────────────────────────────────────────────────────────────┐
│ 1. Owner creates GRIT → Admin approval → Visible to pros   │
│ 2. Professional applies → Owner approves → 20% frozen      │
│ 3. Chat enabled → Negotiation possible                     │
│ 4. Owner clicks "Start Project" → Terms finalization       │
│ 5. Professional accepts → 20% refunded, 100% frozen        │
│ 6. 40% released to professional, 60% remains frozen        │
│ 7. Owner can release up to 80% total during project        │
│ 8. Project completion → Final payment release              │
└─────────────────────────────────────────────────────────────┘
```

#### 7.2 Currency Conversion Flow
```typescript
// Currency conversion for budget display
// No longer needed, as amounts are pre-converted and stored.
// The frontend will simply display the correct amount based on the logged-in user's role.
```

## 🚀 Implementation Phases

### Phase 8: Step-by-Step Implementation Plan

#### Week 1: Database Foundation
1. **Day 1-2**: Create all database migrations
2. **Day 3-4**: Update existing models and create new models
3. **Day 5**: Test database schema and relationships

#### Week 2: Backend Services
1. **Day 1-2**: Implement GritEscrowService
2. **Day 3-4**: Implement GritNegotiationService
3. **Day 5**: Create dispute management service

#### Week 3: API Controllers
1. **Day 1-2**: Update existing controllers for GRIT system
2. **Day 3-4**: Create business owner controllers
3. **Day 5**: Implement admin approval controllers

#### Week 4: Frontend Foundation
1. **Day 1-2**: Rename and update existing components
2. **Day 3-4**: Create new GRIT-specific components
3. **Day 5**: Update service layer and API integration

#### Week 5: Advanced Features
1. **Day 1-2**: Implement negotiation interface
2. **Day 3-4**: Create escrow status visualization
3. **Day 5**: Add dispute management UI

#### Week 6: Real-time Integration
1. **Day 1-2**: Enhance WebSocket system for GRIT events
2. **Day 3-4**: Implement system messages in chat
3. **Day 5**: Add real-time notifications

#### Week 7: Currency Integration
1. **Day 1-2**: Integrate multicurrency display
2. **Day 3-4**: Implement budget conversion
3. **Day 5**: Test currency-related features

#### Week 8: Testing and Polish
1. **Day 1-3**: Comprehensive testing of all flows
2. **Day 4-5**: Bug fixes and performance optimization

## 🔍 Testing Strategy

### Critical Test Cases
1. **GRIT Creation Flow**: Business owner creates → Admin approves → Visible to professionals
2. **Application and Approval**: Professional applies → Owner views profile → Approves → 20% frozen
3. **Project Start**: Terms negotiation → Acceptance → Payment restructuring (20% refund, 100% freeze, 40% release)
4. **Payment Releases**: Owner releases additional payments (max 80% before completion)
5. **Budget Increases**: Owner increases budget → Additional amount frozen
6. **Project Completion**: Professional marks done → Owner satisfaction → Final payment release
7. **Dispute Flow**: Either party raises dispute → Admin intervention → Resolution
8. **Currency Conversion**: All amounts displayed correctly in user's preferred currency
9. **Real-time Updates**: All actions reflected immediately in chat as system messages

## 📋 Migration Checklist

### Pre-Migration Tasks
- [ ] Backup existing hustle data
- [ ] Test migrations on staging environment
- [ ] Prepare data migration scripts for existing hustles
- [ ] Update API documentation

### Migration Tasks
- [ ] Run database migrations in sequence
- [ ] Migrate existing hustle data to GRIT format
- [ ] Update frontend routing and navigation
- [ ] Deploy backend changes
- [ ] Deploy frontend changes
- [ ] Test all critical flows

### Post-Migration Tasks
- [ ] Monitor system performance
- [ ] Verify data integrity
- [ ] Test real-time features
- [ ] Validate currency conversions
- [ ] Check notification delivery

This comprehensive upgrade plan transforms the simple hustle system into a sophisticated GRIT marketplace with escrow payments, multicurrency support, negotiation capabilities, and real-time collaboration features.