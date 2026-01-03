# Spotlight Tickets - Theatrical Booking System

A premium, production-ready ticket booking system designed for theatrical performances and drama shows. This system features a rich, antique dramatic UI, secure payment integration via FlexPayBD, and a robust multi-user administrative backend.

## 🎭 Visual Showcase

### Booking Success & Digital Ticket
The success page generates a working QR code (via QRServer API) for entry verification and includes a professional print-ready ticket layout.
![Success Page with QR and Print](screenshots/success_page_qr.png)

### Admin Dashboard
Comprehensive overview of sales, revenue, and recent bookings.
![Admin Dashboard](screenshots/admin_dashboard.png)

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
    - [FlexPayBD](https://flexpaybd.com/) (Payments).
    - [QRServer](https://goqr.me/api/) (QR Generation).
    - [Google Fonts](https://fonts.google.com/).

## 🚀 Setup & Installation

### 1. Configuration
Update `config/config.php` with your FlexPayBD credentials:
```php
define('FLEXPAY_API_KEY', 'YOUR_API_KEY');
define('FLEXPAY_SECRET_KEY', 'YOUR_SECRET_KEY');
define('FLEXPAY_BRAND_KEY', 'YOUR_BRAND_KEY');
```

### 2. Deployment
1. Upload the files to your server.
2. Ensure the root directory and `bookings.json`, `admins.json`, and `sms_log.txt` have appropriate write permissions for the web server.
3. Access the public interface via `index.php`.

### 3. Admin Access
Standard login path: `public/admin.php`
- **Default Username**: `admin`
- **Default Password**: `admin123`

## 🔒 Security Measures
- **Password Hashing**: Administrative passwords are securely hashed using `PASSWORD_DEFAULT`.
- **Session Security**: Protected admin routes with session-based authentication.
- **Data Protection**: `.gitignore` configured to prevent sensitive data (`admins.json`, etc.) from being pushed to public repositories.

## 📜 License
This project is for demonstration and production use in theatrical events. All rights reserved.
