# InnSync - Hotel Management System
A PHP/MySQL hotel reservation system built with Bootstrap 5, jQuery AJAX, and MVC-inspired architecture.

---

## ⚙️ SETUP INSTRUCTIONS

### 1. Requirements
- XAMPP (PHP 8.0+ and MySQL)
- A web browser

### 2. Database Setup
1. Open **phpMyAdmin** → http://localhost/phpmyadmin
2. Click **Import**
3. Upload `database.sql`
4. Click **Go** — this creates the `dbinnsync` database with all tables and seed data

### 3. Project Setup
1. Copy the entire `innsync/` folder into:
   - Windows: `C:/xampp/htdocs/innsync`
   - Mac: `/Applications/XAMPP/htdocs/innsync`
2. Make sure XAMPP Apache and MySQL are running

### 4. Access the System
- **URL:** http://localhost/innsync
- **Admin Login:**
  - Email: `admin@innsync.com`
  - Password: `admin123`

> 💡 To change the admin password, run this in phpMyAdmin:
```sql
UPDATE users SET password = '$2y$10$NewHashHere' WHERE email = 'admin@innsync.com';
```
> Generate a new hash by creating a PHP file in htdocs:
```php
<?php echo password_hash('your_new_password', PASSWORD_DEFAULT); ?>
```

---

## 📁 FILE STRUCTURE
```
innsync/
├── api/                   ← Backend API (PHP)
│   ├── config.php         ← DB connection + session
│   ├── env.php            ← DB credentials
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── accounts.php
│   ├── rooms.php
│   ├── booking.php
│   ├── payment.php
│   ├── receipt.php
│   ├── room_type.php
│   ├── reservation_type.php
│   ├── refund_rules.php
│   ├── reports.php
│   └── uploads/
│       ├── users/
│       ├── receipts/
│       └── rooms/
├── pages/
│   ├── menu.php           ← Customer sidebar
│   ├── admin_menu.php     ← Admin sidebar
│   ├── home/              ← Customer dashboard
│   ├── login/
│   ├── register/
│   ├── rooms/             ← Browse rooms
│   ├── booking/           ← Make a booking
│   ├── payment/           ← Submit payment
│   ├── history/           ← Booking history + cancel
│   ├── account/           ← Edit profile
│   └── admin/
│       ├── index.php      ← Admin dashboard
│       ├── users/         ← Approve/reject users
│       ├── rooms/         ← Add/edit/delete rooms
│       ├── room_types/
│       ├── reservation_types/
│       ├── bookings/      ← Approve/cancel bookings
│       ├── payments/      ← Confirm/refund payments
│       ├── refund_rules/
│       ├── reports/       ← 4 report types
│       └── logs/          ← Admin activity logs
├── css/
│   └── public.css         ← Global styles
├── index.php              ← Login page (entry point)
├── database.sql           ← Full DB schema + seed data
└── README.md
```

---

## 🔄 USER FLOW

### Customer
1. Register → Wait for admin approval
2. Login → Dashboard
3. Browse Rooms → Filter by type/price/capacity
4. Book a Room → Choose dates + reservation type
5. Pay → Choose payment method
6. View History → Cancel if needed

### Admin
1. Login → Admin Dashboard (stats overview)
2. Approve/Reject Users
3. Manage Room Types, Rooms, Reservation Types
4. Approve/Cancel Bookings
5. Confirm Payments / Process Refunds
6. Set Refund Rules
7. View Reports (bookings, payments, users, rooms)
8. View Activity Logs

---

## 🗄️ DATABASE TABLES
| Table | Phase |
|-------|-------|
| users | 2, 7 |
| room_types | 4, 7 |
| rooms | 4, 7 |
| room_images | 4 |
| reservation_types | 5, 7 |
| bookings | 5, 6, 7, 8 |
| payments | 6, 7, 8 |
| receipts | 6, 7 |
| cancellations | 5, 6, 7 |
| refund_rules | 7 |
| reports | 8 |
| admin_logs | 7, 8 |
| booking_logs | 5, 7, 8 |

---

## 🛠️ TECH STACK
- **Backend:** PHP 8+ with MySQLi prepared statements
- **Frontend:** Bootstrap 5.3, Bootstrap Icons
- **AJAX:** jQuery 3.7
- **Database:** MySQL via XAMPP
- **Pattern:** API-based (JSON responses) similar to professor's reference
