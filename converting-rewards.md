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
- [ ] Implement admin interface for managing tasks and assigning rewards
- [ ] Implement tracking for each task type:
  - [ ] Referral registration tracking
  - [ ] Course completion tracking
  - [ ] Product link sharing tracking
  - [ ] Referral purchase tracking
- [ ] Implement user interface for converting rewards and viewing/completing tasks
- [ ] Implement logic to select a random eligible task based on user's reward points
- [ ] Implement reward granting logic after task completion
- [ ] Integrate with wallet, coupon, course access, and discount systems
- [ ] Testing and QA

---

Next: Implement the admin interface for managing tasks and assigning rewards.
