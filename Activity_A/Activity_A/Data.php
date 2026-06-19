<?php

// ACTIVITY A - Data.php
//in $_SESSION instead of a database table.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['catalog_version']) || $_SESSION['catalog_version'] != 2) {
    $_SESSION['concerts'] = seedConcerts();
    $_SESSION['catalog_version'] = 2;
}

function isLoggedIn() {
    return isset($_SESSION['logged_in_user']);
}

function getCurrentUser() {
    return isLoggedIn() ? $_SESSION['logged_in_user'] : null;
}

function generateBookingCode() {
    return 'LANY-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

// HARDCODED CATALOG than database
function seedConcerts() {
    return [
        [
            'id'           => 1,
            'title'        => 'LANY: a beautiful blur Tour',
            'venue'        => 'Mall of Asia Arena, Pasay City',
            'concert_date' => '2026-08-14',
            'concert_time' => '19:00:00',
            'description'  => 'LANY returns to Manila for one night only, bringing the full a beautiful blur live experience to the Philippines.',
            'categories'   => [
                ['id' => 101, 'category_name' => 'General Admission', 'price' => 2500.00, 'available_quantity' => 4000],
                ['id' => 102, 'category_name' => 'Upper Box',         'price' => 3200.00, 'available_quantity' => 2000],
                ['id' => 103, 'category_name' => 'Lower Box',         'price' => 4500.00, 'available_quantity' => 3000],
                ['id' => 104, 'category_name' => 'VIP Floor',         'price' => 8500.00, 'available_quantity' => 700],
                ['id' => 105, 'category_name' => 'VVIP / Soundcheck', 'price' => 15000.00, 'available_quantity' => 300],
            ],
        ],

        [
            'id'           => 2,
            'title'        => 'LANY: Mama\'s Boy World Tour',
            'venue'        => 'SMX Convention Center, Pasay City',
            'concert_date' => '2026-09-05',
            'concert_time' => '20:00:00',
            'description'  => 'An intimate run-through of LANY\'s catalog, from Malibu Nights to their newest singles.',
            'categories'   => [
                ['id' => 201, 'category_name' => 'General Admission', 'price' => 2200.00, 'available_quantity' => 3500],
                ['id' => 203, 'category_name' => 'Upper Box',         'price' => 2800.00, 'available_quantity' => 1500],
                ['id' => 202, 'category_name' => 'Lower Box',         'price' => 4000.00, 'available_quantity' => 2500],
                ['id' => 204, 'category_name' => 'VIP Floor',         'price' => 7500.00, 'available_quantity' => 800],
                ['id' => 205, 'category_name' => 'VVIP / Soundcheck', 'price' => 13000.00, 'available_quantity' => 200],
            ],
        ],

        [
            'id'           => 3,
            'title'        => 'LANY: Unplugged Manila',
            'venue'        => 'New Frontier Theater, Quezon City',
            'concert_date' => '2026-10-02',
            'concert_time' => '19:30:00',
            'description'  => 'A stripped-down acoustic evening with Paul Klein, just vocals, guitar, and the band.',
            'categories'   => [
                ['id' => 301, 'category_name' => 'General Admission',      'price' => 3500.00, 'available_quantity' => 1500],
                ['id' => 302, 'category_name' => 'Premium Seated',         'price' => 6500.00, 'available_quantity' => 1000],
                ['id' => 303, 'category_name' => 'VIP Table (2 persons)',  'price' => 20000.00, 'available_quantity' => 400],
                ['id' => 304, 'category_name' => 'VVIP Meet & Greet',      'price' => 25000.00, 'available_quantity' => 100],
            ],
        ],
    ];
}

// own working copy of the catalog so quantities can change as bookings are made.
function initConcerts() {
    if (!isset($_SESSION['concerts'])) {
        $_SESSION['concerts'] = seedConcerts();
    }
}

// Returns every concert, with min/max price and total seats left
// computed 
function getConcerts() {
    initConcerts();
    $concerts = $_SESSION['concerts'];

    foreach ($concerts as &$c) {
        $prices = array_column($c['categories'], 'price');
        $seats  = array_column($c['categories'], 'available_quantity');
        $c['min_price']  = min($prices);
        $c['max_price']  = max($prices);
        $c['seats_left'] = array_sum($seats);
    }
    unset($c);

    return $concerts;
}


function getConcertById($id) {
    initConcerts();
    foreach ($_SESSION['concerts'] as $c) {
        if ($c['id'] == $id) return $c;
    }
    return null;
}

// single ticket category inside a concert.
function getCategory($concertId, $categoryId) {
    $concert = getConcertById($concertId);
    if (!$concert) return null;
    foreach ($concert['categories'] as $cat) {
        if ($cat['id'] == $categoryId) return $cat;
    }
    return null;
}

// Decreases available_quantity for a category (mirrors the SQL)
function reduceAvailability($concertId, $categoryId, $qty) {
    foreach ($_SESSION['concerts'] as &$c) {
        if ($c['id'] == $concertId) {
            foreach ($c['categories'] as &$cat) {
                if ($cat['id'] == $categoryId) {
                    $cat['available_quantity'] -= $qty;
                }
            }
            unset($cat);
        }
    }
    unset($c);
}

// Saves a new booking for the currently logged-in user

function addBooking($booking) {
    if (!isset($_SESSION['bookings'])) {
        $_SESSION['bookings'] = [];
    }
    $_SESSION['bookings'][] = $booking;
}

// Returns all bookings made by the current username
function getMyBookings() {
    if (!isLoggedIn() || !isset($_SESSION['bookings'])) return [];
    $username = $_SESSION['logged_in_user']['username'];
    return array_values(array_filter($_SESSION['bookings'], function ($b) use ($username) {
        return $b['username'] === $username;
    }));
}

function formatDate($d) { return date('D, M j, Y', strtotime($d)); }
function formatTime($t) { return date('g:i A', strtotime($t)); }
?>
