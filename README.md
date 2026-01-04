# Spotlight Tickets - Theatrical Booking System 

A premium, production-ready ticket booking system designed for theatrical performances and drama shows. This system features a rich, antique dramatic UI, secure payment integration via FlexPayBD, and a robust multi-user administrative backend.

## 🎭 Visual Showcase

### Landing Page
Theatrical design with gold accents and Bengali typography.
![Landing Page](screenshots/landing_page.png)

### Booking Success & Digital Ticket
The success page generates a working QR code (via QRServer API) for entry verification and includes a professional print-ready ticket layout.
![Success Page with QR and Print](screenshots/success_page_qr.png)

### Admin Dashboard
Comprehensive overview of sales, revenue, and recent bookings.
![Admin Dashboard](screenshots/admin_dashboard_final.png)

### Multi-User Management
Secure session-based admin system with the ability to manage multiple administrative accounts.
![Admin User Management](screenshots/admin_management.png)

## ✨ Core Features
- **Premium UI/UX**: Theatrical purple and gold theme with custom typography (Playfair Display & Hind Siliguri).
- **Dynamic Pricing**: 
    - Early Bird Discounts (Date-based).
    - Bundle Discounts (Quantity-based).
    - Promo Code support.
- **Secure Payments**: Integrated with **FlexPayBD** Hosted Payment Page.
- **Digital Tickets**: Auto-generated QR codes accessible on the success page and via email.
- **Admin Control**: Robust dashboard for sales tracking and administrative user management.
- **Persistence**: Lightweight JSON-based storage for bookings and administrative credentials.

## 🛠️ Tech Stack
- **Frontend**: Vanilla HTML5, CSS3 (Custom Theatrical Design).
- **Backend**: Native PHP 7.4+.
- **Database**: JSON Storage (Bookings & Admins).
- **External APIs**: 
    - **Internal Test Gateway** (Simulated Payments).
    - [QRServer](https://goqr.me/api/) (QR Generation).
    - [Google Fonts](https://fonts.google.com/).

## 🚀 Setup & Installation

### 1. Configuration
The system is pre-configured with the `LocalGateway` for testing. No external API keys are required.


### 2. Deployment
To enable automatic deployment, follow these exact steps on your GitHub repository:

1. Go to **Settings > Secrets and variables > Actions**.
2. Click **New repository secret** for each of these:
   - **`FTP_SERVER`**: Your FTP host (e.g., `ftpupload.net`)
   - **`FTP_USERNAME`**: Your FTP username (e.g., `if0_xxxx`)
   - **`FTP_PASSWORD`**: Your FTP password (vPanel Password)

Once these are added, every push to the `main` branch will automatically update your site.

3. **Permissions**: Ensure the root directory and files like `bookings.json` have write permissions (CHMOD 777 or 666) on your hosting control panel.

### 3. Admin Access
Standard login path: `public/admin.php`
- **Default Username**: `admin`
- **Default Password**: `admin123`

## 🔒 Security Measures
- **Password Hashing**: Administrative passwords are securely hashed using `PASSWORD_DEFAULT`.
- **Session Security**: Protected admin routes with session-based authentication.
- **Data Protection**: `.gitignore` configured to prevent sensitive data (`admins.json`, etc.) from being pushed to public repositories.
- **Access Restrictions**: `.htaccess` implemented to block direct web access to JSON, SQL, and configuration files.

## 📜 License
This project is for demonstration and production use in theatrical events. All rights reserved.
