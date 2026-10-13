# SkillHub Marketplace

A simplified online freelancing marketplace built for a Minor Project (DI05000341), Government Polytechnic Rajkot — IT Department.

**Tech stack:** HTML, CSS, JavaScript, PHP, MySQL (runs on XAMPP)

---

## Setup Instructions (XAMPP)

### 1. Install XAMPP
Download and install from https://www.apachefriends.org if you don't already have it.

### 2. Copy the project
Copy the entire `skillhub-marketplace` folder into:
```
C:\xampp\htdocs\skillhub-marketplace
```
(On Mac/Linux, it's usually `/Applications/XAMPP/htdocs/` or `/opt/lampp/htdocs/`)

**Important:** The folder must be named exactly `skillhub-marketplace`, since this name is used in `includes/functions.php` (`BASE_URL`). If you rename the folder, update that one line to match.

### 3. Start Apache and MySQL
Open the XAMPP Control Panel and click **Start** next to both **Apache** and **MySQL**.

### 4. Create the database
1. Open `http://localhost/phpmyadmin` in your browser
2. Click **New** (left sidebar) and create a database named exactly: `skillhub`
3. Click on the `skillhub` database, go to the **Import** tab
4. Choose the `database.sql` file from this project and click **Go**

### 5. Run the one-time setup script
Open this URL in your browser:
```
http://localhost/skillhub-marketplace/setup.php
```
This securely sets the passwords for the demo accounts below (this is safer than shipping pre-hashed passwords inside the SQL file). Once you see "Setup Complete", you can delete `setup.php` if you like — it isn't needed for the app to run.

### 6. Open the site
```
http://localhost/skillhub-marketplace/
```

---

## Demo Login Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@skillhub.com | admin123 |
| Freelancer | rahul@skillhub.com | password123 |
| Freelancer | priya@skillhub.com | password123 |
| Client | aman@skillhub.com | password123 |

You can also register new accounts from the Register page.

---

## Viewing the site on your phone (same Wi-Fi)

1. Find your PC's local IP address: open Command Prompt and run `ipconfig`, note the "IPv4 Address" (e.g. `192.168.1.5`)
2. On your phone (connected to the same Wi-Fi), open:
   ```
   http://192.168.1.5/skillhub-marketplace/
   ```
The site is responsive and will adapt to mobile screens automatically.

---

## Folder Structure
```
skillhub-marketplace/
├── index.php
├── setup.php              (run once, then optional to delete)
├── database.sql
├── config/db_connect.php
├── auth/                  (login, register, logout, profile)
├── client/                (browse, order, checkout, chat, reviews)
├── freelancer/            (gigs, orders, chat)
├── admin/                 (users, gigs, categories, orders)
├── includes/              (header, footer, shared functions)
├── assets/css, assets/js
└── uploads/                (gig images, profile pictures, delivery files)
```

## Notes
- Payments are **simulated only** (dummy "Pay Now" button) — no real gateway, card processing, or KYC is involved, since that is outside the scope of an academic project.
- If you ever get a "Database connection failed" error, check that MySQL is running in XAMPP and that `config/db_connect.php` matches your MySQL username/password (default XAMPP: user `root`, no password).
