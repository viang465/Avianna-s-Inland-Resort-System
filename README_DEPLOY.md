# Avianna's Inland Resort — Deployment Guide

## Quick Start (XAMPP / cPanel / Shared Hosting)

### 1. Upload Files
Upload the entire `avianna_resort/` folder to your server:
- **XAMPP**: `C:\xampp\htdocs\avianna_resort\`
- **cPanel**: `public_html/` (or a subdirectory)

### 2. Set Up the Database
1. Open **phpMyAdmin**
2. Create a new database named `avianna_resort`
3. Import `avianna_resort.sql` (File → Import)

### 3. Configure Database Connection
Edit `conn.php`:
```php
$servername = "localhost";   // usually localhost
$username   = "root";        // your DB username
$password   = "";            // your DB password
$dbname     = "avianna_resort";
```

### 4. Email (Gmail SMTP) Setup
Edit `book.php` and `admin/approve_booking.php` — change:
```php
$mail->Username = 'your-gmail@gmail.com';
$mail->Password = 'your-16-char-app-password';
```

**How to get a Gmail App Password:**
1. Enable 2-Step Verification on your Google Account
2. Go to https://myaccount.google.com/apppasswords
3. Create a new App Password for "Mail"
4. Copy the 16-character code (no spaces) into the files above

### 5. Admin Login
- URL: `yoursite.com/admin/login.php`
- Default username: `admin`
- Default password: `admin123`
- **Change this immediately after first login!** (Update the hash in the DB via `admin/hash.php`)

### 6. File Permissions
Ensure the uploads directory is writable:
```bash
chmod 755 uploads/
```

---

## Folder Structure
```
avianna_resort/
├── index.php              ← Home page
├── book.php               ← Booking form
├── aboutus.php            ← About page
├── gallery.php            ← Gallery
├── reviews.php            ← Guest reviews
├── cancel_booking.php     ← Guest self-cancellation
├── submit_review.php      ← Review form handler
├── send_email.php         ← Email helper (legacy)
├── notification.php       ← Browser push notification script
├── conn.php               ← Database connection (EDIT THIS)
├── PHPMailer.php          ← Email library
├── Exception.php          ← PHPMailer dependency
├── SMTP.php               ← PHPMailer dependency
├── smtp_test.php          ← SMTP debug tool (DELETE AFTER TESTING)
├── avianna_resort.sql     ← Database setup SQL
├── uploads/               ← Review photo uploads (writable)
├── img/                   ← Place your images here
│   ├── bg.jpg             ← Hero background image
│   └── avianna.png        ← Resort logo
└── admin/
    ├── login.php          ← Admin login
    ├── logout.php         ← Admin logout
    ├── admin.php          ← Pending bookings + announcements
    ├── approve.php        ← Approved/booked view
    ├── approve_booking.php← Approve action + send email
    ├── cancel_booking.php ← Archive booking action
    ├── admin_history.php  ← Cancelled/archived bookings
    ├── admin_analytics.php← Revenue & stats dashboard
    ├── delete.php         ← Hard-delete booking
    ├── hash.php           ← Password hash generator
    ├── PHPMailer.php      ← Email library (admin copy)
    ├── Exception.php      ← PHPMailer dependency
    └── SMTP.php           ← PHPMailer dependency
```

---

## Admin Features
| Feature | URL |
|---------|-----|
| Pending Bookings | `/admin/admin.php` |
| Approved/Booked | `/admin/approve.php` |
| Booking History | `/admin/admin_history.php` |
| Analytics & Revenue | `/admin/admin_analytics.php` |

## Key Fixes Applied
- ✅ Database column names unified (`checkin`/`checkout` in bookings, `checkin_date`/`checkout_date` in deleted_bookings)
- ✅ `deleted_bookings` table now populates both `deletion_date` and `deleted_at` columns for full compatibility
- ✅ `approve_booking.php` loads PHPMailer directly (no Composer/vendor needed)
- ✅ Admin `cancel_booking.php` correctly maps source → destination column names
- ✅ `book.php` validates payment method is selected
- ✅ All email functions use relaxed SSL for shared hosting compatibility
- ✅ Uploads directory handled safely in reviews

## Security Checklist Before Going Live
- [ ] Change admin password (use `admin/hash.php` to generate new hash, update DB)
- [ ] Delete `smtp_test.php` from server
- [ ] Delete `admin/hash.php` from server
- [ ] Set a strong DB password in `conn.php`
- [ ] Ensure `uploads/` is not directly accessible via URL (add `.htaccess`)

## Adding Images
Place these files in the `img/` folder:
- `bg.jpg` — hero background (recommended: 1920×1080px landscape)
- `avianna.png` — resort logo (recommended: 200×200px, transparent PNG)
