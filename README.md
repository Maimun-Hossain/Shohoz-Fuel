# Fuel Crisis Project

A web-based fuel queue management system that helps drivers find petrol/diesel stations, check real-time availability and stock levels, request tokens to skip long queues, and track their turn — all through a smart token system. Built with **PHP, MySQL, HTML, CSS, and JavaScript**.

## Table of Contents

- [About the Project](#about-the-project)
- [Features](#features)
- [Project File Tree](#project-file-tree)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Setup (Database & Accounts)](#setup-database--accounts)
- [How the Project Works](#how-the-project-works)
- [Demo Accounts](#demo-accounts)
- [How to Run](#how-to-run)

---
## Screenshots

![Screenshot (396)](Assets/Screenshot%202026-09-02%20233714.png)
![Screenshot (397)](Assets/Screenshot%202026-09-02%20233753.png)

---
## About the Project

Fuel Crisis connects drivers with fuel stations in real time. Users can:

- Browse registered stations with live fuel stock and availability status.
- Request a **token** (with a serial number) for petrol or diesel without standing in line.
- Track their token status (Pending → Called → Completed).
- Like/dislike and review stations.

**Staff** manages the queue at their station by calling and completing tokens, while the **Admin** manages everything: stations, market prices, users (role changes, bans), and analytics.

---

## Features

### User
- View stations with live petrol/diesel stock and `AVAILABLE` / `NO STOCK` status
- Request fuel tokens (only one active token at a time, stock-validated)
- Track token serial number and status in real time
- Like / dislike stations and leave reviews
- Token request logged with station name, fuel type, liters, and serial

### Staff
- View pending tokens for their station
- **Call** the next pending token (updates status to `Called`)
- **Complete** tokens (status → `Completed`, stock deducted automatically in a DB transaction)
- Log additional fuel liters into station stock

### Admin
- Full station management (add / edit / delete, toggle AVAILABLE/NO STOCK)
- Update petrol & diesel market prices
- Manage all users (change role, ban/unban)
- View analytics and full activity logs

### Shared
- Role-based access control on every page
- Every action recorded in the `logs` table

---

## Project File Tree

```
WEB-PROJECT/
│
├── home.php                          # Landing page (hero, about, features)
├── db.php                            # DB connection + auto table/seed creation
├── logout.php                        # Destroys session
├── fuelcrisis.sql                    # phpMyAdmin SQL dump (optional import)
├── README.md
│
├── Assets/                           # Images used across pages
│   ├── time.jpg
│   ├── image.png
│   ├── fuel.png
│   ├── fuel-petrol-prices-993971.png
│   ├── Fuel Gauge.jpeg
│   ├── Fuel (1).jpg
│   └── card-im.png
│
├── Registration_Page/
│   ├── SignIn/
│   │   ├── index.php                 # Login form
│   │   └── signin_handler.php        # Authenticates user, routes by role
│   └── SignUp/
│       ├── index.php                 # Registration form
│       └── signup_handler.php        # Creates new user account
│
├── User_Page/
│   ├── USER HOME/
│   │   ├── index.php                 # User dashboard
│   │   └── styles.css
│   ├── USER STATION/
│   │   ├── index.php                 # Station list + token request UI
│   │   ├── user_handler.php          # Like/Dislike, reviews, token requests
│   │   └── style.css
│   └── USER TOKEN/
│       └── user-token-page.php       # Live token tracking page
│
├── Staff_Page/
│   ├── STAFF HOME/
│   │   ├── index.php                 # Staff dashboard
│   │   └── style.css
│   ├── STAFF STATION/
│   │   ├── index.php                 # Station stock view + add liters
│   │   └── style.css
│   └── STAFF TOKEN/
│       ├── staff-token-page.php      # Queue management (call/complete tokens)
│       └── token_handler.php         # Call & complete token logic
│
└── Admin_Page/
    ├── ADMIN HOME/
    │   ├── index.php                 # User management (role, ban)
    │   ├── admin_user_handler.php    # Role change / ban logic
    │   └── style.css
    ├── ADMIN STATION/
    │   ├── index.php                 # Station CRUD + prices
    │   ├── station_handler.php       # Add/Edit/Delete/Toggle/Log liter logic
    │   ├── price_handler.php         # Market price updates
    │   └── style.css
    ├── ADMIN TOKEN/
    │   └── admin-token-page.php      # Admin queue overview
    └── ADMIN ANALYTICS/
        └── index.php                 # Analytics dashboard
```

---

## Tech Stack

| Layer     | Technology                        |
|-----------|-----------------------------------|
| Frontend  | HTML, CSS, JavaScript             |
| Backend   | PHP (procedural, mysqli)          |
| Database  | MySQL / MariaDB                   |
| Server    | Apache (XAMPP)                    |

---

## Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + PHP + MySQL) or any PHP server with MySQL
- A browser

### Steps

1. **Clone or download the project**

   ```bash
   git clone https://github.com/Maimun-Hossain/Fuel-Crisis-Project.git
   ```

2. **Copy the project into XAMPP**

   Place the `WEB-PROJECT` folder inside your XAMPP web root:

   ```
   C:\xampp\htdocs\WEB-PROJECT
   ```

3. **Start XAMPP services**

   Open the XAMPP Control Panel and start:
   - **Apache**
   - **MySQL**

---

## Setup (Database & Accounts)

The database is **fully auto-configured** — no manual setup needed.

1. Open the app in your browser:

   ```
   http://localhost/WEB-PROJECT/home.php
   ```

2. On first load, `db.php` will automatically:
   - Create the `FuelCrisis` database (if it doesn't exist)
   - Create all tables: `users`, `stations`, `tokens`, `reviews`, `likes`, `logs`, `fuel_market_prices`
   - Seed default admin/staff accounts
   - Insert default market prices and two sample stations

> **Optional:** You can also import `fuelcrisis.sql` via phpMyAdmin if you want the demo data (stations, users, logs) exactly as exported.

---

## Demo Accounts

| Role  | Email            | Password | Redirects To                |
|-------|------------------|----------|-----------------------------|
| Admin | admin@gmail.com  | admin    | Admin Dashboard             |
| Staff | staff@gmail.com  | staff    | Staff Dashboard             |
| User  | *(sign up)*      | —        | Landing page (user features)|

---

## How the Project Works

### 1. Flow Overview

```
User requests token ──► token created (status: Pending, serial #N)
        │
Staff calls token    ──► status: Called
        │
Staff completes      ──► status: Completed + fuel stock deducted
```

### 2. Login & Routing (`signin_handler.php`)
- Passwords are checked directly against the `users` table (plain text).
- Banned users are blocked at login.
- Based on `role`, the user is redirected to:
  - `admin` → `Admin_Page/ADMIN HOME/`
  - `staff` → `Staff_Page/STAFF HOME/`
  - `user` → `home.php`

### 3. Token Request (`User_Page/USER STATION/user_handler.php`)
- Validates the station is not `NO STOCK`.
- Validates requested liters against current stock.
- Ensures only **one active token** per user.
- Serial number is per-station, per-day: `MAX(serial_number) + 1`.
- Inserts the token and logs the action.

### 4. Queue Management (`Staff_Page/STAFF TOKEN/token_handler.php`)
- **Call:** picks the lowest pending serial for today at the station, sets it to `Called`.
- **Complete:** sets the token to `Completed` and deducts the fuel liters from station stock inside a **DB transaction** (rollback on failure).

### 5. Station & Price Management (`Admin_Page/`)
- `station_handler.php` — add / edit / delete stations, toggle `AVAILABLE` ↔ `NO STOCK`, add stock liters.
- `price_handler.php` — update petrol/diesel market prices.
- `admin_user_handler.php` — change user roles and ban/unban accounts.

### 6. Logging
Every significant action (token request, call, complete, station changes, price updates, role changes, bans) is inserted into the `logs` table with the acting user's ID, action name, and details — viewable in the admin analytics page.

---

## How to Run

1. Start **Apache** and **MySQL** in XAMPP.
2. Open `http://localhost/WEB-PROJECT/home.php` in your browser.
3. Sign up as a user, or log in with the demo accounts above.

---

## License

This project was built for academic/learning purposes.
