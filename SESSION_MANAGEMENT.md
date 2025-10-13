# Session Management Implementation

This document describes the global session management and authentication verification system implemented to address session persistence issues.

## Problem
The application was experiencing indefinite session persistence (24+ hours) without proper expiration checks. Sessions were not being validated globally across the application.

## Solution Overview
Implemented a comprehensive session management system with:
1. **Backend token expiration** (2 hours default)
2. **Global frontend session verification** (checks every 3 minutes)
3. **Automatic logout on session expiry**
4. **User activity tracking** (30-minute inactivity timeout)
5. **Enhanced error handling for expired sessions**

---

## Backend Changes

### 1. Sanctum Configuration (`backend/config/sanctum.php`)
**Changed:**
- Token expiration from `null` to `env('SANCTUM_EXPIRATION', 120)` (120 minutes = 2 hours)

**What this does:**
- API tokens now expire after 2 hours by default
- Configurable via `SANCTUM_EXPIRATION` environment variable
- Provides proper security by limiting token lifetime

---

## Frontend Changes

### 1. Session Guard Composable (`frontend/src/composables/useSessionGuard.js`)
**New file created**

**Features:**
- **Periodic session verification**: Checks authentication status every 3-5 minutes (configurable)
- **Activity tracking**: Monitors user interactions (mouse, keyboard, scroll, touch)
- **Inactivity timeout**: Logs out user after 30 minutes of inactivity
- **Automatic cleanup**: Properly removes event listeners on component unmount

**Key Methods:**
- `verifySession()` - Validates session with backend
- `startSessionMonitoring()` - Begins periodic checks
- `stopSessionMonitoring()` - Stops monitoring when logged out
- `updateActivity()` - Tracks user activity
- `handleSessionExpired()` - Handles expired session cleanup

### 2. API Service (`frontend/src/services/api.js`)
**Enhanced with:**

**Request Interceptor:**
- Adds `X-Verify-Session` header every 2 minutes to trigger session validation
- Tracks last session check timestamp
- Prevents excessive verification requests

**Response Interceptor:**
- Improved 401 (Unauthorized) handling
- Added 419 (CSRF) handling for session expiration
- Redirects to login with:
  - Expiry message
  - Current path for redirect after re-login
  - Clear indication that session expired

**What this does:**
- Every API request verifies the token is valid
- Expired tokens immediately redirect to login
- Preserves user's intended destination for post-login redirect

### 3. App Component (`frontend/src/App.vue`)
**Enhanced with:**

**Session Guard Integration:**
- Automatically starts monitoring when user logs in
- Stops monitoring when user logs out
- Watches authentication state for changes

**What this does:**
- Global session monitoring across entire application
- No need to add session checks to individual components
- Works seamlessly in background

### 4. Login View (`frontend/src/views/auth/LoginView.vue`)
**Enhanced with:**

**Session Expiry Alert:**
- Displays warning banner when redirected due to expired session
- Shows custom expiry message
- Clears query parameters after displaying message
- Visually distinct yellow warning box

**What this does:**
- Users understand why they were logged out
- Reduces confusion about session expiration
- Provides clear feedback

---

## How It Works

### Session Verification Flow

1. **User logs in** → Token stored in localStorage
2. **App.vue starts session guard** → Monitors authentication every 3 minutes
3. **Every API request** → Includes auth token
4. **Every 2 minutes** → API service adds session verification flag
5. **User activity tracked** → Resets inactivity timer
6. **If inactive for 30 minutes** → Automatic logout
7. **If token expires** → Backend returns 401
8. **On 401 response** → Automatic logout and redirect to login with message
9. **User sees expiry message** → Clear feedback on login page

### Timeline Example

```
00:00 - User logs in (token valid for 2 hours)
00:03 - First session check (token valid)
00:06 - Second session check (token valid)
...
01:57 - Session check (token valid)
02:00 - Token expires on backend
02:03 - Session check fails (401 response)
02:03 - Automatic logout + redirect to login
02:03 - User sees "Your session has expired" message
```

### Inactivity Timeline Example

```
00:00 - User logs in
00:05 - User clicks (activity tracked)
00:10 - User types (activity tracked)
00:40 - No activity for 30 minutes
00:40 - Automatic logout due to inactivity
00:40 - User sees "Session expired due to inactivity" message
```

---

## Configuration Options

### Backend
Add to `.env` file:
```bash
# Session expiration in minutes (default: 120 = 2 hours)
SANCTUM_EXPIRATION=120
```

### Frontend
In `App.vue`, adjust session check interval:
```javascript
// Check every 3 minutes (default)
const { startSessionMonitoring } = useSessionGuard(3 * 60 * 1000)

// Or check every 5 minutes
const { startSessionMonitoring } = useSessionGuard(5 * 60 * 1000)
```

In `useSessionGuard.js`, adjust inactivity timeout:
```javascript
// Current: 30 minutes
const maxInactiveTime = 30 * 60 * 1000

// Increase to 60 minutes
const maxInactiveTime = 60 * 60 * 1000
```

In `api.js`, adjust session check interval:
```javascript
// Current: 2 minutes
const SESSION_CHECK_INTERVAL = 2 * 60 * 1000

// Increase to 5 minutes
const SESSION_CHECK_INTERVAL = 5 * 60 * 1000
```

---

## Security Benefits

1. **Reduced attack window**: Tokens expire after 2 hours
2. **Inactivity protection**: Auto-logout after 30 minutes idle
3. **Global validation**: Every request validates session
4. **Immediate expiry detection**: No stale sessions
5. **Clean session cleanup**: Proper logout on expiry

---

## User Experience Benefits

1. **Clear feedback**: Users know when and why session expired
2. **Smart redirects**: Returns to intended page after re-login
3. **Activity tracking**: Won't logout active users
4. **Seamless operation**: Works automatically in background
5. **No manual checking**: Global implementation

---

## Testing

### Test Session Expiration
1. Set `SANCTUM_EXPIRATION=1` (1 minute) in backend `.env`
2. Login to application
3. Wait 2 minutes
4. Make any request (navigate, click button, etc.)
5. Should automatically logout and show expiry message

### Test Inactivity Timeout
1. Login to application
2. Don't interact for 30+ minutes
3. Next session check should logout with inactivity message

### Test Session Verification
1. Open browser console → Network tab
2. Login and navigate around
3. Watch for requests with `X-Verify-Session: true` header every 2 minutes
4. Observe automatic session validation

---

## Files Modified

### Backend
- `/backend/config/sanctum.php` - Added token expiration

### Frontend
- `/frontend/src/composables/useSessionGuard.js` - New session guard composable
- `/frontend/src/services/api.js` - Enhanced interceptors
- `/frontend/src/App.vue` - Integrated session monitoring
- `/frontend/src/views/auth/LoginView.vue` - Added expiry alert

---

## Maintenance Notes

- Monitor token expiration time in production (currently 2 hours)
- Adjust inactivity timeout based on user feedback
- Consider adding session extension option for long-running operations
- Review session check intervals if experiencing performance issues

---

## Future Enhancements

1. **Session Extension Dialog**: Prompt user before auto-logout
2. **Configurable Timeouts**: Admin panel to adjust session settings
3. **Session Analytics**: Track session duration and expiry patterns
4. **Remember Me**: Extend session for users who check "Remember me"
5. **Multiple Tab Support**: Sync session state across browser tabs

