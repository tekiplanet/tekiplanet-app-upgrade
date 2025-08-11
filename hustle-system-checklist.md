# GRIT System Upgrade Checklist

This checklist tracks the implementation of the GRIT system, including the integration with the project management system.

## Phase 1: Database & Models

### Migrations
- [x] **Users Table**: Add `frozen_balance` column.
- [x] **Rename Tables**: Rename `hustles` and related tables to `grits`.
- [x] **Grits Table**:
    - [x] Add columns for business owner (`created_by_user_id`, `admin_approval_status`).
    - [x] Add columns for multicurrency budget (`owner_budget`, `owner_currency`, `professional_budget`, `professional_currency`).
    - [x] Add columns for workflow management (`negotiation_status`, `terms_modified_at`, `project_started_at`, `completion_requested_at`).
    - [x] Add columns for feedback and disputes (`owner_satisfaction`, `owner_rating`, `dispute_status`).
    - [x] Add `project_id` for project integration.
- [x] **Create `grit_escrow_transactions` table**: For managing frozen/released funds.
- [x] **Create `grit_negotiations` table**: To track terms negotiation history.
- [x] **Create `grit_disputes` table**: For handling disputes between parties.
- [x] **Professionals Table**: Enhance with `completion_rate`, `average_rating`, `total_projects_completed`, `qualifications`, and `portfolio_items`.

### Models
- [ ] **Update `User` model**: Add `frozen_balance` attribute and relationships.
- [ ] **Rename `Hustle` models** to `Grit` models (`Grit`, `GritApplication`, `GritMessage`, `GritPayment`).
- [ ] **Update `Grit` model**: Add new fillable attributes, casts, and relationships (`project`, `escrowTransactions`, `negotiations`, `disputes`).
- [ ] **Create `GritEscrowTransaction` model**.
- [ ] **Create `GritNegotiation` model**.
- [ ] **Create `GritDispute` model**.
- [ ] **Update `Professional` model**: Add new fillable attributes and casts.

## Phase 2: Backend Services

- [ ] **`GritEscrowService`**:
    - [ ] `freezeInitialAmount()`: Freeze 20% on professional approval.
    - [ ] `processProjectStart()`: Refund 20%, freeze 100%, release 40%.
    - [ ] `releasePayment()`: Release subsequent payments up to 80%.
    - [ ] `handleBudgetIncrease()`: Freeze additional funds.
    - [ ] `processFinalPayment()`: Release remaining funds on completion.
- [ ] **`GritNegotiationService`**:
    - [ ] `proposeTerms()`: Allow owner or professional to propose changes.
    - [ ] `acceptTerms()`: Finalize terms and trigger payment processing.
    - [ ] `rejectTerms()`: Handle rejection of proposed terms.
- [ ] **`GritDisputeService`**:
    - [ ] `raiseDispute()`: Create a new dispute record.
    - [ ] `addEvidence()`: Allow parties to upload evidence.
    - [ ] `resolveDispute()`: Admin functionality to resolve and close disputes.
- [ ] **`GritProjectIntegrationService`**:
    - [ ] `createProjectFromGrit()`: Automatically create a project when a GRIT starts.

## Phase 3: API & Controllers

- [ ] **Business Owner `GritController`**:
    - [ ] `store()`: Create a new GRIT (pending admin approval).
    - [ ] `approveApplication()`: Approve a professional and trigger initial escrow.
    - [ ] `startProject()`: Trigger project creation and main escrow flow.
    - [ ] `releasePayment()`: Authorize staged payments.
    - [ ] `increaseBudget()`: Add funds to the project.
    - [ ] `markComplete()`: Mark the GRIT as complete and provide feedback.
- [ ] **Admin `GritController`**:
    - [ ] `approveGrit()`: Approve a newly created GRIT to make it public.
    - [ ] `manageDispute()`: View and resolve disputes.
- [ ] **Professional `GritController`**:
    - [ ] `apply()`: Apply for a GRIT.
    - [ ] `respondToTerms()`: Accept or reject negotiation proposals.
- [ ] **Update `ProjectController`**: Ensure business owners can view and manage their newly created projects.

## Phase 4: Frontend Implementation

### Component Renaming & Updates
- [ ] Rename `hustleService.ts` to `gritService.ts`.
- [ ] Rename `components/hustles/` to `components/grits/`.
- [ ] Rename `pages/hustles/` to `pages/grits/`.
- [ ] Rename `useHustleChat.ts` to `useGritChat.ts`.

### New GRIT Components
- [ ] **`CreateGritDialog.tsx`**: For business owners to create GRITs.
- [ ] **`ProfessionalProfileModal.tsx`**: For owners to view applicant profiles.
- [ ] **`GritNegotiationDialog.tsx`**: For modifying terms.
- [ ] **`EscrowStatusCard.tsx`**: To visualize payment stages.
- [ ] **`GritDisputeDialog.tsx`**: For raising and managing disputes.

### Service Layer (`gritService.ts`)
- [ ] Add functions for all new GRIT actions (create, approve, negotiate, etc.).
- [ ] Ensure multicurrency amounts are handled correctly for display.

## Phase 5: Real-time Features

- [ ] **WebSocket Enhancements**: Create new channels and events for GRITs.
- [ ] **System Messages**: Integrate system messages into the chat for all key actions (e.g., "Payment of $50 released.").
- [ ] **Real-time Notifications**: Trigger notifications for owners, professionals, and admins for relevant events.

## Phase 6: Testing

- [ ] **Unit & Feature Tests**: Write tests for all new services and controller actions.
- [ ] **End-to-End Testing Scenarios**:
    - [ ] Full GRIT lifecycle: Creation -> Application -> Approval -> Project Start -> Payments -> Completion.
    - [ ] Negotiation flow.
    - [ ] Dispute resolution flow.
    - [ ] Budget increase flow.
    - [ ] Currency display and conversion accuracy.
