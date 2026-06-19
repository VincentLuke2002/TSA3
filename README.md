Project Overview

What it is, who it's for, and a screenshot or two of the UI

Tech Stack

PHP, MySQL, XAMPP — list versions if relevant

Setup Instructions

Step-by-step: clone/download → put in htdocs/TSA3 → run database.sql in phpMyAdmin → start Apache & MySQL → open localhost/TSA3/Activity_B/Display.php
Mention the default XAMPP credentials (root, empty password) and where to change them in Database.php

Folder Structure

A tree showing what each file does, since your structure has two Activity folders

Features List

User registration & login with session management
Concert browsing with seat availability
Ticket booking with category selection and quantity picker
Booking history with unique booking codes
Responsive dark UI

Database Schema

Brief description of the 4 tables: users, concerts, ticket_categories, bookings
Or just tell them to check database.sql

Default Data

Let them know 3 LANY concerts are pre-seeded so the site works immediately

How to Use

Short user flow: Register → Browse shows → Pick a category → Confirm → See your ticket in My Tickets

Customization Notes

How to add more concerts (via phpMyAdmin or a future admin panel)
How to change the artist name or color scheme (point to the CSS variables at the top of style.css)

Known Limitations / Future Ideas

No payment gateway (just a booking system)
No admin panel yet
No email confirmation
No QR code on tickets (could add later)
