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
- created_at, updated_at

### conversion_task_types
- id (uuid, primary)
- name (string) — e.g., "Refer to Register", "Complete Course", "Share Product", "Refer to Buy Course"
- description (text, nullable)

### conversion_reward_types
- id (uuid, primary)
- name (string) — e.g., "Cash", "Coupon", "Course Access", "Discount Code"
- description (text, nullable)

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
- [ ] Implement tracking for each task type:
  - [ ] Referral registration tracking
  - [ ] Course completion tracking
  - [ ] Product link sharing tracking
  - [ ] Referral purchase tracking
- [ ] Implement reward granting logic after task completion
- [ ] Integrate with wallet, coupon, course access, and discount systems
- [ ] Testing and QA

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

---

## Next

The next step is to implement tracking for each task type (referral registration, course completion, product sharing, referral purchase).

## Recommended Task Types and Reward Types to Add

### Task Types
| Name                    | Description                                                      |
|-------------------------|------------------------------------------------------------------|
| Refer to Register       | User must refer someone to register on the platform.              |
| Complete Course         | User must complete a specified course.                            |
| Share Product           | User must share a product link.                                   |
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
| Share a Product Link        | Share Product        | Discount Code   | 50-100      | 10% off on a service          | User must share a product link (track share) |
| Refer to Buy a Course       | Refer to Buy Course  | Course Access   | 400-600     | Select a paid course          | User must refer someone who purchases a course |
| Complete a Specific Course  | Complete Course      | Coupon          | 200-400     | Select a store coupon         | User must complete a specific course (select course) |
| Refer to Buy a Product      | Refer to Register    | Cash            | 250-350     | ₦500 cash                     | User must refer someone who buys a product (if supported) |
| Share a Service Link        | Share Product        | Discount Code   | 80-150      | 15% off on a service          | User must share a service link (track share) |
| Refer to Buy a Course       | Refer to Buy Course  | Coupon          | 350-500     | Select a store coupon         | User must refer someone who purchases a course |
| Complete Any Course         | Complete Course      | Course Access   | 150-300     | Select a paid course          | User must complete any course (select course) |

- Adjust the points, reward details, and notes as needed for your business logic.
- For each task, select the correct type and reward, and fill in the required fields in the admin panel.
