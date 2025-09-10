# BCC Community (Bird Catalog + Forum)

A minimal PHP + MySQL app featuring:
- User auth (register, login, logout)
- Birds catalog (image, scientific name, description)
- Forums (boards, posts with optional image + location, likes, comments)
- Home page with recent image tiles + headlines

## 1) Import database
- Open phpMyAdmin (or MySQL CLI) and run the SQL file:
  - `database/bcc_schema.sql`

## 2) Configure web root
- Place this `bcc/` folder under your server root (e.g., `C:\xampp\htdocs\` on Windows).
- If you use a different folder name, edit `config.php` -> `url()` base.