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

### Legacy/Compatibility fixes
- [x] Populate legacy single-currency `budget` column from `owner_budget` during GRIT creation (until column is removed/nullable).
- [x] Default `professional_budget` to 0 and `professional_currency` to owner's currency at creation (to satisfy NOT NULL constraints; finalized during negotiation).

### Models
- [x] **Update `User` model**: Add `frozen_balance` attribute and relationships.
- [x] **Rename `Hustle` models** to `Grit` models (`Grit`, `GritApplication`, `GritMessage`, `GritPayment`).
- [x] **Update `Grit` model**: Add new fillable attributes, casts, and relationships (`project`, `escrowTransactions`, `negotiations`, `disputes`).
- [x] **Create `GritEscrowTransaction` model**.
- [x] **Create `GritNegotiation` model**.
- [x] **Create `GritDispute` model**.
- [x] **Update `Professional` model**: Add new fillable attributes and casts.

## Phase 2: Backend Services

- [x] **`GritEscrowService`**:
    - [x] `freezeInitialAmount()`: Freeze 20% on professional approval.
    - [x] `processProjectStart()`: Refund 20%, freeze 100%, release 40%.
    - [x] `releasePayment()`: Release subsequent payments up to 80%.
    - [x] `handleBudgetIncrease()`: Freeze additional funds.
    - [x] `processFinalPayment()`: Release remaining funds on completion.
- [x] **`GritNegotiationService`**:
    - [x] `proposeTerms()`: Allow owner or professional to propose changes.
    - [x] `acceptTerms()`: Finalize terms and trigger payment processing.
    - [x] `rejectTerms()`: Decline proposed terms.
    - [x] `counterOffer()`: Propose new terms in response.
- [x] **`GritDisputeService`**:
    - [x] `raiseDispute()`: Create a new dispute record.
    - [x] `addEvidence()`: Allow parties to upload evidence.
    - [x] `resolveDispute()`: Finalize and record the outcome. resolve and close disputes.
- [x] **`GritProjectIntegrationService`**:
    - [x] `createProjectFromGrit()`: Automatically create a project when a GRIT starts.

## Phase 3: API & Controllers

- [ ] **Business Owner `GritController`**:
    - [x] `store()`: Create a new GRIT (pending admin approval).
    - [ ] `approveApplication()`: Approve a professional and trigger initial escrow.
    - [ ] `startProject()`: Trigger project creation and main escrow flow.
    - [ ] `releasePayment()`: Authorize staged payments.
    - [ ] `increaseBudget()`: Add funds to the project.
    - [ ] `markComplete()`: Mark the GRIT as complete and provide feedback.
- [x] Add endpoint to list only GRITs created by the authenticated business owner (`GET /my-grits`).
- [x] **Admin `GritController`**:
    - [x] `updateApprovalStatus()`: Approve/reject newly created GRITs with queued email + in-app notifications.
    - [x] `getPendingCount()`: Get count of pending GRITs for admin dashboard.
    - [x] `getByStatus()`: Get GRITs filtered by approval status with search/filtering.
    - [x] `index()`: List GRITs with filtering and view rendering.
    - [x] `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`: Full CRUD operations.
    - [ ] `manageDispute()`: View and resolve disputes.
- [ ] **Professional `GritController`**:
    - [ ] `apply()`: Apply for a GRIT.
    - [ ] `respondToTerms()`: Accept or reject negotiation proposals.
- [ ] **Update `ProjectController`**: Ensure business owners can view and manage their newly created projects.

## Phase 4: Frontend Implementation

### Component Renaming & Updates
- [x] Rename `hustleService.ts` to `gritService.ts` (including all interfaces and methods).
- [x] Rename `components/hustles/` to `components/grits/`.
- [x] Rename `pages/hustles/` to `pages/grits/`.
- [x] Rename `useHustleChat.ts` to `useGritChat.ts` (including hook implementation).
- [x] Rename `HustleDetails.tsx` to `GritDetails.tsx` (including component implementation).
- [x] Rename `ApplyHustleDialog.tsx` to `ApplyGritDialog.tsx` (including component implementation).
- [x] Rename `Hustles.tsx` to `Grits.tsx` (including component implementation).
- [x] Rename `MyHustles.tsx` to `MyGrits.tsx` (including component implementation).

### New GRIT Components
- [x] **`CreateGrit.tsx` page**: For business owners to create GRITs.
- [x] **Add `Manage Grits` link to Business Dashboard**: Link added to `Dashboard.tsx` (goes to `/dashboard/grits/mine`).
- [x] **`MyGrits.tsx` page and route (`/dashboard/grits/mine`)**: Business owners can view only their created GRITs.
- [ ] **`ProfessionalProfileModal.tsx`**: For owners to view applicant profiles.
- [ ] **`GritNegotiationDialog.tsx`**: For modifying terms.
- [ ] **`EscrowStatusCard.tsx`**: To visualize payment stages.
- [ ] **`GritDisputeDialog.tsx`**: For raising and managing disputes.

### Service Layer (`gritService.ts`)
- [x] Implement and wire up GRIT API endpoints (GritController, routes, etc.).
- [ ] Ensure multicurrency amounts are handled correctly for display.

### Role-based navigation & access
- [x] Business users are redirected away from the public grits listing (`/dashboard/grits`) to their private listing (`/dashboard/grits/mine`).
- [x] Post-create redirect: Business → `/dashboard/grits/mine`; Professional → `/dashboard/grits`.

### Form and listing fixes
- [x] `CreateGrit.tsx`: Use UUID string for `category_id`, prevent premature form submit on Enter, and register/validate `deadline`.
- [x] `Grits.tsx`: Safely handle `categories` and `grits.data` mapping to avoid runtime errors when undefined.

## Phase 5: Real-time Features

- [x] **GRIT Approval/Rejection Notifications**: 
    - [x] Email templates for approval and rejection
    - [x] Queued job system for notifications (`SendGritNotification`)
    - [x] In-app notifications via NotificationService
    - [x] Push notifications via Firebase Cloud Messaging
    - [x] Both email and in-app notifications sent asynchronously
- [x] **Professional Notifications for New GRITs**: 
    - [x] Email template for new GRIT availability (`new-grit-available.blade.php`)
    - [x] Mail class for professional notifications (`NewGritAvailable`)
    - [x] Queued job to notify all professionals in category (`NotifyProfessionalsAboutNewGrit`)
    - [x] Notifications sent when admin approves business-created GRITs
    - [x] Notifications sent when admin creates GRITs (auto-approved)
    - [x] Both email and in-app notifications sent asynchronously
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
