# Admin Dashboard Guide - Siddhartha Live 2026

This documentation covers how to manage the **Spotlight Tickets** system using the admin dashboard.

## Accessing the Dashboard
- **URL**: [https://shiddarth.xo.je/public/admin.php](https://shiddarth.xo.je/public/admin.php)
- **Default Credentials**: 
  - Username: `admin`
  - Password: `admin123`

---

## Step-by-Step Workflow

### 1. Overview (Dashboard)
The landing page provides a real-time snapshot of the event's performance:
- **Total Revenue**: Cumulative sales amount.
- **Today's Sales**: Sales made in the last 24 hours.
- **Pending Actions**: Number of bookings waiting for your approval.
- **Capacity Bars**: Visual representation of how full each show slot is.

### 2. Processing Payments (Pending Approval)
**Priority Task**: Check this section daily. It lists customers who have submitted a bKash transaction ID but haven't been confirmed yet.
1. Click **Pending Approval** in the sidebar.
2. Verify the `Transaction ID` and `Amount` against your bKash merchant/personal statement.
3. If valid, click the **Approve** button.
   - The user will move to the "Confirmed" list.
   - (Optional) You can automate SMS confirmation if an SMS gateway is connected.

### 3. Managing Events & Slots
Configure your show times and pricing here.
- **Event Name**: Updates the main title on the public ticket page.
- **Add New Slot**:
  - Enter Date/Time (e.g., `25 Jan 2026 18:30`).
  - Set Location (e.g., `National Theatre`).
  - Set Capacity & Price for all 3 tiers (Regular, VIP, Front Row).
- **Delete Slot**: Removes a show time. *Note: Data for bookings made for this slot is preserved in the database but won't show in slot statistics.*

### 4. Managing Bookings
Search and filter through the entire database of ticket sales.
- **Search**: Type a name, phone number, or Transaction ID to instantly filter the list.
- **Export CSV**: Download a complete spreadsheet of all bookings for Excel/Sheets.
- **Check-In**: You can manually check-in a user from this list if you are not using the QR scanner.

### 5. QR Scanner (Venue Entry)
Use this tool at the venue gate to validate tickets.
1. Open **QR Scanner** from the sidebar on a mobile device.
2. Grant camera permissions.
3. Scan the QR code on the customer's e-ticket.
4. System will show:
   - ✅ **Valid**: Green screen, shows seat details.
   - ❌ **Invalid**: Red screen.
   - ⚠️ **Already Used**: Yellow screen (ticket scanned previously).

### 6. Managing Admins
Control who has access to this dashboard.
- **Add Admin**: Create new accounts for staff members.
- **Delete**: Remove access for former staff.
- *Note: You cannot delete your own account while logged in.*

---

## Troubleshooting

**Q: A user says they paid but didn't get a ticket.**
A: Check the **Pending Approval** list. If they entered a typo in the transaction ID, search for their phone number in **All Bookings**. You can manually edit or approve it.

**Q: How do I change the bKash number shown to users?**
A: This is set in the `.env` file on the server (or `config.php`). It cannot be changed from the admin panel for security reasons. Contact your developer.
