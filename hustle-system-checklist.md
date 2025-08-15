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
- [x] **Professional `GritApplicationController`**:
    - [x] `store()` (POST `/api/grits/{gritId}/apply`): Create application with guards (open/unassigned, no duplicates, optional category match).
    - [x] Queued notifications on apply:
        - [x] If owner-created → email owner + in-app/push via `NotificationService`.
        - [x] If admin-created → notify active admins (mail + database) via `NewGritApplicationNotification`.
- [x] **Admin `GritController`**:
    - [x] `updateApprovalStatus()`: Approve/reject newly created GRITs with queued email + in-app notifications.
    - [x] `getPendingCount()`: Get count of pending GRITs for admin dashboard.
    - [x] `getByStatus()`: Get GRITs filtered by approval status with search/filtering.
    - [x] `index()`: List GRITs with filtering and view rendering.
    - [x] `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`: Full CRUD operations with new GRIT fields.
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
- [x] **`BusinessGritDetails.tsx`**: Business owner view for managing their GRITs with edit functionality.
- [x] **`EditGrit.tsx`**: Business owner form for editing GRITs with admin approval reset.
- [x] **Business vs Professional Views**: Different views based on user account type.
- [x] **Edit Restrictions**: GRITs can only be edited when no professional is assigned and status is 'open'.
- [x] **Admin Approval Reset**: When business edits GRIT, admin_approval_status resets to 'pending'.
- [x] **Chat Integration**: Removed chat tabs from GRIT details pages, kept chat access via dropdown/sidebar buttons that navigate to dedicated chat page.
- [x] **Dashboard Import Fix**: Fixed MessageSquare import error in Dashboard.tsx.
- [ ] **`ProfessionalProfileModal.tsx`**: For owners to view applicant profiles.
- [ ] **`GritNegotiationDialog.tsx`**: For modifying terms.
- [ ] **`EscrowStatusCard.tsx`**: To visualize payment stages.
- [ ] **`GritDisputeDialog.tsx`**: For raising and managing disputes.

### Service Layer (`gritService.ts`)
- [x] Implement and wire up GRIT API endpoints (GritController, routes, etc.).
- [x] **GRIT Messaging System**: Created GritMessageController and added message routes to handle chat functionality.
- [x] **Database Fix**: Created migration to fix grit_messages table column structure (rename hustle_id to grit_id) - ✅ COMPLETED.
- [ ] Ensure multicurrency amounts are handled correctly for display.
    - [x] `BusinessGritDetails.tsx`: Display budget in `owner_currency` using shared `useCurrencyFormat` helper.
    - [x] `Grits.tsx`: Display owner budget with correct owner currency (handles code vs symbol; graceful fallback) and added debug logging.
    - [ ] Audit remaining views (e.g., all list cards, receipts, any legacy components) for consistent currency display.
- [x] Applications count: use `withCount('applications')` in GRIT listings (`index`, `myGrits`) to provide reliable `applications_count`.

### Role-based navigation & access
- [x] Business users are redirected away from the public grits listing (`/dashboard/grits`) to their private listing (`/dashboard/grits/mine`).
- [x] Post-create redirect: Business → `/dashboard/grits/mine`; Professional → `/dashboard/grits`.
- [x] Restrict GRIT creation route (`/dashboard/grits/create`) to Business accounts via `RequireAccountType` + `ProtectedRoute`.
- [x] Show toast guidance when blocked: “Switch to Business profile to access this feature”.
- [x] Hide “Create Grit” button in `Grits.tsx` for non-business users.

### Form and listing fixes
- [x] `CreateGrit.tsx`: Use UUID string for `category_id`, prevent premature form submit on Enter, and register/validate `deadline`.
- [x] `Grits.tsx`: Safely handle `categories` and `grits.data` mapping to avoid runtime errors when undefined.
- [x] `Grits.tsx`: Format deadline as `15th August, 2025` using `date-fns` (`do MMMM, yyyy`).
- [x] `Grits.tsx`: Show owner budget with correct currency (no USD fallback), using shared currency helpers.
- [x] `BusinessGritDetails.tsx`: Removed chat tab, kept chat access via navigation buttons.
- [x] `GritDetails.tsx`: Removed chat tab, simplified tab structure for professional view.
 - [x] `ChatPage.tsx`: Mobile UX improvements (sticky header and input, hide global dashboard bars on chat route, empty-state when no messages).

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
- [x] **Admin GRIT Forms Updated**: 
- [x] **Application Notifications (Queued)**:
    - [x] Job `SendGritApplicationNotification` (email + in-app/push) dispatched on apply
    - [x] Owner-created GRIT → email owner (`NewGritApplicationSubmitted`) + in-app/push
    - [x] Admin-created GRIT → notify active admins via `NewGritApplicationNotification` (mail + database)
    - [x] Create form now includes `owner_budget`, `owner_currency`, `requirements` fields
    - [x] Edit form now includes all new GRIT fields including `admin_approval_status`
    - [x] Both forms properly handle multicurrency support with dynamic currency loading
    - [x] Currency selection now uses active currencies from database instead of hardcoded values
    - [x] Requirements field handles both skills and other project specifications
    - [x] Removed redundant `skills_required` field to match database schema and user-side approach
    - [x] Forms updated to use correct `admin.grits.*` routes
    - [x] All form labels and text updated from "Hustle" to "GRIT"
    - [x] **Success Messages Added**: All admin GRIT views now display success/error notifications
    - [x] Success messages shown when creating, updating, approving, rejecting, or deleting GRITs
    - [x] Consistent notification system using `showNotification()` function across all views
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
