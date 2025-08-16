# Online Presence Tracking System - Complete Guide

## **🏗️ The Big Picture**
Think of presence tracking like a "status system" that tells other users if you're currently using the app or not. It's like WhatsApp's "online/offline" status, but more sophisticated.

## **📊 Database Storage**
The system stores 3 key pieces of information for each user:
- **`online_status`**: "online", "away", or "offline"
- **`last_activity_at`**: When the user last did something (clicked, typed, etc.)
- **`last_seen_at`**: When the user was last seen online

## **🔄 The Complete Lifecycle**

### **1. User Logs In**
- User logs in → `online_status` = "online"
- `last_activity_at` = current time
- System broadcasts to all other users: "User X is now online"

### **2. User is Active (Using the App)**
- **Every API request** (clicking buttons, sending messages, etc.) → `last_activity_at` = current time
- **Every 2 minutes** → "Heartbeat" sent to keep user online
- **Real-time updates** → Other users see live status changes via Pusher

### **3. User Switches Tabs or Minimizes Browser**
- **Tab switching** → User stays "online" (smart detection)
- **Browser minimized** → User stays "online" 
- **Heartbeat continues** → Every 2 minutes to maintain online status

### **4. User Closes Browser/Tab or Logs Out**
- **Multiple detection methods**:
  - `beforeunload` event → "User is about to leave"
  - `pagehide` event → "User is leaving the page"
  - `unload` event → "User has left the page"
- **Synchronous request** → Immediately tells server "I'm going offline"
- **Server marks user offline** → `online_status` = "offline", `last_seen_at` = current time
- **Broadcasts to all users** → "User X is now offline"

### **5. User is Inactive (No Activity)**
- **After 5 minutes of no activity** → Automatic cleanup marks user offline
- **Scheduled task runs every 5 minutes** → Checks for inactive users
- **Broadcasts updates** → Other users see status change

## **⏰ Timing Breakdown**

| Action | Timing | What Happens |
|--------|--------|--------------|
| **Heartbeat** | Every 2 minutes | Keeps user online |
| **Activity tracking** | Every API request | Updates last activity |
| **Fallback refresh** | Every 15 seconds | If Pusher fails |
| **Cleanup check** | Every 5 minutes | Marks inactive users offline |
| **Offline threshold** | 5 minutes | How long before considered offline |

## **🛠️ How It Actually Works**

### **Backend (Server Side)**
1. **Middleware** → Every API request updates user's activity time
2. **Heartbeat endpoint** → Keeps users online every 2 minutes
3. **Cleanup scheduler** → Runs every 5 minutes, marks inactive users offline
4. **Pusher broadcasting** → Sends real-time updates to all connected users
5. **Caching** → Stores presence data for 5 minutes for performance

### **Frontend (Browser Side)**
1. **Event listeners** → Detects when user leaves page
2. **Heartbeat timer** → Sends "I'm still here" every 2 minutes
3. **Pusher subscription** → Listens for other users' status changes
4. **Fallback system** → Refreshes presence data every 15 seconds if Pusher fails

## **🎯 The Key Improvements Made**

### **Before (The Problem)**
- `isUserOnline()` used 5 minutes
- `cleanupOldPresence()` used 10 minutes
- **Result**: 5-minute gap where users appeared online but should be offline

### **After (The Fix)**
- Both use 5 minutes consistently
- Cache is cleared when users go offline
- Multiple page leave detection methods
- Faster fallback refresh (15s instead of 30s)

## **🌍 Real-World Example**

**Scenario**: Alice and Bob are chatting

1. **Alice logs in** → Bob sees "Alice is online"
2. **Alice sends a message** → Her activity time updates
3. **Alice closes her browser** → Multiple events fire, server marks her offline
4. **Bob sees update** → "Alice is offline" (within 15 seconds)
5. **If Alice doesn't close properly** → After 5 minutes, system marks her offline automatically

## **🛡️ Reliability Features**

- **Multiple detection methods** → If one fails, others catch it
- **Synchronous requests** → Ensures offline status is sent before page closes
- **Fallback system** → If real-time updates fail, periodic refresh works
- **Automatic cleanup** → Catches users who don't close properly
- **Cache management** → Ensures fresh data is always shown

## **🔧 Technical Components**

### **Backend Files**
- `UserPresenceService.php` - Core presence logic
- `UserPresenceController.php` - API endpoints
- `TrackUserActivity.php` - Middleware for activity tracking
- `CleanupPresence.php` - Console command for cleanup
- `LoginController.php` - Logout integration

### **Frontend Files**
- `usePresence.ts` - React hook for presence management
- `presenceService.ts` - API service calls
- `PresenceIndicator.tsx` - UI component for status display
- `ChatPage.tsx` - Integration in chat interface

### **Database**
- `users` table with presence columns
- Migration: `2025_01_15_000000_add_presence_tracking_to_users_table.php`

## **🚀 Performance Optimizations**

- **Caching** → 5-minute TTL for presence data
- **Batch updates** → Multiple users' presence fetched at once
- **Real-time broadcasting** → Instant updates via Pusher
- **Fallback system** → Periodic refresh if real-time fails
- **Smart cleanup** → Only processes inactive users

## **📱 Mobile & Cross-Platform Support**

- **Mobile browsers** → Same detection methods work
- **App switching** → Users stay online when switching apps
- **Background tabs** → Heartbeat continues in background
- **Network issues** → Fallback system handles disconnections

This system ensures that users' online status is accurate and updates quickly, providing a smooth real-time experience for everyone using the chat system.
