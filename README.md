# Ciudad de San Jose - QR Code Entry & Exit Monitoring System

## Admin Dashboard Features

### 📊 Summary Statistics
- **Total Homeowners**: Display total number of active homeowners
- **Currently Inside**: Real-time count of homeowners inside the subdivision
- **Currently Outside**: Real-time count of homeowners outside the subdivision
- **Total Entries Today**: Count of all entry scans for the current day
- **Total Exits Today**: Count of all exit scans for the current day
- **Total Scans Today**: Combined entry and exit scans
- **Active Scanners**: Number of online scanner devices
- **Active Guards**: Number of guards currently on duty

### 📋 Real-Time Activity Log
- Live table showing all entry/exit activities
- Color-coded status badges (Green for IN, Red for OUT)
- Displays: Homeowner Name, ID, Action, Date, Time, Scanner Device
- Auto-refreshes every 30 seconds
- Manual refresh button available
- Sortable and searchable using DataTables

### 👥 Homeowner Status List
- Complete list of all homeowners with current status
- Shows current location (INSIDE/OUTSIDE)
- Displays last scan time
- Filter buttons: All, Inside, Outside
- Quick actions: View details, Edit homeowner
- Real-time updates

### 📈 Analytics & Charts
- **Entry/Exit Chart**: Line chart showing entry and exit trends
- **Status Distribution**: Doughnut chart showing inside vs outside ratio
- Period selector: Last 7, 30, or 90 days
- Interactive and responsive charts using Chart.js

### 🔍 Search & Filter Features
- Global search across all homeowners
- Filter by name, homeowner ID, date, and IN/OUT status
- Advanced filtering on all data tables
- Real-time search results

### 📄 Reports Module (Framework Ready)
- Daily, weekly, and monthly reports
- Custom date range selection
- Export to PDF and Excel formats
- Report generation tracking

### 🏠 Homeowner Management
- Add new homeowners
- Edit homeowner information
- Deactivate/suspend accounts
- Automatic QR code generation
- View homeowner details and history

### 👮 Guard/User Management
- User role management (Admin, Guard, Supervisor)
- Track guard activities
- Login history
- User status management

### 📱 Scanner Device Monitoring
- Real-time device status (Online/Offline)
- Last activity tracking
- Device location information
- Visual status indicators

### 📝 Audit Log
- Track all system actions
- User activity monitoring
- Change history tracking
- Security and compliance

### 🎨 Design Features
- Modern, premium dark theme
- Responsive design (mobile, tablet, desktop)
- Smooth animations and transitions
- Color-coded status indicators
- Interactive hover effects
- Professional typography (Inter & Outfit fonts)
- Glassmorphism effects
- Real-time data updates

## 🚀 Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser (Chrome, Firefox, Edge, etc.)

### Installation Steps

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Click "Import" tab
   - Select the file: `database/schema.sql`
   - Click "Go" to execute

   OR use MySQL command line:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Configure Database Connection**
   - File is already created at: `config/database.php`
   - Default settings:
     - Host: localhost
     - Database: ciudad_de_san_jose
     - Username: root
     - Password: (empty)
   - Modify if your settings are different

4. **Access the System**
   - Login page: http://localhost/CiudadDeSanJose/auth/login.php
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`

5. **Test the Dashboard**
   - After login, you'll be redirected to the dashboard
   - Sample data is pre-loaded for testing
   - All features are functional with sample data

## 📁 File Structure

```
CiudadDeSanJose/
├── admin/
│   ├── dashboard.php          # Main dashboard page
│   ├── dashboard.css          # Dashboard styles
│   ├── dashboard.js           # Dashboard JavaScript
│   └── api/
│       ├── get_activity_log.php      # Activity log API
│       ├── get_homeowner_status.php  # Homeowner status API
│       ├── get_stats.php             # Statistics API
│       └── get_chart_data.php        # Chart data API
├── auth/
│   └── login.php              # Login page
├── config/
│   └── database.php           # Database configuration
└── database/
    └── schema.sql             # Database schema and sample data
```

## 🔄 How the System Works

### Automatic Status Toggling
1. When a homeowner scans their QR code at entry/exit
2. System logs the action in `entry_logs` table
3. Database trigger automatically updates homeowner's `current_status`
4. Status toggles between 'IN' and 'OUT'
5. `last_scan_time` is updated with current timestamp
6. Dashboard reflects changes in real-time

### Data Flow
```
QR Scan → Entry Log Created → Trigger Fires → Status Updated → Dashboard Refreshes
```

## 🛠️ API Endpoints

### GET /admin/api/get_activity_log.php
Returns recent entry/exit activities
```json
[
  {
    "homeowner_name": "Juan Dela Cruz",
    "homeowner_id": "HO-001",
    "action": "IN",
    "date": "2026-01-30",
    "time": "10:30:00",
    "device": "Main Gate Scanner"
  }
]
```

### GET /admin/api/get_homeowner_status.php
Returns all homeowners with current status
```json
[
  {
    "id": "1",
    "name": "Juan Dela Cruz",
    "homeowner_id": "HO-001",
    "current_status": "IN",
    "last_scan_time": "2026-01-30 10:30:00"
  }
]
```

### GET /admin/api/get_stats.php
Returns dashboard statistics
```json
{
  "total_homeowners": 150,
  "currently_inside": 98,
  "currently_outside": 52,
  "total_entries_today": 87,
  "total_exits_today": 76,
  "total_scans_today": 163
}
```

### GET /admin/api/get_chart_data.php?period=30
Returns chart data for specified period
```json
{
  "labels": ["Jan 01", "Jan 02", "Jan 03"],
  "entries": [45, 52, 48],
  "exits": [42, 49, 51]
}
```

## 🔐 Security Features
- Session-based authentication
- Password hashing (bcrypt)
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- Role-based access control
- Audit logging

## 📱 Responsive Design
- Mobile-first approach
- Breakpoints: 576px, 768px, 992px, 1200px
- Touch-friendly interface
- Optimized for all screen sizes

## 🎯 Future Enhancements
- Real-time WebSocket updates
- Push notifications
- Mobile app integration
- Facial recognition
- Visitor management
- Vehicle tracking
- SMS/Email alerts
- Advanced analytics
- Multi-language support

## 🐛 Troubleshooting

### Database Connection Error
- Verify XAMPP MySQL is running
- Check database credentials in `config/database.php`
- Ensure database `ciudad_de_san_jose` exists

### Tables Not Found
- Import `database/schema.sql` via phpMyAdmin
- Check if all tables are created

### No Data Showing
- Sample data is included in schema.sql
- Check if data was imported successfully
- API endpoints have fallback sample data

### Permission Denied
- Ensure proper file permissions
- Check XAMPP installation directory permissions

## 📞 Support
For issues or questions, please refer to the documentation or contact the system administrator.

## 📄 License
Proprietary - Ciudad de San Jose Subdivision Management System
