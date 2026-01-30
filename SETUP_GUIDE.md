# Quick Setup Guide

## ⚡ Fast Setup (5 Minutes)

### Step 1: Start XAMPP
1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**

### Step 2: Import Database
1. Open browser and go to: **http://localhost/phpmyadmin**
2. Click **"Import"** tab
3. Click **"Choose File"** and select: `C:\xampp\htdocs\CiudadDeSanJose\database\schema.sql`
4. Click **"Go"** button at the bottom
5. Wait for success message

### Step 3: Login
1. Open browser and go to: **http://localhost/CiudadDeSanJose/auth/login.php**
2. Enter credentials:
   - **Username:** `admin`
   - **Password:** `admin123`
3. Click **Login**

### Step 4: Explore Dashboard
You should now see the admin dashboard with:
- ✅ Summary statistics cards
- ✅ Real-time activity log
- ✅ Homeowner status list
- ✅ Charts and analytics
- ✅ Scanner device status

## 🎯 What's Included

### Pre-loaded Sample Data
- **8 Sample Homeowners** (HO-001 to HO-008)
- **8 Entry/Exit Logs** (recent activities)
- **4 Scanner Devices** (3 online, 1 offline)
- **1 Admin User** (username: admin)

### Working Features
✅ Real-time activity monitoring
✅ Homeowner status tracking
✅ Entry/Exit statistics
✅ Interactive charts
✅ Search and filter
✅ Auto-refresh (every 30 seconds)
✅ Responsive design
✅ Dark theme

## 🔧 Troubleshooting

### "Database connection failed"
**Solution:** Make sure MySQL is running in XAMPP

### "Table doesn't exist"
**Solution:** Import the schema.sql file again

### "Access denied"
**Solution:** Check if you're using the correct login credentials

### Page not loading
**Solution:** Make sure Apache is running in XAMPP

## 📋 Next Steps

1. **Test the system** with sample data
2. **Add real homeowners** using the "+" button
3. **Configure scanner devices** in settings
4. **Set up guards/users** for access control
5. **Generate reports** for daily activities

## 🚀 Production Deployment

Before going live:
1. Change admin password
2. Update database credentials
3. Remove sample data
4. Configure backup schedule
5. Set up SSL certificate
6. Enable error logging

## 📞 Need Help?

Check the full **README.md** for detailed documentation.

---

**System Ready! 🎉**

Your QR Code Entry & Exit Monitoring System is now set up and ready to use!
