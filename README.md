const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  HeadingLevel, AlignmentType, BorderStyle, WidthType, ShadingType,
  LevelFormat, PageNumber, PageBreak, ExternalHyperlink
} = require('docx');
const fs = require('fs');

const VIOLET = '7C3AED';
const ROSE   = 'F43F5E';
const DARK   = '0A0E1A';
const GRAY   = '64748B';
const LIGHT  = 'F1F5F9';
const WHITE  = 'FFFFFF';
const GREEN  = '059669';
const AMBER  = 'D97706';

const thinBorder = { style: BorderStyle.SINGLE, size: 1, color: 'E2E8F0' };
const borders = { top: thinBorder, bottom: thinBorder, left: thinBorder, right: thinBorder };
const noBorder = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
const noBorders = { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder };

function h1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    spacing: { before: 320, after: 120 },
    children: [new TextRun({ text, bold: true, size: 36, font: 'Arial', color: DARK })]
  });
}

function h2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    spacing: { before: 280, after: 100 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: VIOLET, space: 4 } },
    children: [new TextRun({ text, bold: true, size: 28, font: 'Arial', color: VIOLET })]
  });
}

function h3(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_3,
    spacing: { before: 200, after: 80 },
    children: [new TextRun({ text, bold: true, size: 24, font: 'Arial', color: DARK })]
  });
}

function body(text, opts = {}) {
  return new Paragraph({
    spacing: { before: 60, after: 60 },
    children: [new TextRun({ text, size: 22, font: 'Arial', color: DARK, ...opts })]
  });
}

function bullet(text, level = 0) {
  return new Paragraph({
    numbering: { reference: 'bullets', level },
    spacing: { before: 40, after: 40 },
    children: [new TextRun({ text, size: 22, font: 'Arial', color: DARK })]
  });
}

function numbered(text, level = 0) {
  return new Paragraph({
    numbering: { reference: 'numbers', level },
    spacing: { before: 40, after: 40 },
    children: [new TextRun({ text, size: 22, font: 'Arial', color: DARK })]
  });
}

function inlineCode(text) {
  return new TextRun({
    text, font: 'Courier New', size: 20, color: VIOLET,
    shading: { fill: 'EDE9FE', type: ShadingType.CLEAR }
  });
}

function codePara(text) {
  return new Paragraph({
    spacing: { before: 0, after: 0 },
    shading: { fill: '1E1B2E', type: ShadingType.CLEAR },
    indent: { left: 180, right: 180 },
    children: [new TextRun({ text, font: 'Courier New', size: 18, color: 'A78BFA' })]
  });
}

function codeBlock(lines) {
  return [
    new Paragraph({
      spacing: { before: 100, after: 0 },
      shading: { fill: '1E1B2E', type: ShadingType.CLEAR },
      indent: { left: 180, right: 180 },
      children: []
    }),
    ...lines.map(l => codePara(l)),
    new Paragraph({
      spacing: { before: 0, after: 100 },
      shading: { fill: '1E1B2E', type: ShadingType.CLEAR },
      indent: { left: 180, right: 180 },
      children: []
    }),
  ];
}

function spacer(before = 80) {
  return new Paragraph({ spacing: { before, after: 0 }, children: [] });
}

function badgePara(label, text, color) {
  return new Paragraph({
    spacing: { before: 60, after: 60 },
    children: [
      new TextRun({ text: `${label}  `, bold: true, size: 22, font: 'Arial', color: DARK }),
      new TextRun({ text: ` ${text} `, size: 20, font: 'Arial', color: WHITE,
        shading: { fill: color, type: ShadingType.CLEAR }, bold: true })
    ]
  });
}

function sectionDivider() {
  return new Paragraph({
    spacing: { before: 200, after: 200 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 2, color: 'E2E8F0', space: 1 } },
    children: []
  });
}

function makeTable(headers, rows, colWidths) {
  const headerRow = new TableRow({
    tableHeader: true,
    children: headers.map((h, i) => new TableCell({
      borders,
      width: { size: colWidths[i], type: WidthType.DXA },
      shading: { fill: VIOLET, type: ShadingType.CLEAR },
      margins: { top: 80, bottom: 80, left: 120, right: 120 },
      children: [new Paragraph({ children: [new TextRun({ text: h, bold: true, size: 20, font: 'Arial', color: WHITE })] })]
    }))
  });
  const dataRows = rows.map((row, ri) => new TableRow({
    children: row.map((cell, ci) => new TableCell({
      borders,
      width: { size: colWidths[ci], type: WidthType.DXA },
      shading: { fill: ri % 2 === 0 ? WHITE : 'F8FAFC', type: ShadingType.CLEAR },
      margins: { top: 80, bottom: 80, left: 120, right: 120 },
      children: [new Paragraph({ children: [new TextRun({ text: cell, size: 20, font: 'Arial', color: DARK })] })]
    }))
  }));
  return new Table({
    width: { size: colWidths.reduce((a,b) => a+b, 0), type: WidthType.DXA },
    columnWidths: colWidths,
    rows: [headerRow, ...dataRows]
  });
}

const doc = new Document({
  numbering: {
    config: [
      { reference: 'bullets', levels: [
        { level: 0, format: LevelFormat.BULLET, text: '\u2022', alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 720, hanging: 360 } } } },
        { level: 1, format: LevelFormat.BULLET, text: '\u25E6', alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 1080, hanging: 360 } } } },
      ]},
      { reference: 'numbers', levels: [
        { level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 720, hanging: 360 } } } },
      ]},
    ]
  },
  styles: {
    default: { document: { run: { font: 'Arial', size: 22 } } },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 36, bold: true, font: 'Arial', color: DARK },
        paragraph: { spacing: { before: 320, after: 120 }, outlineLevel: 0 } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 28, bold: true, font: 'Arial', color: VIOLET },
        paragraph: { spacing: { before: 280, after: 100 }, outlineLevel: 1 } },
      { id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 24, bold: true, font: 'Arial', color: DARK },
        paragraph: { spacing: { before: 200, after: 80 }, outlineLevel: 2 } },
    ]
  },
  sections: [{
    properties: {
      page: {
        size: { width: 12240, height: 15840 },
        margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 }
      }
    },
    children: [

      // ── COVER ──────────────────────────────────────────
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 1440, after: 200 },
        shading: { fill: '0A0E1A', type: ShadingType.CLEAR },
        children: [new TextRun({ text: '  LANY CONCERT TICKETING SYSTEM  ', size: 48,
          bold: true, font: 'Arial', color: WHITE,
          shading: { fill: '0A0E1A', type: ShadingType.CLEAR } })]
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 80 },
        children: [new TextRun({ text: 'README & DOCUMENTATION', size: 24, font: 'Arial', color: VIOLET, bold: true })]
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 40 },
        children: [new TextRun({ text: 'TSA3 Project  |  PHP + MySQL + XAMPP', size: 20, font: 'Arial', color: GRAY })]
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 800 },
        children: [new TextRun({ text: '2025', size: 20, font: 'Arial', color: GRAY })]
      }),

      sectionDivider(),

      // ── 1. OVERVIEW ──────────────────────────────────
      h2('1. Project Overview'),
      body('The LANY Concert Ticketing System is a full-stack web application built with PHP and MySQL, designed to allow users to browse upcoming LANY concerts in the Philippines, register accounts, book tickets across different seating categories, and view their confirmed bookings.'),
      spacer(80),
      body('This project is built to run locally using XAMPP and follows a two-activity folder structure (Activity_A and Activity_B) as required by TSA3.'),
      spacer(60),
      badgePara('Artist:', 'LANY (Paul Klein)', ROSE),
      badgePara('Status:', 'Complete', GREEN),
      badgePara('Environment:', 'XAMPP (localhost)', VIOLET),

      sectionDivider(),

      // ── 2. TECH STACK ────────────────────────────────
      h2('2. Tech Stack'),
      makeTable(
        ['Layer', 'Technology', 'Version'],
        [
          ['Backend', 'PHP', '8.x (XAMPP)'],
          ['Database', 'MySQL', '8.x (XAMPP)'],
          ['Server', 'Apache', 'XAMPP bundled'],
          ['Frontend', 'HTML5 + CSS3', 'Vanilla'],
          ['Fonts', 'Google Fonts', 'Cinzel + Inter'],
          ['Styling', 'Custom CSS', 'Dark Concert Theme'],
        ],
        [3500, 3500, 2360]
      ),

      sectionDivider(),

      // ── 3. FOLDER STRUCTURE ──────────────────────────
      h2('3. Folder Structure'),
      body('Place the entire TSA3 folder inside your XAMPP htdocs directory:'),
      spacer(60),
      ...codeBlock([
        'C:\\xampp\\htdocs\\TSA3\\',
        '   database.sql               <- Run this first in phpMyAdmin',
        '   Activity_A\\',
        '      Display.php             <- Redirects to Activity_B',
        '      Login.php               <- Redirects to Activity_B',
        '      Logout.php              <- Redirects to Activity_B',
        '      RegistrationPage.php    <- Redirects to Activity_B',
        '   Activity_B\\',
        '      Database.php            <- DB connection + session helpers',
        '      Display.php             <- Homepage: concert listing',
        '      Login.php               <- User sign-in',
        '      Logout.php              <- Session destroy + redirect',
        '      RegistrationPage.php    <- New account creation',
        '      BookTicket.php          <- Ticket category picker + booking',
        '      Bookings.php            <- My Tickets: booking history',
        '      style.css               <- All styles (dark concert theme)',
      ]),

      sectionDivider(),

      // ── 4. SETUP ─────────────────────────────────────
      h2('4. Setup Instructions'),

      h3('Step 1 — Start XAMPP'),
      numbered('Open the XAMPP Control Panel'),
      numbered('Click Start next to Apache'),
      numbered('Click Start next to MySQL'),
      numbered('Both status lights should turn green'),

      spacer(100),
      h3('Step 2 — Copy Files'),
      numbered('Download or clone the TSA3 folder'),
      numbered('Copy the entire TSA3 folder into: C:\\xampp\\htdocs\\'),
      numbered('Final path should be: C:\\xampp\\htdocs\\TSA3\\'),

      spacer(100),
      h3('Step 3 — Set Up the Database'),
      numbered('Open your browser and go to: http://localhost/phpmyadmin'),
      numbered('Click the SQL tab at the top'),
      numbered('Open database.sql in any text editor and copy all the contents'),
      numbered('Paste into the SQL box in phpMyAdmin and click Go'),
      numbered('You should see a success message — the lany_tickets database is now created'),

      spacer(100),
      h3('Step 4 — Open the Site'),
      numbered('In your browser, go to:'),
      new Paragraph({
        spacing: { before: 40, after: 60 },
        indent: { left: 720 },
        children: [inlineCode('http://localhost/TSA3/Activity_B/Display.php')]
      }),
      numbered('You should see the LANY homepage with 3 concerts listed'),

      sectionDivider(),

      // ── 5. DATABASE ──────────────────────────────────
      h2('5. Database Schema'),
      body('Database name: ', { bold: false }),
      new Paragraph({
        spacing: { before: 0, after: 80 },
        children: [inlineCode('lany_tickets')]
      }),
      spacer(40),

      makeTable(
        ['Table', 'Purpose', 'Key Columns'],
        [
          ['users', 'Registered accounts', 'id, username, email, password (hashed), full_name'],
          ['concerts', 'Concert listings', 'id, title, venue, concert_date, concert_time, description'],
          ['ticket_categories', 'Seat tiers per concert', 'id, concert_id, category_name, price, available_quantity'],
          ['bookings', 'Confirmed bookings', 'id, user_id, concert_id, booking_code, quantity, total_price, status'],
        ],
        [2000, 2500, 4860]
      ),

      spacer(120),
      body('Passwords are stored using PHP\'s password_hash() with BCRYPT — never in plain text.'),
      body('Each booking gets a unique booking_code in the format: LANY-XXXXXXXX'),

      sectionDivider(),

      // ── 6. FEATURES ──────────────────────────────────
      h2('6. Features'),

      h3('User Authentication'),
      bullet('Account registration with full name, username, email, and password'),
      bullet('Password confirmation and email format validation'),
      bullet('Minimum 8-character password requirement'),
      bullet('Secure login with PHP sessions'),
      bullet('Duplicate username/email detection'),
      bullet('Session-based logout'),

      spacer(100),
      h3('Concert Browsing'),
      bullet('Homepage displays all upcoming LANY concerts'),
      bullet('Each card shows date, venue, seat availability, and price range'),
      bullet('Urgent low-stock badge (red) when fewer than 500 seats remain'),
      bullet('3 pre-seeded LANY concerts: MOA Arena, Araneta Coliseum, The Tent'),

      spacer(100),
      h3('Ticket Booking'),
      bullet('Category picker: General Admission, Lower Box, Upper Box, VIP Floor, VVIP'),
      bullet('Live order summary updates as user selects category and quantity'),
      bullet('Quantity control (1-10 tickets per booking)'),
      bullet('Sold-out categories are automatically disabled'),
      bullet('Seat inventory decrements in real time after each booking'),
      bullet('Booking confirmed page with styled ticket stub and booking code'),

      spacer(100),
      h3('My Tickets'),
      bullet('Booking history for logged-in users'),
      bullet('Each ticket displayed as a styled stub with perforated edge'),
      bullet('Shows: booking code, category, quantity, total paid, booking date'),
      bullet('Status badges: Confirmed (green), Pending (amber), Cancelled (red)'),

      spacer(100),
      h3('UI / Design'),
      bullet('Dark concert aesthetic: midnight navy, electric violet (#7C3AED), neon rose (#F43F5E)'),
      bullet('Typography: Cinzel (display/headings) + Inter (body)'),
      bullet('Signature element: perforated ticket edge on all concert and booking cards'),
      bullet('Animated glow effects on hero section'),
      bullet('Responsive layout — works on mobile and desktop'),
      bullet('Smooth hover transitions on all interactive elements'),

      sectionDivider(),

      // ── 7. USER FLOW ────────────────────────────────
      h2('7. How to Use'),
      makeTable(
        ['Step', 'Action', 'Page'],
        [
          ['1', 'Visit the homepage to browse concerts', 'Display.php'],
          ['2', 'Click "Get Started" to create an account', 'RegistrationPage.php'],
          ['3', 'Sign in with your credentials', 'Login.php'],
          ['4', 'Click "Book Tickets" on any concert card', 'BookTicket.php'],
          ['5', 'Select a ticket category and quantity', 'BookTicket.php'],
          ['6', 'Click Confirm Booking', 'BookTicket.php'],
          ['7', 'See your booking code on the confirmation stub', 'BookTicket.php'],
          ['8', 'View all bookings under "My Tickets"', 'Bookings.php'],
        ],
        [800, 5000, 3560]
      ),

      sectionDivider(),

      // ── 8. CONFIGURATION ────────────────────────────
      h2('8. Configuration'),
      h3('Changing Database Credentials'),
      body('If your XAMPP MySQL has a different username or password, open:'),
      new Paragraph({
        spacing: { before: 40, after: 40 },
        children: [inlineCode('Activity_B/Database.php')]
      }),
      body('And update these constants at the top of the file:'),
      spacer(40),
      ...codeBlock([
        "define('DB_HOST', 'localhost');",
        "define('DB_USER', 'root');      // Change if needed",
        "define('DB_PASS', '');          // Add your password here",
        "define('DB_NAME', 'lany_tickets');",
      ]),

      spacer(120),
      h3('Changing the Color Scheme'),
      body('All colors are defined as CSS variables at the top of style.css:'),
      spacer(40),
      ...codeBlock([
        ':root {',
        '  --violet:  #7C3AED;   /* primary accent */',
        '  --rose:    #F43F5E;   /* secondary accent */',
        '  --bg-base: #080B14;   /* page background */',
        '}',
      ]),

      spacer(120),
      h3('Adding More Concerts'),
      body('Insert directly into phpMyAdmin using SQL, or add rows to the concerts and ticket_categories tables. Example:'),
      spacer(40),
      ...codeBlock([
        "INSERT INTO concerts (title, venue, concert_date, concert_time,",
        "  description, total_seats, available_seats)",
        "VALUES ('LANY: NEW SHOW', 'Venue Name', '2025-12-31',",
        "  '20:00:00', 'Description here.', 5000, 5000);",
      ]),

      sectionDivider(),

      // ── 9. LIMITATIONS ──────────────────────────────
      h2('9. Known Limitations & Future Ideas'),
      makeTable(
        ['Limitation', 'Possible Future Addition'],
        [
          ['No payment gateway', 'Integrate PayMongo or GCash for PH payments'],
          ['No admin panel', 'Admin dashboard to manage concerts and view all bookings'],
          ['No email confirmation', 'Send booking code to user email via PHPMailer'],
          ['No QR code on tickets', 'Generate scannable QR from booking code using a PHP library'],
          ['No ticket cancellation UI', 'Let users cancel bookings (with refund policy)'],
          ['No search / filter', 'Filter concerts by date, venue, or price range'],
          ['Session-only auth', 'Add "remember me" with persistent login tokens'],
        ],
        [4000, 5360]
      ),

      sectionDivider(),

      // ── 10. CREDITS ─────────────────────────────────
      h2('10. Credits'),
      makeTable(
        ['Item', 'Detail'],
        [
          ['Project', 'LANY Concert Ticketing System'],
          ['Subject', 'TSA3'],
          ['Stack', 'PHP 8, MySQL 8, Apache (XAMPP), HTML5, CSS3'],
          ['Fonts', 'Cinzel + Inter via Google Fonts'],
          ['Artist Theme', 'LANY (Paul Klein) — indie-pop / dream-pop'],
          ['Year', '2025'],
        ],
        [3000, 6360]
      ),

      spacer(200),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 200, after: 0 },
        children: [new TextRun({ text: 'TSA3  |  LANY Ticketing System  |  2025', size: 18, font: 'Arial', color: GRAY, italics: true })]
      }),

    ]
  }]
});

Packer.toBuffer(doc).then(buf => {
  fs.writeFileSync('/mnt/user-data/outputs/TSA3_README.docx', buf);
  console.log('Done!');
});
