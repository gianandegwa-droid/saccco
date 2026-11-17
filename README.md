MemberLink SACCO - Centralized Member Records Management (Prototype)

A lightweight prototype implementing a centralized member records management system for MemberLink SACCO, Nyeri.

Features
- Staff authentication (admin/teller)
- Member registration, update, delete, search
- Duplicate detection by National ID / Phone / Email
- CSV export reports
- SQLite database (single-file) for easy setup

Requirements
- PHP 8.0+ with PDO SQLite extension

Quick setup
1. From project root run: `php init_db.php` to create the database and seed an admin user.
2. Start server: `php -S localhost:8000 -t public`
3. Open `http://localhost:8000` in your browser.

Default admin credentials (created by init)
- Email: admin@memberlink.local
- Password: Admin@123

Files
- `public/` — web entry files (login, dashboard, members pages)
- `src/` — PHP classes for DB, Auth, MemberModel
- `data/database.sqlite` — SQLite database (created by `init_db.php`)
- `PROPOSAL.md` — Project proposal / research document

Notes
- This is a prototype for demonstration and academic submission. For production: use HTTPS, stronger auth, role-based access, input sanitization, backups and cloud hosting.
