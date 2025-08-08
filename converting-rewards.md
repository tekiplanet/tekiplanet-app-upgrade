# Learning Rewards Conversion System

## Overview
Enable users to convert their learning rewards into various benefits, with the conversion process tied to completing specific tasks. Admins can define tasks, associate them with reward point ranges, and specify the reward for each task. The system must track task completion and ensure rewards are granted only after successful completion.

## Conversion Options
- Convert to cash (added to wallet balance)
- Convert to coupon code for store products
- Convert to free access to a paid course
- Convert to a discount code for services

## Task Types (for conversion eligibility)
- Refer people to register on the platform (track referrals)
- Complete a specified course (track course completion)
- Share a product link (track sharing)
- Refer someone to purchase a course (track referral purchases)

## Admin Features
- Add/edit/delete tasks
- Assign tasks to learning reward point ranges
- Specify the reward for each task (cash, coupon, course access, discount code)
- When creating a task, the admin can:
  - Select the task type (e.g., referral, course completion, sharing, etc.)
  - Select the reward type (e.g., cash, coupon, course access, discount code)
  - Based on the reward type, select from existing products, coupons, or courses, or enter a cash amount/discount details
  - For example:
    - If the reward is a coupon, select from available coupons
    - If the reward is course access, select from available paid courses
    - If the reward is cash, enter the amount
    - If the reward is a discount code, enter the percentage and specify the service
  - For some task types (e.g., refer to buy product), select the specific product

## Database Schema (Updated)

### conversion_tasks
- id (uuid, primary)
- title (string)
- description (text, nullable)
- task_type_id (uuid, foreign key to conversion_task_types)
- min_points (integer)
- max_points (integer)
- reward_type_id (uuid, foreign key to conversion_reward_types)
- product_id (uuid, nullable, foreign key to products) — for tasks/rewards involving products
- coupon_id (uuid, nullable, foreign key to coupons) — for coupon rewards
- course_id (uuid, nullable, foreign key to courses) — for course access rewards or course-based tasks
- cash_amount (decimal, nullable) — for cash rewards
- discount_percent (integer, nullable) — for discount code rewards
- service_name (string, nullable) — for discount code rewards
- referral_target (integer, default 1) — for referral tasks
- share_target (integer, default 1) — for share tasks
- created_at, updated_at

### conversion_task_types
- id (uuid, primary)
- name (string) — e.g., "Refer to Register", "Complete Course", "Share Product", "Refer to Buy Course"
- description (text, nullable)

### conversion_reward_types
- id (uuid, primary)
- name (string) — e.g., "Cash", "Coupon", "Course Access", "Discount Code"
- description (text, nullable)

### user_conversion_tasks (Updated)
- id (uuid, primary)
- user_id (uuid, foreign key to users)
- conversion_task_id (uuid, foreign key to conversion_tasks)
- status (string: pending, completed, failed)
- claimed (boolean, default false) — NEW: tracks if reward has been claimed
- claimed_at (timestamp, nullable) — NEW: when the reward was claimed
- assigned_at (timestamp)
- completed_at (timestamp, nullable)
- referral_count (integer, default 0) — for referral tasks
- share_count (integer, default 0) — for share tasks

### user_referrals
- id (uuid, primary)
- referrer_user_id (uuid, foreign key to users)
- referred_user_id (uuid, foreign key to users)
- user_conversion_task_id (uuid, foreign key to user_conversion_tasks)
- registered_at (timestamp)
- status (string: e.g., pending, completed)

### user_product_shares (NEW)
- id (uuid, primary)
- user_id (uuid, foreign key to users)
- user_conversion_task_id (uuid, foreign key to user_conversion_tasks)
- product_id (uuid, foreign key to products)
- share_link (string) — unique tracking link
- shared_at (timestamp)
- purchase_count (integer, default 0) — tracks purchases made through this share link
- status (string: active, completed, expired)

### product_share_purchases (NEW)
- id (uuid, primary)
- user_product_share_id (uuid, foreign key to user_product_shares)
- order_id (uuid, foreign key to orders)
- purchaser_user_id (uuid, foreign key to users) — who made the purchase
- purchased_at (timestamp)
- order_amount (decimal) — total order amount
- status (string: pending, completed, cancelled)

## Admin Conversion Rewards Menu (How It Works)

A new "Conversion Rewards" group is available in the admin sidebar. It contains:

- **Task Types**: Define the types of tasks users can be assigned to convert their rewards (e.g., referral, course completion, sharing, etc.). Admins can add, edit, or delete task types. These are used to categorize tasks.
- **Reward Types**: Define the types of rewards users can receive (e.g., cash, coupon, course access, discount). Admins can add, edit, or delete reward types. These are used to specify what a user gets after completing a task.
- **Tasks**: The main configuration. Admins create tasks, assign them a task type, a reward type, and specify the minimum and maximum reward points required to be eligible for the task. Each task represents a possible conversion action a user can be assigned. Admins can add, edit, or delete tasks. When creating or editing a task, the form will dynamically show the correct selection fields (dropdowns) for products, coupons, or courses based on the chosen task and reward type.

**How they work together:**
- Admins first define the available task types and reward types.
- Then, admins create tasks, selecting from the available types and rewards, and set the points range for eligibility. The form will show the correct fields for each reward type (e.g., coupon dropdown, course dropdown, cash amount, discount details).
- When a user wants to convert their learning rewards, the system will select a random eligible task (based on their points) from the tasks defined here.
- After the user completes the task, the system will grant the specified reward.

## User Flow
1. User chooses to convert learning rewards
2. System selects a random eligible task based on user's reward points
3. User completes the task
4. System verifies completion and grants the specified reward
5. User claims the reward (course access or cash)
6. System marks the reward as claimed and prevents duplicate claims

---

## Implementation Checklist

- [x] Design database schema for tasks, task types, reward point ranges, and reward types
  - All new tables use UUIDs as primary keys and for all relevant foreign keys for consistency.
- [x] Create Eloquent models for all conversion tables
  - Models use UUIDs and define relationships for easy querying and logic.
- [x] Implement admin controllers and routes for managing tasks and assigning rewards
  - Resource controllers and routes created for task types, reward types, and tasks.
- [x] Implement admin Blade views and modals for managing tasks and rewards
  - Views and modals scaffolded for task types, reward types, and tasks, matching project conventions.
- [x] Implement dynamic admin form for task creation/editing, showing correct fields for products, coupons, courses, cash, or discount based on selection
- [x] Implement logic to select a random eligible task based on user's reward points
- [x] Deduct user's learning rewards and create user_conversion_tasks record on conversion initiation
- [x] Implement API endpoint and controller for conversion initiation (user POST /rewards/convert)
- [x] Implement frontend rewardService for fetching tasks and initiating conversion
- [x] Create Rewards & Tasks page and add to Learning group in dashboard menu
- [x] Implement backend endpoint for fetching user tasks (GET /rewards/tasks)
- [x] Implement user interface for converting rewards and viewing/completing tasks
- [x] Implement backend service for conversion initiation, task assignment, and user task creation
- [x] Implement RewardConversionController and RewardConversionService for conversion logic
- [x] Implement UserConversionTask and ConversionTask models and relationships
- [x] API endpoints for conversion initiation, user task listing, and debug are available
- [x] Implement tracking for each task type:
  - [x] Referral registration tracking (backend endpoint for instructions/referral link implemented)
  - [ ] Product link sharing tracking
  - [ ] Course completion tracking
  - [ ] Referral purchase tracking
- [x] Implement reward granting logic after task completion (referral registration only)
- [x] Integrate with wallet, coupon, course access, and discount systems
- [x] Create a user_referrals table to track each referral event:
  - id (uuid, primary)
  - referrer_user_id (uuid, foreign key to users)
  - referred_user_id (uuid, foreign key to users)
  - user_conversion_task_id (uuid, foreign key to user_conversion_tasks)
  - registered_at (timestamp)
  - status (string: e.g., pending, completed)
- [x] Add referral_count to user_conversion_tasks for tracking referral progress
- [x] Update registration logic to handle referral links: create UserReferral, increment referral_count, and mark task as completed if target is reached
- [x] Fix currency conversion for course access rewards display
- [x] Make reward modals vertically scrollable for better UX
- [x] Fix currency conversion for course enrollment when claiming course access rewards
- [x] Add loading states to claim buttons for better UX
- [x] Implement robust error handling for already enrolled users
- [x] Add claimed tracking system to prevent duplicate claims
- [x] Implement cash reward functionality with currency conversion
- [ ] Testing and QA

---

## Product Link Sharing Tracking Implementation Plan

### Phase 1: Database Schema Updates

#### 1.1 Update conversion_tasks table
- Add `share_target` column (integer, default 1) for share tasks
- Add `product_id` column (uuid, nullable) for product-specific share tasks

#### 1.2 Create user_product_shares table
```sql
CREATE TABLE user_product_shares (
    id uuid PRIMARY KEY,
    user_id uuid REFERENCES users(id) ON DELETE CASCADE,
    user_conversion_task_id uuid REFERENCES user_conversion_tasks(id) ON DELETE CASCADE,
    product_id uuid REFERENCES products(id) ON DELETE CASCADE,
    share_link varchar(500) NOT NULL,
    shared_at timestamp DEFAULT CURRENT_TIMESTAMP,
    purchase_count integer DEFAULT 0,
    status varchar(50) DEFAULT 'active',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);
```

#### 1.3 Create product_share_purchases table
```sql
CREATE TABLE product_share_purchases (
    id uuid PRIMARY KEY,
    user_product_share_id uuid REFERENCES user_product_shares(id) ON DELETE CASCADE,
    order_id uuid REFERENCES orders(id) ON DELETE CASCADE,
    purchaser_user_id uuid REFERENCES users(id) ON DELETE CASCADE,
    purchased_at timestamp DEFAULT CURRENT_TIMESTAMP,
    order_amount decimal(10,2) NOT NULL,
    status varchar(50) DEFAULT 'pending',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);
```

#### 1.4 Update user_conversion_tasks table
- Add `share_count` column (integer, default 0) for tracking share progress

### Phase 2: Backend Implementation

#### 2.1 Create Eloquent Models
- **UserProductShare**: Model for tracking user's product shares
- **ProductSharePurchase**: Model for tracking purchases made through share links

#### 2.2 Update ConversionTask Model
- Add relationship to Product model
- Add validation for product_id when task type is "Share Product"

#### 2.3 Update UserConversionTask Model
- Add `share_count` to fillable array
- Add method to generate unique share links
- Add relationship to UserProductShare model

#### 2.4 Update RewardConversionController
- Extend `getTaskInstructions()` method to handle "Share Product" tasks:
  - Generate unique share link for the specific product
  - Return product details and share instructions
  - Return progress tracking (current shares vs target)
- Add method to track share link clicks
- Add method to track purchases made through share links

#### 2.5 Create Share Tracking Service
- **ProductShareService**: Handle share link generation, tracking, and purchase detection
- Methods:
  - `generateShareLink(UserConversionTask $userTask, Product $product)`
  - `trackShareClick(string $shareLink)`
  - `trackPurchase(string $shareLink, Order $order, User $purchaser)`
  - `checkTaskCompletion(UserConversionTask $userTask)`

#### 2.6 Update Order Processing
- Modify `OrderController::store()` to detect share links in the request
- Track purchases made through share links
- Update share counts and mark tasks as completed when targets are met

### Phase 3: Frontend Implementation

#### 3.1 Update Task Instructions Display
- Modify TasksPage.tsx to show product details and share link for "Share Product" tasks
- Add copy-to-clipboard functionality for share links
- Display progress tracking (shares needed vs completed)

#### 3.2 Add Share Link Generation
- Update rewardService to fetch product details and share instructions
- Handle share link display and copying
- Show product information (name, image, price) in the task modal

#### 3.3 Update Product Details Page
- Add share tracking parameters to product URLs when accessed through share links
- Preserve share link information during the purchase flow

### Phase 4: Admin Panel Updates

#### 4.1 Update Task Creation Form
- Add product selection dropdown for "Share Product" tasks
- Add share target input field
- Validate that product is selected when task type is "Share Product"

#### 4.2 Add Share Analytics
- Display share statistics in admin dashboard
- Show which products are being shared most
- Track conversion rates from shares to purchases

### Phase 5: Testing and Integration

#### 5.1 Test Share Link Generation
- Verify unique share links are generated for each user/task combination
- Test share link format and parameters

#### 5.2 Test Purchase Tracking
- Test purchase detection through share links
- Verify share counts are incremented correctly
- Test task completion when share targets are met

#### 5.3 Test Frontend Integration
- Test share link display and copying
- Test progress tracking display
- Test task completion notifications

### Implementation Steps

1. **Database Migrations**
   - Create migration for user_product_shares table
   - Create migration for product_share_purchases table
   - Add share_target and share_count columns to existing tables

2. **Backend Models**
   - Create UserProductShare and ProductSharePurchase models
   - Update existing models with new relationships
   - Add share link generation methods

3. **Backend Controllers**
   - Update RewardConversionController for share task instructions
   - Add share tracking endpoints
   - Update OrderController for purchase tracking

4. **Frontend Updates**
   - Update TasksPage for share task display
   - Add share link functionality
   - Update product details page for share tracking

5. **Admin Panel**
   - Update task creation form for product selection
   - Add share analytics dashboard

6. **Testing**
   - Test complete share-to-purchase flow
   - Verify task completion and reward claiming

---

## Progress Log

- **2024-06-08:** Started and in progress: Conversion initiation logic (deduct points, assign random eligible task, create user_conversion_tasks record).
- **2024-06-08:** Implemented API endpoint and controller for conversion initiation (user POST /rewards/convert).
- **2024-06-08:** Implemented frontend rewardService for fetching tasks and initiating conversion.
- **2024-06-08:** Created Rewards & Tasks page and added to Learning group in dashboard menu.
- **2024-06-08:** Implemented backend endpoint for fetching user tasks (GET /rewards/tasks).
- **2024-06-08:** Completed user interface for converting rewards and viewing/completing tasks with modern design.
- **2024-06-08:** Added confirmation dialog for reward conversion and improved error messages for better user experience.
- **2024-06-08:** Created dedicated tasks page with filtering, sorting, and modern UI design. Modified Rewards & Tasks page to show only 2 recent tasks with "View All Tasks" button.
- **2024-06-08:** Implemented backend pagination, filtering, and sorting for optimal performance with large datasets. Added pagination controls and per-page selection.
- **2024-06-09:** Backend models, controllers, and services for conversion tasks, task types, reward types, and user conversion tasks are implemented. Conversion initiation, task assignment, and user task listing are working. Next: actionable instructions, referral link generation, and tracking.
- **2024-06-10:** Registration now processes referral links, creates UserReferral records, increments referral_count, and marks tasks as completed when the referral target is met.
- **2024-06-11:** Backend endpoint for actionable instructions and referral link for referral registration tasks implemented. Next: frontend to fetch and display referral link with copy feature.
- **2024-06-11:** Frontend now fetches and displays actionable instructions and referral link for referral tasks, with modern, responsive design and copy-to-clipboard feature.
- **2024-06-11:** Fixed referral registration flow: frontend now properly captures and forwards referral parameters to backend, and backend creates UserReferral records and increments referral count correctly.
- **2024-06-11:** Fixed admin panel form: reward-specific fields (coupon dropdown, course dropdown, cash amount field, etc.) now show properly when editing tasks without requiring reward type changes.
- **2024-06-11:** Implemented course access reward claiming: users can now claim free course access with fully covered enrollment and tuition fees, automatic enrollment, and redirect to course management.
- **2024-06-11:** Fixed currency display in course access rewards: tuition and enrollment fees now show in user's preferred currency with proper conversion, matching the wallet dashboard and course details pages.
- **2024-06-11:** Resolved URL parsing issue with hash routing - referral parameters are now properly extracted from URLs with hash fragments.
- **2024-12-19:** Fixed currency conversion issues in TasksPage.tsx modal - tuition and enrollment fees now properly convert to user's currency and display correct symbols.
- **2024-12-19:** Made reward modals vertically scrollable by adding max-height and overflow-y-auto classes for better UX when content is long.
- **2024-12-19:** Fixed currency conversion for course enrollment when claiming course access rewards - amounts are now converted to user's currency before saving to database.
- **2024-12-19:** Added loading states to "Claim Course Access" button to prevent multiple clicks and provide visual feedback during the claiming process.
- **2024-12-19:** Implemented robust error handling for already enrolled users - frontend now correctly parses backend error responses and shows user-friendly messages instead of generic 400 errors.
- **2024-12-19:** Added claimed tracking system to prevent duplicate claims:
  - Added `claimed` and `claimed_at` columns to `user_conversion_tasks` table
  - Updated backend to check and set claimed status when rewards are claimed
  - Updated frontend to conditionally show claim button or "Reward Claimed" status
- **2024-12-19:** Implemented cash reward functionality:
  - Backend converts cash amounts to user's currency for display
  - Added new API endpoint for claiming cash rewards (`POST /rewards/tasks/{id}/claim-cash`)
  - Cash rewards are added to user's wallet balance with proper transaction records
  - Frontend displays converted cash amounts and provides claim functionality
  - Loading states and claimed status tracking for cash rewards
- **2024-12-19:** Created comprehensive implementation plan for product link sharing tracking system

---

## Recent Features Implemented

### Currency Conversion Fixes
- **Course Access Rewards Display**: Fixed currency conversion in reward modals to properly display tuition and enrollment fees in user's preferred currency with correct symbols.
- **Course Enrollment Conversion**: Fixed currency conversion when claiming course access rewards - amounts are now converted to user's currency before saving to database, matching normal enrollment behavior.
- **Cash Rewards Display**: Cash rewards now display in user's currency (converted from NGN) with proper formatting.

### User Experience Improvements
- **Modal Scrollability**: Reward modals are now vertically scrollable with proper max-height and overflow handling for better UX.
- **Loading States**: Added loading spinners and disabled states to claim buttons to prevent multiple submissions and provide visual feedback.
- **Error Handling**: Improved error handling for already enrolled users with specific, user-friendly error messages instead of generic 400 errors.

### Claim Tracking System
- **Database Schema**: Added `claimed` (boolean) and `claimed_at` (timestamp) columns to `user_conversion_tasks` table.
- **Backend Logic**: Updated backend to check claimed status before processing claims and mark rewards as claimed after successful processing.
- **Frontend Display**: Frontend now conditionally shows claim buttons or "Reward Claimed" status based on claimed state.

### Cash Reward Functionality
- **Currency Conversion**: Cash amounts are converted to user's currency for display and processing.
- **Claim Mechanism**: Users can claim cash rewards which are added to their wallet balance.
- **Transaction Records**: Proper transaction records are created when cash rewards are claimed.
- **UI Integration**: Cash rewards have their own UI section with claim functionality and loading states.

## Next Step: Product Link Sharing Tracking Implementation

Currently, when a user starts a "Share Product" task, the system does not yet display actionable instructions or generate unique share links for products. Nor does it track user actions (such as link clicks or purchases made through share links) for task completion.

### What Needs to Be Implemented
- When a user starts a "Share Product" task, the UI should display:
  - Product details (name, image, price, description)
  - A unique share link for the specific product with tracking parameters
  - Clear instructions on how to share and what counts as completion
  - Progress tracking (current shares vs target)
- The backend should:
  - Generate unique share links for each user/task/product combination
  - Track usage of these share links (clicks, purchases, etc.)
  - Detect purchases made through share links
  - Mark tasks as completed when purchase targets are met
- The API should:
  - Provide endpoints to get product details and share instructions for a user's task
  - Provide endpoints to track share link usage and purchases
  - Update task progress and completion status

**This is the next milestone for the project.**

We will implement this step by step, starting with the database schema updates and backend models.

## Next Step

- Implement database migrations for product sharing tracking tables
- Create UserProductShare and ProductSharePurchase models
- Update ConversionTask and UserConversionTask models with new relationships

## Recommended Task Types and Reward Types to Add

### Task Types
| Name                    | Description                                                      |
|-------------------------|------------------------------------------------------------------|
| Refer to Register       | User must refer someone to register on the platform.              |
| Complete Course         | User must complete a specified course.                            |
| Share Product           | User must share a product link and someone must purchase through the link. |
| Refer to Buy Course     | User must refer someone to purchase a course.                     |

### Reward Types
| Name           | Description                                                      |
|----------------|------------------------------------------------------------------|
| Cash           | User receives a cash reward added to their wallet balance.        |
| Coupon         | User receives a coupon code for store products.                  |
| Course Access  | User receives free access to a paid course.                      |
| Discount Code  | User receives a code for a percentage discount on a service.     |

## Example Conversion Tasks to Create (Admin)

Below are recommended example tasks you should create to cover all main scenarios. Adjust the point ranges and details as needed for your platform.

| Task Title                  | Task Type             | Reward Type     | Points Range | Reward Details                | Notes |
|-----------------------------|----------------------|-----------------|-------------|-------------------------------|-------|
| Refer a Friend to Register  | Refer to Register    | Coupon          | 100-200     | Select a store coupon         | User must refer a new user who completes registration |
| Complete Any Paid Course    | Complete Course      | Cash            | 300-500     | ₦1,000 cash                   | User must complete a paid course |
| Share a Product Link        | Share Product        | Discount Code   | 50-100      | 10% off on a service          | User must share a product link and someone must purchase through it |
| Refer to Buy a Course       | Refer to Buy Course  | Course Access   | 400-600     | Select a paid course          | User must refer someone who purchases a course |
| Complete a Specific Course  | Complete Course      | Coupon          | 200-400     | Select a store coupon         | User must complete a specific course (select course) |
| Refer to Buy a Product      | Refer to Register    | Cash            | 250-350     | ₦500 cash                     | User must refer someone who buys a product (if supported) |
| Share a Service Link        | Share Product        | Discount Code   | 80-150      | 15% off on a service          | User must share a service link and someone must purchase through it |
| Refer to Buy a Course       | Refer to Buy Course  | Coupon          | 350-500     | Select a store coupon         | User must refer someone who purchases a course |
| Complete Any Course         | Complete Course      | Course Access   | 150-300     | Select a paid course          | User must complete any course (select course) |

- Adjust the points, reward details, and notes as needed for your business logic.
- For each task, select the correct type and reward, and fill in the required fields in the admin panel.
