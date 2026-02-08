# Family Member Management System

## Overview
This system allows primary homeowners to manage family members within a single household. Each family member can have their own QR code for access control without needing email or phone number registration.

## Features

### For Homeowners
- Add unlimited family members to household
- Assign roles (Owner, Spouse, Child, Relative, Caregiver, etc.)
- Set individual access permissions per member
- Generate unique QR codes for each family member
- View, edit, disable, regenerate, or delete family member QR codes
- Track family member entry/exit activity

### Access Control Settings
Each family member can have:
- **Custom Access Hours**: Define specific time ranges (e.g., 6:00 AM - 10:00 PM)
- **Entry Point Restrictions**: Limit to specific gates (Entry Gate, Exit Gate)
- **QR Code Expiry**: Set expiration dates for temporary access
- **Status Control**: Active, Disabled, or Suspended

### Security Features
- Each QR code is unique and cryptographically secure
- QR codes can be regenerated instantly (old codes become invalid)
- All scans are logged with timestamp and location
- Access can be disabled without deleting the member
- Comprehensive audit trail for compliance

## Database Structure

### family_members Table
Stores all family member information:
- Basic info: full_name, role, date_of_birth
- QR credentials: qr_token, qr_code, qr_expiry
- Access control: allowed_entry_points, allowed_hours, access_status
- Tracking: current_status (IN/OUT), last_scan_time

### family_member_logs Table
Records all access events:
- Entry/Exit actions
- Timestamp and location
- Device/scanner information
- Guard verification (if applicable)

## Usage Guide

### Adding a Family Member
1. Navigate to Homeowners page
2. Click the **People icon** (Manage Family) for the homeowner
3. Click **Add Family Member**
4. Fill in required information:
   - Full Name
   - Role in family
   - Optional: Date of Birth, Access restrictions
5. Toggle **Auto-generate QR Code** (enabled by default)
6. Click **Save Member**

### Managing Access
**Enable/Disable Access:**
- Edit the family member
- Change Access Status to Active/Disabled/Suspended

**Set Time Restrictions:**
- Enter Allowed Hours Start and End times
- Leave empty for 24/7 access

**Restrict Entry Points:**
- Check specific gates allowed
- Leave all unchecked for access through any gate

### QR Code Management
**View QR Code:**
- Click the green QR icon
- Modal shows scannable code
- Download as PNG for distribution

**Regenerate QR:**
- Click the refresh icon
- Set new expiry date
- Old code becomes invalid immediately

**First-time Generation:**
- If QR not generated during creation
- Click the QR scan icon to generate

### Deleting Members
- Click the red trash icon
- Confirm deletion
- **Warning**: Deletes all associated logs permanently

## API Endpoints

### GET `/api/get_family_members.php`
Retrieve all family members for a homeowner
- **Parameter**: `homeowner_id`
- **Returns**: Array of family member objects

### POST `/api/add_family_member.php`
Add new family member
- **Parameters**: homeowner_id, full_name, role, [optional fields]
- **Returns**: Success status and new member ID

### POST `/api/update_family_member.php`
Update family member details
- **Parameters**: id, full_name, role, access_status, [optional fields]
- **Returns**: Success status

### POST `/api/delete_family_member.php`
Delete family member
- **Parameter**: id
- **Returns**: Success status

### POST `/api/generate_family_member_qr.php`
Generate/regenerate QR code
- **Parameters**: id, [optional: expiry_date]
- **Returns**: New QR token and expiry date

### POST `/api/toggle_family_member_access.php`
Change access status
- **Parameters**: id, status (active/disabled/suspended)
- **Returns**: Success status

## Technical Implementation

### Frontend Components
1. **familyManagementModal**: Main interface for viewing all family members
2. **addEditFamilyMemberModal**: Form for creating/editing members
3. **family_management.js**: All JavaScript logic and AJAX calls

### Backend Components
1. **Database Schema**: family_members and family_member_logs tables
2. **API Files**: 7 PHP endpoints for all operations
3. **Triggers**: Auto-update member status on log entry

### Security Considerations
- All QR tokens are generated using cryptographically secure random bytes
- SQL injection protection via prepared statements
- Foreign key constraints ensure data integrity
- Cascade delete prevents orphaned records
- Access logs retain audit trail even after member deletion

## Integration with Scanner System

When scanning QR codes:
1. Scanner reads QR token
2. System validates token exists and is not expired
3. Checks access_status (must be 'active')
4. Verifies current time is within allowed_hours (if set)
5. Confirms entry_point is in allowed_entry_points (if set)
6. Logs entry/exit action
7. Updates current_status (IN/OUT)
8. Records last_scan_time

## Best Practices

### For Administrators
- Regularly review and update family member lists
- Set reasonable QR expiry dates (recommend 1 year)
- Use access restrictions for temporary members (caregivers, etc.)
- Monitor activity logs for unusual patterns

### For Homeowners
- Keep QR codes secure (treat like keys)
- Report lost/stolen QR codes immediately for regeneration
- Update member information when roles change
- Delete members who no longer need access

## Future Enhancements
- Bulk import from CSV
- Mobile app for homeowners to self-manage
- Photo upload for family members
- Push notifications for family member access
- Statistical reporting (most active members, peak times)
- Temporary guest passes with auto-expiry
