# Registered Students Management Guide

## 📋 Overview

The Registered Students Management system allows administrators to track and manage student information throughout their course lifecycle, including start/end dates, payment status, contact information, and personal notes.

## 🚀 Getting Started

### Step 1: Update Database

Before using the feature, you must update your database schema to add the required columns:

1. Navigate to: `https://yoursite.com/database/update_database.php`
2. This will automatically add:
   - `start_date` - Course start date
   - `end_date` - Course end date
   - `payment_status` - Payment status (Pending, Partial, Paid)
   - `amount_paid` - Amount received
   - `total_amount` - Total course cost
   - `phone` - Student phone number
   - `address` - Student address
   - `emergency_contact` - Emergency contact information
   - `notes` - Additional notes about the student

3. The status ENUM will be updated to include "Registered" status

### Step 2: Update Dashboard

Once the database is updated, the admin dashboard will show:
- A new "Registered" statistic card showing total registered students
- A link to "Manage Registered Students"
- The "Registered" option will appear in the status dropdown

## 📊 Accessing Registered Students

### From Dashboard:
1. Log in to the admin panel
2. Click "Manage Registered Students" button
3. Or navigate to: `/admin/registered_students.php`

## 👥 Student Management Features

### Overview Page

The registered students page displays:
- **Statistics Cards**: Total students, completed payments, pending payments, total revenue
- **Search Function**: Filter students by name or email
- **Payment Filter**: View students by payment status (All, Paid, Pending, Partial)
- **Student Cards**: Quick view of each student's information

### Student Card Information

Each student card displays:
- Student name
- Email address
- Course type
- Phone number
- Start date (formatted as DD-MM-YYYY)
- End date (formatted as DD-MM-YYYY)
- Time preference (Morning/Afternoon/Evening)
- Payment status badge with color coding:
  - 🟢 Green - Paid
  - 🟡 Yellow - Pending
  - 🔵 Blue - Partial Payment
- Edit and Delete buttons

### Editing Student Information

1. Click the **Edit** button on a student card
2. A modal dialog will open with all editable fields:

#### Required Fields:
- **Start Date**: When the student begins the course
- **End Date**: When the student completes the course

#### Payment Fields:
- **Payment Status**: Select Pending, Partial Payment, or Paid
- **Amount Paid (€)**: The amount received so far
- **Total Amount (€)**: The total course cost

#### Contact Information:
- **Phone Number**: Student's phone
- **Course Type**: Displays the course (read-only)
- **Address**: Student's address
- **Emergency Contact**: Emergency contact name/number

#### Additional Information:
- **Additional Notes**: Any important notes about the student (special requirements, attendance issues, etc.)

3. Click **Save Changes** to update the student information
4. A success message will appear

## 💳 Payment Tracking

### Payment Status Types

1. **Pending** (Yellow badge)
   - No payment received
   - Amount Paid = €0.00

2. **Partial** (Blue badge)
   - Some payment received but not complete
   - Amount Paid is less than Total Amount

3. **Paid** (Green badge)
   - Full payment received
   - Amount Paid equals Total Amount

### Monitoring Payments

- Use the payment filter dropdown to quickly see students by payment status
- The statistics card shows:
  - Number of complete payments
  - Number of pending payments
  - Total revenue collected

## 🔍 Search and Filter

### Search by Name/Email
- Type in the search box at the top
- Results update in real-time
- Searches through name and email fields

### Filter by Payment Status
- Use the dropdown filter
- Options: All, Paid, Pending, Partial
- Shows only students matching the selected status

## 📝 Student Lifecycle

### Creating a Registered Student

Option 1: Change status directly
1. From the dashboard, find a registration
2. Change status dropdown to "Registered"
3. The student will appear on the Registered Students page

Option 2: Set up from registration
1. Go to Dashboard
2. Find the new registration
3. Change status to "Registered"
4. Go to Registered Students page
5. Click Edit to add dates and payment info

### Typical Workflow

1. **New Registration** → Student submits registration form
2. **Pending** → Admin reviews registration
3. **Planned** → Admin schedules the course
4. **Scheduled** → Course date is confirmed
5. **Registered** → Student is officially enrolled
   - Set start and end dates
   - Set payment terms
   - Add personal information
6. **Completed** → Course has ended
7. **Cancelled** → If student drops out

## ⚠️ Important Notes

### Date Format
- All dates are displayed in DD-MM-YYYY format
- All dates are stored in YYYY-MM-DD format in the database
- Use the date picker for consistency

### Currency
- All amounts are in Euros (€)
- Use decimal format (e.g., 100.50)

### Backup Recommendations
- Regularly backup your database
- Keep records of payment receipts
- Document payment arrangements in notes field

### Data Privacy
- Ensure you comply with GDPR
- Keep student contact information confidential
- Don't share access credentials

## 🎯 Best Practices

1. **Always fill in start and end dates** for proper course tracking
2. **Keep notes updated** for important student information
3. **Update payment status promptly** after receiving payments
4. **Use search** to quickly find students before editing
5. **Export data regularly** for backup purposes

## 📱 Mobile Access

The interface is responsive and works on:
- Desktop computers
- Tablets
- Smartphones

On mobile devices:
- Swipe to see all student information
- Tap Edit button to open student modal
- Use filters to narrow down results

## 🚨 Troubleshooting

### Database columns not added
- Run `/database/update_database.php` again
- Check database permissions
- Verify MySQL connection

### Status dropdown not showing "Registered"
- The dashboard may need to be refreshed
- Database update may not have completed
- Clear browser cache and refresh

### Changes not saving
- Check that you have admin access
- Verify all required fields are filled
- Check browser console for errors

### Payment calculations wrong
- Verify amounts are entered correctly
- Check currency format
- Ensure decimal separators match locale

## 📞 Support

For issues or questions:
1. Check this guide
2. Review database update logs
3. Contact system administrator
4. Check browser console for error messages

## 🔄 Updates and Maintenance

The system will automatically:
- Add missing database columns on first access
- Update status ENUM if needed
- Maintain data integrity

Keep your system updated to ensure all features work properly.

---

**Last Updated**: 2024  
**Version**: 1.0  
**Compatible with**: NT2 Taalles International v2.0+
