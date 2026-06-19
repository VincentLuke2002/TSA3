# ◈ LANY Concert Ticketing System

![PHP](https://img.shields.io/badge/PHP-8.x-7C3AED?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-F43F5E?style=for-the-badge&logo=mysql&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-XAMPP-7C3AED?style=for-the-badge&logo=apache&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Dark_Theme-F43F5E?style=for-the-badge&logo=css3&logoColor=white)

> A full-stack concert ticketing web app for **LANY** — browse shows, register an account, book tickets, and view your confirmed bookings. Built with PHP, MySQL, and XAMPP for local development.

---

## 📸 Screenshots

| Homepage | Book Tickets | My Tickets |
|---|---|---|
|  | * | <img width="940" height="826" alt="image" src="https://github.com/user-attachments/assets/5d02ddcc-996e-4c49-a533-596bfee2a720" />
 |

---

## ✨ Features

- 🔐 **User Auth** — Register, login, and logout with secure bcrypt-hashed passwords
- 🎤 **Concert Listings** — Browse upcoming LANY shows with dates, venues, and seat availability
- 🎫 **Ticket Booking** — Pick a seating category, set quantity, and confirm with a unique booking code
- 📋 **My Tickets** — View all your bookings in one place with styled ticket stubs
- ⚡ **Low Stock Alerts** — Red badge appears when fewer than 500 seats remain
- 🌙 **Dark UI** — Concert-inspired design with violet + rose accents, Cinzel + Inter fonts, and perforated ticket card edges

---

## 🗂️ Folder Structure

```
htdocs/TSA3/
├── database.sql               ← Run this first in phpMyAdmin
├── Activity_A/
│   ├── Display.php            ← Redirects to Activity_B
│   ├── Login.php              ← Redirects to Activity_B
│   ├── Logout.php             ← Redirects to Activity_B
│   └── RegistrationPage.php   ← Redirects to Activity_B
└── Activity_B/
    ├── Database.php           ← DB connection + session helpers
    ├── Display.php            ← Homepage: concert listing
    ├── Login.php              ← User sign-in
    ├── Logout.php             ← Session destroy + redirect
    ├── RegistrationPage.php   ← New account creation
    ├── BookTicket.php         ← Ticket category picker + booking
    ├── Bookings.php           ← My Tickets: booking history
    └── style.css              ← All styles (dark concert theme)
```

---

## 🚀 Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL running
- A browser

### Steps

**1. Clone or download this repo**
```bash
git clone https://github.com/your-username/TSA3.git
```

**2. Move to htdocs**
```
Copy the TSA3 folder into: C:\xampp\htdocs\TSA3\
```

**3. Set up the database**
- Open `http://localhost/phpmyadmin`
- Click the **SQL** tab
- Paste the contents of `database.sql` and click **Go**

**4. Open the site**
```
http://localhost/TSA3/Activity_B/Display.php
```

---

## 🗄️ Database Schema

Database name: `lany_tickets`

| Table | Purpose |
|---|---|
| `users` | Registered user accounts |
| `concerts` | Concert listings with date, venue, and seat count |
| `ticket_categories` | Seating tiers and prices per concert |
| `bookings` | Confirmed bookings with unique booking codes |

> 3 LANY concerts are pre-seeded in `database.sql` so the site works immediately after setup.

---

## ⚙️ Configuration

### Database credentials
Open `Activity_B/Database.php` and update if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // change if needed
define('DB_PASS', '');       // add your password here
define('DB_NAME', 'lany_tickets');
```

### Color scheme
All colors are CSS variables at the top of `Activity_B/style.css`:
```css
:root {
  --violet:  #7C3AED;   /* primary accent */
  --rose:    #F43F5E;   /* secondary accent */
  --bg-base: #080B14;   /* page background */
}
```

### Adding more concerts
Insert directly in phpMyAdmin:
```sql
INSERT INTO concerts (title, venue, concert_date, concert_time, description, total_seats, available_seats)
VALUES ('LANY: NEW SHOW', 'Venue Name', '2025-12-31', '20:00:00', 'Description.', 5000, 5000);
```

---

## 🧭 User Flow

```
Register → Login → Browse Shows → Book Tickets → Confirm → View in My Tickets
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.x |
| Database | MySQL 8.x |
| Server | Apache via XAMPP |
| Frontend | HTML5 + CSS3 (Vanilla) |
| Fonts | Cinzel + Inter (Google Fonts) |

---

## 🔮 Future Ideas

- [ ] Payment gateway (PayMongo / GCash)
- [ ] Admin panel to manage concerts and view all bookings
- [ ] Email confirmation with booking code via PHPMailer
- [ ] QR code generation on ticket stubs
- [ ] Ticket cancellation with refund policy
- [ ] Search and filter concerts by date or venue

---

## 📄 License

This project is for educational purposes as part of **TSA3**.

---

<p align="center">Made for LANY 🎵 &nbsp;|&nbsp; TSA3 &nbsp;|&nbsp; 2025</p>
