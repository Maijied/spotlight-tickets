# How to Test "Spotlight Tickets" (SaaS Edition)

Your system is running locally! Here are the three best ways to test it.

## 1. Automated "One-Click" Test (Fastest)
I have created a script that simulates a user buying a ticket. It mimics a browser, fills the form, and checks if the ticket is generated.

**Run this command in your terminal:**
```bash
bash verify_flow.sh
```
**Expected Output:**
> "VERIFIED: Success page loaded with 'QR Code'."

---

## 2. Visual Browser Test (Best for Experience)
Since your server is running, you can open the site in your browser.

1.  **Open Homepage**: [http://localhost:8082/public/index.php](http://localhost:8082/public/index.php)
2.  **Select a Slot**: Click on any available show time.
3.  **Fill Form**: Enter any name/email (e.g., "Test User").
4.  **Click Buy**: Click "টিকেট সংগ্রহ করুন".
5.  **Result**: You will immediately land on the "Success" page with your Ticket & QR Code.

---

## 3. Dynamic Admin Test (SaaS Features)
Test the "Service" capabilities by managing events dynamically.

1.  **Login**: [http://localhost:8082/public/admin.php](http://localhost:8082/public/admin.php)
    *   **User**: `admin`
    *   **Pass**: `admin123`
2.  **Add a Slot**:
    *   Find "Add New Slot" at the bottom.
    *   Enter a Time (e.g., "Feb 14, 08:00 PM") and Location.
    *   Click "Add Show Slot".
3.  **Verify**:
    *   Go back to the [Homepage](http://localhost:8082/public/index.php).
    *   **Magic!** Your new slot is instantly available for booking.
