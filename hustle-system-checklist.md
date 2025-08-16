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
    - [x] `approveApplication()`: Approve a professional and trigger initial escrow (implemented in GritApplicationController).
    - [ ] `startProject()`: Trigger project creation and main escrow flow.
    - [ ] `releasePayment()`: Authorize staged payments.
    - [ ] `increaseBudget()`: Add funds to the project.
    - [ ] `markComplete()`: Mark the GRIT as complete and provide feedback.
- [x] Add endpoint to list only GRITs created by the authenticated business owner (`GET /my-grits`).
- [x] Add endpoints for business owners to manage applications (`GET /grits/{gritId}/applications`, `GET /applications/{applicationId}`, `PATCH /applications/{applicationId}/status`).
- [x] **Enhanced Applications API**: Added pagination support with proper metadata and query parameters.
- [x] **Professional Details API**: New controller and routes for fetching complete professional information with reviews and application status.
- [x] **Professional `GritApplicationController`**:
    - [x] `store()` (POST `/api/grits/{gritId}/apply`): Create application with guards (open/unassigned, no duplicates, optional category match).
    - [x] `index()` (GET `/api/grits/{gritId}/applications`): List applications for a GRIT with professional details and pagination support.
    - [x] `show()` (GET `/api/applications/{applicationId}`): View specific application details.
    - [x] `updateStatus()` (PATCH `/api/applications/{applicationId}/status`): Approve/reject applications and auto-assign professional.
    - [x] `getMyApplications()` (GET `/api/grit-applications`): Get all applications for the authenticated professional.
    - [x] `withdraw()` (POST `/api/grit-applications/{id}/withdraw`): Allow professionals to withdraw their pending applications.
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
- [x] **`BusinessGritDetails.tsx`**: Business owner view for managing their GRITs with edit functionality and applications management.
- [x] **`EditGrit.tsx`**: Business owner form for editing GRITs with admin approval reset.
- [x] **Business vs Professional Views**: Different views based on user account type.
- [x] **Edit Restrictions**: GRITs can only be edited when no professional is assigned and status is 'open'.
- [x] **Admin Approval Reset**: When business edits GRIT, admin_approval_status resets to 'pending'.
- [x] **Chat Integration**: Removed chat tabs from GRIT details pages, kept chat access via dropdown/sidebar buttons that navigate to dedicated chat page.
- [x] **Dashboard Import Fix**: Fixed MessageSquare import error in Dashboard.tsx.
- [x] **Applications Tab Integration**: Integrated compact ApplicationsTab with "View All" navigation to dedicated page.
- [x] **`ApplicationsTab.tsx`**: For business owners to view and manage GRIT applications with professional profiles, stats, and approval/rejection functionality.
- [x] **`GritApplications.tsx`**: Dedicated page for viewing all applications with pagination, search, filtering, and detailed professional information.
- [x] **`ProfessionalDetails.tsx`**: Comprehensive professional details page for viewing complete applicant information, reviews, and application management.
- [x] **`MyApplications.tsx`**: For professionals to view and manage their own GRIT applications with status tracking, withdrawal functionality, search, filtering, and pagination.
- [x] **Navigation Fix**: Fixed professional details navigation issue by replacing `window.location.href` with React Router's `navigate` function in ApplicationsTab and GritApplications components, and reordered routes to prevent conflicts.
- [x] **Professional Chat Access**: Added "Open Chat" button to `GritDetails.tsx` when professional is assigned to GRIT, allowing direct access to chat functionality.
- [x] **View Applications Navigation**: Updated "View Applications" buttons in `BusinessGritDetails.tsx` to navigate to dedicated applications page instead of applications tab.
- [x] **MyApplications Backend Fix**: Added missing `/api/grit-applications` endpoint and controller methods for professionals to view and withdraw their applications.
- [x] **MyApplications Enhanced Features**: Added pagination, search by GRIT title, status filtering, and improved design with better UX and mobile responsiveness.
- [ ] **`GritNegotiationDialog.tsx`**: For modifying terms.
- [ ] **`EscrowStatusCard.tsx`**: To visualize payment stages.
- [ ] **`GritDisputeDialog.tsx`**: For raising and managing disputes.

### Service Layer (`gritService.ts`)
- [x] Implement and wire up GRIT API endpoints (GritController, routes, etc.).
- [x] **GRIT Messaging System**: Created GritMessageController and added message routes to handle chat functionality.
- [x] **Database Fix**: Created migration to fix grit_messages table column structure (rename hustle_id to grit_id) - ✅ COMPLETED.
- [x] **GRIT Applications API**: Added methods for fetching and managing applications (`getGritApplications`, `getApplicationDetails`, `updateApplicationStatus`).
- [x] **Applications Pagination**: Backend pagination support with proper metadata and frontend pagination controls.
- [x] **Applications Search & Filtering**: Search by professional name and filter by application status.
- [ ] Ensure multicurrency amounts are handled correctly for display.
    - [x] `BusinessGritDetails.tsx`: Display budget in `owner_currency` using shared `useCurrencyFormat` helper.
    - [x] `Grits.tsx`: Display owner budget with correct owner currency (handles code vs symbol; graceful fallback) and added debug logging.
    - [ ] Audit remaining views (e.g., all list cards, receipts, any legacy components) for consistent currency display.
- [x] Applications count: use `withCount('applications')` in GRIT listings (`index`, `myGrits`) to provide reliable `applications_count`.
- [x] **Applications Management**: Business owners can view, approve, and reject applications with professional profiles and stats display.
- [x] **Mobile-Responsive Applications**: Compact design for mobile devices with proper responsive layouts.
- [x] **Applications Preview Tab**: Shows 2 most recent applications with "View All" button for full list.
- [x] **Dedicated Applications Page**: Full-featured page with pagination, search, filtering, and detailed professional information.
- [x] **Professional Details Page**: Comprehensive page for viewing complete professional information including reviews, statistics, portfolio, and application management.
- [x] **Application Management Redesign**: Moved approve/reject functionality from application cards to professional details page with "View Applicant" buttons.

### Role-based navigation & access
- [x] Business users are redirected away from the public grits listing (`/dashboard/grits`) to their private listing (`/dashboard/grits/mine`).
- [x] Post-create redirect: Business → `/dashboard/grits/mine`; Professional → `/dashboard/grits`.
- [x] Restrict GRIT creation route (`/dashboard/grits/create`) to Business accounts via `RequireAccountType` + `ProtectedRoute`.
- [x] Show toast guidance when blocked: "Switch to Business profile to access this feature".
- [x] Hide "Create Grit" button in `Grits.tsx` for non-business users.

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
    - [x] **Application Status Notifications (Queued)**:
        - [x] Job `SendGritApplicationStatusNotification` (email + in-app/push) dispatched on approval/rejection
        - [x] Email templates: `emails/applications/approved.blade.php` and `emails/applications/rejected.blade.php`
        - [x] Mail classes: `GritApplicationApproved` and `GritApplicationRejected`
        - [x] Auto-rejection logic: When one application is approved, all other pending applications are automatically rejected
        - [x] Rejection reason: "Another professional has been assigned" for auto-rejected applications
        - [x] **Confirmation Dialogs**: Added confirmation dialogs for application approval/rejection actions
        - [x] Frontend components updated: `GritApplications.tsx`, `ApplicationsTab.tsx`, and `ProfessionalDetails.tsx`
        - [x] User-friendly confirmation messages explaining the consequences of each action
        - [x] Proper UX: approval dialog warns about auto-rejecting other applications, rejection dialog warns about irreversibility
        - [x] Notifications sent to both approved and rejected professionals
        - [x] Controllers updated: `GritApplicationController` and `ProfessionalDetailsController`
        - [x] Admin interface automatically benefits from notification system
        - [x] **Auto-Rejection Notifications**: Added notification dispatch for automatically rejected applications when one is approved
        - [x] **Complete Notification System**: Both `GritApplicationController` and `ProfessionalDetailsController` now dispatch notifications for all status changes
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
- [x] **Chat System Enhanced for Business Owners**:
    - [x] **Database Migration**: Updated `grit_messages` table to support new sender types (`admin`, `professional`, `owner`, `system`)
    - [x] **Backend Updates**: 
        - [x] GritMessageController properly handles all sender types with correct access control
        - [x] GritSystemMessageService created for comprehensive system message handling
        - [x] Application system messages integrated (application approval creates system message)
        - [x] **Real-time Typing Indicators**: Added typing start/stop endpoints and GritTypingEvent for broadcasting typing status
            - [x] **Typing Indicator UX Fixes**: Fixed flickering typing indicators by implementing proper debouncing logic
                - [x] Start typing indicator after 300ms delay (prevents false starts)
                - [x] Stop typing indicator after 2 seconds of no input (more natural feel)
                - [x] Separate timeout references for start/stop to prevent conflicts
                - [x] Auto-scroll to typing indicator when it appears (no manual scrolling required)
                - [x] Reduced cleanup interval frequency and increased timeout for smoother experience
            - [x] **Message Toast Improvements**: Added message truncation for better UX
                - [x] Helper function to truncate long messages to 100 characters
                - [x] Applied truncation to new message notifications and system event notifications
                - [x] Prevents toast layout breaking from very long messages
    - [x] **Frontend Updates**:
        - [x] ChatPage.tsx: Fixed current user detection using `useAuthStore`, proper message positioning, system message display, clean interface without redundant labels, integrated real-time typing indicators
        - [x] GritChat.tsx: Same improvements as ChatPage for consistent experience, integrated real-time typing indicators
        - [x] useGritChat.ts: Updated notifications to handle all sender types and system events, fixed self-notification issue, added typing event handling
        - [x] gritService.ts: Added startTyping and stopTyping API methods
    - [x] **Key Features**:
        - [x] Business owners can send/receive messages in their GRIT chats
        - [x] Proper message positioning (right for current user, left for others)
        - [x] Clean avatar display with proper initials fallback (first_name + last_name + username + '?')
        - [x] System messages with special styling for important events
        - [x] Proper access control for all user types
        - [x] Fixed UUID comparison issues for proper user identification
        - [x] Fixed toast notifications to only show for messages from other users (not self)
        - [x] **Real-time Typing Indicators**: Users see when others are typing with debounced events and automatic cleanup
            - [x] Smooth, non-flickering typing indicators with proper debouncing
            - [x] Automatic scrolling to typing indicators for better visibility
            - [x] Optimized cleanup intervals for consistent user experience
- [x] **Real-time Online Status & Last Seen**: Complete backend and frontend implementation for user presence tracking
    - [x] **Database Migration**: Added presence tracking columns to users table (`online_status`, `last_seen_at`, `last_activity_at`)
    - [x] **UserPresenceService**: Comprehensive service for managing user presence with caching and Pusher broadcasting
    - [x] **UserPresenceController**: Full API endpoints for presence management (update, get, heartbeat, multiple users)
    - [x] **Form Request Validation**: Proper validation for presence status updates
    - [x] **API Routes**: Complete presence API endpoints under `/api/presence/*`
    - [x] **User Model Updates**: Added presence fields to fillable array with proper casts and defaults
    - [x] **Activity Tracking Middleware**: Automatic user activity tracking on API requests
    - [x] **Real-time Broadcasting**: Pusher integration for live presence updates
    - [x] **Performance Optimization**: Caching system with 10-minute TTL for presence data
    - [x] **Smart Status Logic**: Automatic offline detection after 10 minutes of inactivity
    - [x] **Human-readable Timestamps**: Last seen displayed as "X minutes ago", "X hours ago", etc.
    - [x] **Presence Cleanup System**: Automated cleanup of inactive users with scheduled tasks
        - [x] **Console Command**: Created `CleanupPresence` command for manual presence cleanup
        - [x] **Scheduled Tasks**: Automated cleanup every 5 minutes via Laravel scheduler
        - [x] **Logout Integration**: Users marked offline immediately upon logout via LoginController
        - [x] **Page Leave Detection**: Frontend detects when users close browser or navigate away
            - [x] **Event Listeners**: Multiple detection methods (pagehide, beforeunload, visibilitychange, unload)
            - [x] **Synchronous Requests**: Uses synchronous XMLHttpRequest for beforeunload to ensure completion
            - [x] **Smart Tab Handling**: Doesn't mark users offline when switching tabs, only when leaving page
    - [x] **Bug Fixes**: Fixed timing inconsistencies and cache issues
        - [x] **Timing Consistency**: Aligned `isUserOnline()` (10 minutes) with cleanup timing (10 minutes)
        - [x] **Cache Management**: Clear cached presence data when users go offline
        - [x] **Real-time Updates**: Enhanced cleanup to broadcast presence updates when marking users offline
        - [x] **Improved Detection**: Added multiple page leave event listeners for better reliability
        - [x] **Faster Fallback**: Reduced presence refresh interval from 30s to 15s for better accuracy
- [x] **Frontend Integration**: Complete presence display implementation in chat interfaces
    - [x] **PresenceService**: Frontend service for presence API calls
    - [x] **usePresence Hook**: React hook for managing presence state and heartbeat
    - [x] **PresenceIndicator Component**: Reusable UI component for displaying online status
    - [x] **Chat Integration**: Presence indicators added to chat headers, message avatars, and typing indicators
    - [x] **Real-time Updates**: Live presence updates via Pusher integration
    - [x] **Mobile Responsive**: Presence indicators work seamlessly on all screen sizes
    - [x] **Theme Support**: Full light/dark theme compatibility for presence indicators
    - [x] **Fallback System**: Periodic presence refresh (15 seconds) if Pusher connection fails
    - [x] **Heartbeat System**: Automatic heartbeat every 2 minutes to maintain online status
- [ ] **WebSocket Enhancements**: Create new channels and events for GRITs.
- [ ] **Additional System Messages**: Integrate system messages for other key actions (payment releases, budget changes, etc.).
- [ ] **Real-time Notifications**: Trigger notifications for owners, professionals, and admins for relevant events.

## Phase 6: Testing

- [ ] **Unit & Feature Tests**: Write tests for all new services and controller actions.
- [ ] **End-to-End Testing Scenarios**:
    - [ ] Full GRIT lifecycle: Creation -> Application -> Approval -> Project Start -> Payments -> Completion.
    - [ ] Negotiation flow.
    - [ ] Dispute resolution flow.
    - [ ] Budget increase flow.
    - [ ] Currency display and conversion accuracy.
- [ ] **Presence Tracking Testing Scenarios**:
    - [ ] **Real-time Presence Updates**: Test presence status changes in real-time via Pusher
    - [ ] **Logout Presence**: Verify users are marked offline immediately upon logout
    - [ ] **Page Leave Detection**: Test presence updates when users close browser or navigate away
    - [ ] **Tab Switching**: Verify users remain online when switching between tabs
    - [ ] **Inactivity Cleanup**: Test automatic offline marking after 10 minutes of inactivity
    - [ ] **Heartbeat System**: Verify heartbeat maintains online status during active use
    - [ ] **Fallback System**: Test periodic presence refresh when Pusher connection fails
    - [ ] **Cross-browser Presence**: Test presence updates between different browsers/devices
    - [ ] **Presence API Endpoints**: Test all presence API endpoints (update, get, heartbeat, multiple users)
    - [ ] **Scheduled Cleanup**: Test automated presence cleanup command and scheduler
