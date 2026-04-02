# 🎬 MovieStream - Feature Usage Guide

## 🔔 How to Use Notifications

### 1. View Notifications
- Look at the top-right corner of the navbar
- You'll see a **bell icon** (🔔)
- If there are unread notifications, a **red badge** shows the count

### 2. Open Notification Dropdown
- Click the bell icon
- A dropdown will appear showing all your notifications
- Notifications are sorted by newest first

### 3. Interact with Notifications
- **View**: Click on any notification to see details
- **Mark as Read**: Click "Mark read" on unread notifications (removes the red dot)
- **Mark All as Read**: Click "Mark all as read" at the top
- **Delete**: Click the trash icon to remove a notification
- **Follow Link**: Click "View" to navigate to the linked page

### 4. Notification Types
Each type has a different icon and color:
- 💳 **Payment** (Green) - Subscription and payment updates
- 🎬 **Movie** (Red) - New releases and recommendations
- 👋 **Welcome** (Blue) - Welcome messages
- ℹ️ **System** (Yellow) - System updates and maintenance

---

## 💳 How to Subscribe to Premium

### Method 1: From Profile Page
1. Navigate to **Profile** (click your name in sidebar)
2. Scroll down to **"Premium Subscription"** section
3. Review the benefits:
   - Access to all premium movies
   - Ad-free streaming
   - Early access to new releases
   - HD & 4K quality
4. Click **"Subscribe Now"** button
5. In **Demo Mode**: Subscription activates immediately
6. In **Production**: You'll be redirected to Chapa payment gateway
7. After activation, you'll see your subscription status and expiry date

### Method 2: From Premium Movie
1. Try to watch a premium movie (marked with 👑 badge)
2. You'll see a locked overlay
3. Click **"Upgrade to Premium"** button
4. You'll be redirected to your profile
5. Follow the steps above

### Check Your Subscription Status
On your profile page, you'll see:
- **Free User**: Upgrade card with benefits and pricing
- **Premium User**: 
  - Status badge
  - Expiry date
  - Days remaining
  - Renewal button

---

## 🔍 How to Use Advanced Search

### 1. Open Search Dropdown
- Click the **search icon** in the navbar
- A large dropdown appears with search options

### 2. Search by Text
- Type in the search box
- Search works for:
  - Movie titles
  - Descriptions
  - Genre names
- Results appear **instantly** as you type

### 3. Filter by Genre
- Click the **"Genre"** dropdown
- Select any genre (Action, Drama, Comedy, etc.)
- Results update immediately
- Combines with text search

### 4. Filter by Year
- Click the **"Year"** dropdown
- Select a year (last 30 years available)
- Results update immediately
- Combines with other filters

### 5. Combine Filters
You can use all three at once:
- Text: "knight"
- Genre: "Action"
- Year: "2008"
- Result: The Dark Knight (2008)

### 6. View Results
Each result shows:
- **Poster** thumbnail
- **Title** and description
- **Genre** badge
- **Year** of release
- **Rating** (if available)
- **👑 Premium** badge (if premium)

### 7. Navigate to Movie
- Click on any result
- You'll be taken to the movie watch page
- The dropdown auto-closes

### 8. Clear Filters
- Click **"Clear Filters"** to reset everything
- Or close dropdown and reopen

---

## 🎥 How to Watch Premium Movies

### If You're a Free User
1. Browse movies or search for content
2. Click on a movie with **👑 Premium** badge
3. You'll see a **locked overlay** with:
   - Premium content message
   - Upgrade button
   - Browse free content option
4. Click **"Upgrade to Premium"**
5. Subscribe (see subscription guide above)
6. Come back and watch!

### If You're a Premium User
1. All movies are unlocked
2. Premium badge shown but no restrictions
3. Video plays normally
4. Progress is saved automatically

---

## 🎯 Quick Tips

### Notifications
- **Auto-refresh**: Notifications update every 30 seconds
- **Unread count**: Badge shows actual number or "9+" if more than 9
- **Click outside**: Closes the dropdown
- **No notifications?**: System will show "No notifications yet"

### Payments (Demo Mode)
- **Instant activation**: No waiting or external payment
- **30 days**: Default subscription period
- **Transaction record**: Saved in database with status "completed"
- **Notification**: You'll get a notification confirming activation

### Search
- **10 results max**: To keep it fast
- **Real-time**: No need to press Enter
- **Case-insensitive**: "ACTION" = "action" = "Action"
- **Partial match**: "dark" finds "The Dark Knight"
- **View all**: Click "View All Results" to see more

### Premium Content
- **Premium badge**: 👑 icon on movie titles
- **Access control**: Video player locked for free users
- **Subscription check**: Happens automatically when you visit a movie
- **Multiple devices**: Subscription works across all your sessions

---

## 📊 Testing Checklist

### ✅ Test Notifications
- [ ] Click bell icon - dropdown opens
- [ ] See unread count badge
- [ ] Mark notification as read - badge updates
- [ ] Delete notification - removed from list
- [ ] Click notification link - navigates correctly
- [ ] Wait 30 seconds - auto-refresh works

### ✅ Test Search
- [ ] Click search icon - dropdown opens
- [ ] Type text - results appear instantly
- [ ] Select genre - results filter
- [ ] Select year - results filter
- [ ] Combine filters - all work together
- [ ] Click result - navigates to movie
- [ ] Clear filters - resets everything

### ✅ Test Payments
- [ ] Visit profile - see subscription card
- [ ] Click "Subscribe Now" - demo mode activates
- [ ] See success message
- [ ] Page reloads - shows premium status
- [ ] Try premium movie - can watch now
- [ ] Check notifications - subscription confirmed

### ✅ Test Premium Access
- [ ] Mark a movie as premium (SQL)
- [ ] Visit movie as free user - see locked overlay
- [ ] Subscribe to premium
- [ ] Visit same movie - video plays
- [ ] See premium badge on title

---

## 🆘 Troubleshooting

### Notifications not showing?
1. Check if notifications table exists
2. Verify user_id in notifications matches your logged-in user
3. Check browser console for errors
4. Try refreshing the page

### Search not working?
1. Make sure you have movies in database
2. Check browser console for errors
3. Verify API endpoint is accessible
4. Try opening/closing the dropdown

### Payment fails?
1. It should work in demo mode automatically
2. Check browser console for error details
3. Verify transactions table exists
4. Check if user is logged in

### Can't watch premium movies after subscribing?
1. Check subscription_status in users table
2. Verify subscription_expiry is in the future
3. Clear browser cache and reload
4. Check browser console for errors

---

## 🎉 You're All Set!

Your MovieStream app now has:
- ✅ Working notifications with real-time updates
- ✅ Demo payment system for testing
- ✅ Advanced search with multiple filters
- ✅ Premium content protection
- ✅ Beautiful, modern UI

Enjoy your fully functional streaming platform! 🍿
