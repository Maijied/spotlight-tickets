# FlexPayBD Ticket Selling System

A simple, production-ready PHP system for selling event tickets using the FlexPayBD payment gateway.

## Features
- Modern UI for ticket booking.
- Integration with FlexPayBD hosted payment page.
- Server-side payment verification for security.
- Email confirmation upon successful payment.
- Configurable event name and ticket price.

## Project Structure
- `/api/`: Backend handlers for payment creation.
- `/config/`: Configuration files (API keys, event info).
- `/includes/`: Reusable classes for FlexPay API and Mailer.
- `/public/`: Frontend pages (Form, Success, Cancel).
- `callback.php`: The main entry point for post-payment logic.

## Setup Instructions

### 1. Configure API Keys
Open `config/config.php` and fill in your credentials from the FlexPayBD Merchant Dashboard:
```php
define('FLEXPAY_API_KEY', 'YOUR_API_KEY_HERE');
define('FLEXPAY_SECRET_KEY', 'YOUR_SECRET_KEY_HERE');
define('FLEXPAY_BRAND_KEY', 'YOUR_BRAND_KEY_HERE');
```

### 2. Local Environment
- Ensure you have **PHP 7.4+** installed.
- Ensure the **cURL** extension is enabled in `php.ini`.
- Place the project in your web server root (e.g., `htdocs` for XAMPP).
- Update `BASE_URL` in `config/config.php` to match your local path.

### 3. Verification & Testing
- Use the sandbox credentials provided by FlexPayBD docs.
- Test the full flow: Form -> Payment Page -> Success Page.
- Check if the confirmation email is triggered (requires a working SMTP server or local mail hog).

### 4. Switch to Production
- Change the API keys to Live keys.
- Ensure `BASE_URL` uses `https`.
- Set `error_reporting(0)` in `config/config.php`.

## Common Mistakes to Avoid
- **Security**: Never expose `SECRET_KEY` or `API_KEY` in frontend JS. Keep them in `config.php`.
- **Verification**: Always call the `verify` API on the callback. Do not trust the redirect parameters alone.
- **URLs**: Ensure `SUCCESS_URL` and `CANCEL_URL` are publicly accessible for FlexPayBD's server to redirect properly.

## Expansion Ideas (TODOs in Code)
- **Database**: Add `tickets` table to store customer data and payment status.
- **SMS**: Integrate providers like Twilio or local BD SMS gateways.
- **Tickets**: Use libraries like `TCPDF` or `dompdf` to generate downloadable tickets with QR codes.
