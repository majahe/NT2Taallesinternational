# Registered Students Feature - Quick Setup Guide

## ⚡ Quick Setup (3 Steps)

### Step 1: Run Database Update
```
1. Open your browser
2. Navigate to: https://yoursite.com/database/update_database.php
3. Wait for the page to show: "Database update completed!"
```

✅ This automatically adds all required database columns and updates the status options.

### Step 2: Verify Dashboard Update
```
1. Go to: https://yoursite.com/admin/dashboard.php
2. Look for new "Registered" statistic card
3. Look for new "Manage Registered Students" button
```

✅ The dashboard now includes the new Registered status option.

### Step 3: Start Using It!
```
1. Go to Dashboard
2. Change any registration status to "Registered"
3. Click "Manage Registered Students" button
4. Edit the student to add dates and payment info
```

✅ You're all set! Start managing your registered students.

---

## 📋 What Gets Added?

### Database Changes:
- ✅ `start_date` - When course starts
- ✅ `end_date` - When course ends
- ✅ `payment_status` - Pending/Partial/Paid
- ✅ `amount_paid` - Amount received
- ✅ `total_amount` - Total course cost
- ✅ `phone` - Student phone
- ✅ `address` - Student address
- ✅ `emergency_contact` - Emergency contact
- ✅ `notes` - Admin notes

### New Status:
- ✅ "Registered" added to status dropdown

### New Page:
- ✅ `/admin/registered_students.php` - Full student management interface

---

## 🎯 Typical Usage Flow

```
Registration Form Submitted
        ↓
    Dashboard (Status: New)
        ↓
    Change to: Pending/Planned/Scheduled
        ↓
    Change to: Registered
        ↓
    Click "Manage Registered Students"
        ↓
    Edit Student:
        • Set Start Date
        • Set End Date
        • Add Phone
        • Add Address
        • Set Payment Terms
        • Add Notes
        ↓
    Track Payment Status:
        • Pending → Partial → Paid
        ↓
    Course Completion:
        • Change status to: Completed
```

---

## 💡 Key Features

### Student Cards Display:
- Name, Email, Course
- Phone, Start/End Dates
- Payment Status with Color Coding
- Quick Edit & Delete Buttons

### Search & Filter:
- Search by name or email
- Filter by payment status (Paid, Pending, Partial)

### Payment Tracking:
- Monitor total revenue
- See payment breakdown
- Track pending payments

### Statistics:
- Total registered students
- Completed payments count
- Pending payments count
- Total revenue collected

---

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| "Registered" option not showing | Run database update script |
| Can't see new page | Clear browser cache, refresh |
| Dates not saving | Ensure dates are properly formatted |
| Can't access page | Check admin login |

---

## 📞 Need Help?

1. Read full guide: `Guide/Registered-Students-Guide.md`
2. Check database update log
3. Review page errors in browser console
4. Contact system administrator

---

## ✨ You're Ready!

The registered students management system is now active and ready to use. Start organizing your students today!

**Questions?** Review the detailed guide for more information on each feature.
