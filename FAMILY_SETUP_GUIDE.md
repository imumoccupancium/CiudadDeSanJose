# Family Member Management System - Quick Start Guide

## ✅ What Has Been Created

### 1. Database Schema
- `database/family_members_schema.sql` - Complete database structure
- `database/migrate_family_members.php` - CLI migration script
-  `database/migrate.html` - Web-based migration interface ⭐ **USE THIS**
- `database/run_migration.php` - Migration backend

### 2. API Endpoints (7 files in `admin/api/`)
- `get_family_members.php` - Retrieve family members
- `add_family_member.php` - Add new member
- `update_family_member.php` - Update member details
- `delete_family_member.php` - Remove member
- `generate_family_member_qr.php` - Generate/regenerate QR codes
- `toggle_family_member_access.php` - Enable/disable access

### 3. Frontend Components
- `admin/includes/family_modals.php` - UI modals
- `admin/js/family_management.js` - JavaScript logic
- Updated `admin/homeowners.php` with family management button

### 4. Documentation
- `docs/FAMILY_MANAGEMENT.md` - Complete feature documentation

## 🚀 Installation Steps

### Step 1: Run Database Migration

**Option A: Web Interface** (Recommended)
1. Open your browser
2. Navigate to: `http://localhost/CiudadDeSanJose/database/migrate.html`
3. Click "Run Migration"
4. Wait for success message

**Option B: Direct SQL** (Alternative)
1. Open phpMyAdmin
2. Select your database (`ciudad_san_jose`)
3. Go to SQL tab
4. Copy and paste contents from `database/family_members_schema.sql`
5. Click "Go"

### Step 2: Verify Installation
After migration, verify these tables exist:
- ✅ `family_members` - Stores family member data
- ✅ `family_member_logs` - Stores access logs
- ✅ Trigger: `after_family_member_log_insert`

### Step 3: Test the Feature
1. Go to admin panel: `http://localhost/CiudadDeSanJose/admin/homeowners.php`
2. Find any homeowner in the table
3. Click the **blue people icon** (🧑‍🤝‍🧑) in the Actions column
4. Family Management modal should open
5. Click "Add Family Member"
6. Fill in the form and save

## 📋 Features Overview

### What You Can Do
- ✅ Add unlimited family members per household
- ✅ Assign roles: Owner, Spouse, Child, Relative, Caregiver, Other
- ✅ Generate individual QR codes for each member
- ✅ Set custom access hours (e.g., 6AM - 10PM)
- ✅ Restrict entry points (Main Gate, Back Gate, etc.)
- ✅ Set QR code expiry dates
- ✅ View, edit, disable, or delete members
- ✅ Regenerate QR codes anytime
- ✅ Download QR codes as PNG images
- ✅ Track entry/exit logs per member

### Access Control Options
Each family member can have:
- **Time Restrictions**: Only allow access during specific hours
- **Gate Restrictions**: Limit to specific entry points
- **Status Control**: Active / Disabled / Suspended
- **QR Expiry**: Auto-expire codes after set date

## 🎯 Usage Examples

### Example 1: Adding a Child
```
Full Name: Maria Dela Cruz
Role: Child
Access Status: Active
Allowed Hours: 6:00 AM to 9:00 PM
Entry Points: Main Gate, Pedestrian Gate
QR Expiry: 1 year from now
```

### Example 2: Temporary Caregiver
```
Full Name: Elena Santos
Role: Caregiver
Access Status: Active
Allowed Hours: 8:00 AM to 6:00 PM
Entry Points: Service Gate only
QR Expiry: 3 months from now
```

### Example 3: Adult Family Member (24/7)
```
Full Name: Juan Dela Cruz Jr.
Role: Owner
Access Status: Active
Allowed Hours: (leave empty for 24/7)
Entry Points: (leave all unchecked for all gates)
QR Expiry: 1 year from now
```

## 🔧 Troubleshooting

### Migration Failed
- Ensure XAMPP MySQL is running
- Check database connection in `config/database.php`
- Verify database name is correct
- Check PHP error logs

### Modal Not Opening
1. Check browser console for JavaScript errors
2. Verify jQuery is loaded (should be in homeowners.php)
3. Clear browser cache
4. Ensure `js/family_management.js` is accessible

### QR Code Not Generating
1. Verify QRCode.js library is loaded
2. Check API endpoint response in Network tab
3. Ensure database tables exist
4. Verify PHP has write permissions

### Family Members Not Loading
1. Open browser developer tools → Network tab
2. Click the family icon
3. Check the request to `api/get_family_members.php`
4. Verify response is valid JSON
5. Check for PHP errors in response

## 📱 Scanner Integration

When implementing the scanner, validate:
1. QR token exists in database
2. `qr_expiry` hasn't passed
3. `access_status` is 'active'
4. Current time is within `allowed_hours` (if set)
5. Current gate is in `allowed_entry_points` (if set)
6. Log entry to `family_member_logs`
7. Update `current_status` to IN/OUT

## 🎨 UI Features

### Family Member Cards Show:
- Name and role
- QR code status (has code or not)
- Access status badge (Active/Disabled/Suspended)
- Current location (INSIDE/OUTSIDE)
- Access hours or "24/7 Access"
- QR expiry date

### Action Buttons:
- 📝 **Edit** - Update member details
- ✅ **View QR** - Display scannable code
- 🔄 **Regenerate QR** - Create new code
- 🆕 **Generate QR** - First-time generation
- 🗑️ **Delete** - Remove member

## 📊 Database Structure

### family_members Table
- Basic info: id, homeowner_id, full_name, role, date_of_birth
- QR data: qr_code, qr_token, qr_expiry, qr_last_generated
- Access control: allowed_entry_points (JSON), allowed_hours_start, allowed_hours_end
- Status: access_status, current_status (IN/OUT)
- Tracking: last_scan_time, created_at, updated_at

### family_member_logs Table
- Entry data: id, family_member_id, homeowner_id, action (IN/OUT)
- Metadata: timestamp, device_name, device_id, entry_point
- Verification: guard_id, notes

## 🔐 Security Notes

- QR tokens use cryptographically secure random generation
- All database queries use prepared statements (SQL injection safe)
- Cascade deletes prevent orphaned records
- Access logs retained even after member deletion
- QR regeneration instantly invalidates old codes

## 🌟 Next Steps

After installation:
1. Test adding a family member
2. Generate and download a QR code
3. Test editing access permissions
4. Try the regenerate QR function
5. Practice deleting a member

## 📞 Support

For issues or questions:
1. Check `docs/FAMILY_MANAGEMENT.md` for detailed documentation
2. Review browser console for JavaScript errors
3. Check PHP error logs
4. Verify all files are in correct locations

## ✨ Feature Highlights

**No Email/Phone Required** - Just name and role
**Individual QR Codes** - Unique code per family member
**Flexible Permissions** - Time and location restrictions
**Easy Management** - Add, edit, delete from one interface
**Full Audit Trail** - Complete access history per member
**Instant Updates** - Changes take effect immediately
**Visual Interface** - Card-based layout with status indicators
**Mobile Friendly** - Responsive design works on all devices

---

**That's it! Your Family Member Management System is ready to use.** 🎉

Navigate to the Homeowners page and click the people icon to get started!
