# User Monitoring System

## Overview
Fitur monitoring users memungkinkan admin untuk memantau aktivitas user secara real-time, termasuk status online/offline, device yang digunakan, browser, platform, IP address, dan lokasi.

## Features

### 1. Real-time Monitoring
- Auto-refresh setiap 30 detik
- Manual refresh button
- Live online user count
- Status indicator (Online/Offline)

### 2. User Information
- User name, email, dan role
- Device type (Desktop, Mobile, Tablet)
- Browser detection (Chrome, Firefox, Safari, Edge, dll)
- Platform detection (Windows, MacOS, Linux, Android, iOS)
- IP Address
- Location (City, Country) - requires GeoIP setup

### 3. Session Tracking
- Current session details
- Recent sessions history (last 10)
- Last activity timestamp
- Session duration

### 4. DataTables Features
- Server-side processing
- Search functionality
- Pagination
- Sorting by last activity
- Responsive design

## Files Created

### Backend
1. **Migration**: `database/migrations/2025_10_30_231825_create_user_sessions_table.php`
   - Creates `user_sessions` table
   - Tracks: user_id, session_id, ip_address, user_agent, device_type, browser, platform, country, city, last_activity, is_online

2. **Model**: `app/Models/UserSession.php`
   - Relations: belongsTo User
   - Methods: 
     - `updateOrCreateSession()` - Update or create session
     - `parseUserAgent()` - Parse device info from user agent
     - `isStillOnline()` - Check if user still online (5 min threshold)
     - `markOffline()` - Mark session as offline
   - Scopes: `online()`, `forUser()`
   - Attributes: device_icon, browser_icon, platform_icon, last_activity_human

3. **Middleware**: `app/Http/Middleware/TrackUserActivity.php`
   - Auto-track user activity on every request
   - Update last_activity timestamp
   - Update device and location info

4. **Controller**: `app/Http/Controllers/Admin/UserMonitoringController.php`
   - Methods:
     - `index()` - Display monitoring page with DataTables
     - `show($userId)` - Get user session details (JSON)
     - `getOnlineCount()` - Get current online users count (JSON)
     - `forceLogout($userId)` - Force logout user session

### Frontend
1. **View**: `resources/views/admin/monitoring/index.blade.php`
   - Statistics cards (Online, Total Users, Total Sessions)
   - DataTables with real-time updates
   - Detail modal for viewing session history
   - Auto-refresh countdown
   - Device/Browser/Platform icons

### Configuration
1. **Routes**: `routes/web.php`
   - 4 new routes under `/admin/monitoring/` prefix

2. **Sidebar Menu**: `config/adminlte.php`
   - Added "Monitoring Users" under PENGATURAN section

3. **Middleware Registration**: `bootstrap/app.php`
   - Registered `TrackUserActivity` middleware globally

## Dependencies

### PHP Packages
- `jenssegers/agent` - User agent parser for device detection (already installed)
- `yajra/laravel-datatables-oracle` - DataTables server-side (already installed)

### Frontend Libraries
- DataTables
- SweetAlert2
- FontAwesome (for icons)

## Database Schema

```sql
CREATE TABLE user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    device_type VARCHAR(50) NULL,
    browser VARCHAR(50) NULL,
    platform VARCHAR(50) NULL,
    country VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    last_activity TIMESTAMP NULL,
    is_online BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_online (is_online),
    INDEX idx_last_activity (last_activity)
);
```

## How It Works

### 1. Activity Tracking
- `TrackUserActivity` middleware runs on every request
- Calls `UserSession::updateOrCreateSession()` for authenticated users
- Updates `last_activity` timestamp and device info
- Sets `is_online = true`

### 2. Online Status Detection
- User considered **online** if:
  - `is_online = true` AND
  - `last_activity` within last 5 minutes
- Checked by `isStillOnline()` method

### 3. Device Detection
- Uses `jenssegers/agent` library
- Parses `user_agent` string to detect:
  - Device type (desktop/mobile/tablet)
  - Browser name and version
  - Operating system (platform)

### 4. Real-time Updates
- DataTables auto-refresh every 30 seconds
- Online count updates via AJAX
- Manual refresh button available
- Countdown timer shows next refresh

## Usage

### Access Monitoring Page
1. Login as Admin
2. Navigate to: **Pengaturan > Monitoring Users**
3. Or direct URL: `/admin/monitoring/users`

### View User Details
1. Click "Detail" button on any user row
2. Modal shows:
   - Current session info
   - Recent 10 sessions history
   - Device and location details

### Interpreting Status
- **Green badge "Online"**: Active within last 5 minutes
- **Gray badge "Offline"**: No activity in last 5+ minutes

## Configuration Options

### Change Online Threshold
Edit `UserSession.php`:
```php
// Default: 5 minutes
public function scopeOnline($query)
{
    return $query->where('is_online', true)
                ->where('last_activity', '>=', Carbon::now()->subMinutes(5));
}

// Change to 10 minutes:
->where('last_activity', '>=', Carbon::now()->subMinutes(10));
```

### Change Auto-refresh Interval
Edit `resources/views/admin/monitoring/index.blade.php`:
```javascript
// Default: 30 seconds
let countdown = 30;

// Change to 60 seconds:
let countdown = 60;
```

### Disable Activity Tracking
Remove middleware from `bootstrap/app.php`:
```php
// Comment this line:
// $middleware->append(\App\Http\Middleware\TrackUserActivity::class);
```

## Testing Checklist

- [ ] Admin dapat mengakses halaman monitoring
- [ ] DataTables load dengan data users
- [ ] Status online/offline akurat
- [ ] Device, browser, platform terdeteksi
- [ ] IP address tercatat
- [ ] Last activity timestamp correct
- [ ] Auto-refresh berjalan setiap 30 detik
- [ ] Manual refresh button works
- [ ] Detail modal shows correct data
- [ ] Recent sessions history visible
- [ ] Online count updates real-time
- [ ] Mobile responsive design
- [ ] No console errors

## Future Enhancements

1. **GeoIP Integration**
   - Setup Torann/GeoIP for accurate location
   - Add country and city detection

2. **Force Logout Feature**
   - Implement `forceLogout()` method
   - Add button in detail modal

3. **Session Analytics**
   - Peak online hours chart
   - User activity heatmap
   - Device/Browser statistics

4. **Alerts & Notifications**
   - Alert when suspicious login detected
   - Notify admin of simultaneous logins
   - Track failed login attempts

5. **Export Reports**
   - Export activity logs to Excel/PDF
   - Generate usage statistics reports

## Troubleshooting

### Issue: All users show as offline
**Cause**: Middleware not registered or not running
**Solution**: Check `bootstrap/app.php` has `TrackUserActivity` appended

### Issue: Device info shows as null
**Cause**: `jenssegers/agent` package not installed
**Solution**: Run `composer require jenssegers/agent`

### Issue: DataTables not loading
**Cause**: Route not accessible or permission issue
**Solution**: Check route permissions and user role

### Issue: Location always "Unknown"
**Cause**: GeoIP not configured
**Solution**: Configure `torann/geoip` package (already installed)

## API Endpoints

### GET /admin/monitoring/users
Returns DataTables JSON data for all users

### GET /admin/monitoring/users/{userId}
Returns JSON with user session details:
```json
{
    "user": {
        "name": "John Doe",
        "email": "john@example.com",
        "role": "Admin"
    },
    "current_session": {
        "is_online": true,
        "device_type": "desktop",
        "browser": "Chrome",
        "platform": "Windows",
        "ip_address": "192.168.1.1",
        "location": "Jakarta, Indonesia",
        "last_activity": "31 Oct 2025 23:45:00",
        "last_activity_human": "2 minutes ago"
    },
    "recent_sessions": [...]
}
```

### GET /admin/monitoring/online-count
Returns current online users count:
```json
{
    "online_count": 15
}
```

### POST /admin/monitoring/users/{userId}/force-logout
Force logout user (marks session as offline):
```json
{
    "success": true,
    "message": "User berhasil di-logout"
}
```

## Security Considerations

1. **Permission Check**: Only users with `admin-access` can view monitoring
2. **Session Isolation**: Users can only see their own sessions (except admin)
3. **Data Privacy**: IP addresses and locations are sensitive data
4. **GDPR Compliance**: Consider data retention policies
5. **Audit Log**: Track who views monitoring data

## Performance Notes

- Uses indexed columns for fast queries
- Server-side DataTables for large datasets
- Auto-cleanup old sessions recommended (add scheduled task)
- Consider pagination for session history

## License
Part of SIMANSA v3 - Internal Use Only
