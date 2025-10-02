# APLX Parcel Transport

A responsive logistics/parcel booking website with dark/light theme, service showcase, contact/quote forms, and basic customer flows (register, book, track). Built for WAMP/XAMPP style PHP hosting.

## Features
- **Landing page** with slideshow hero, services, about, why-us, gallery, stats, map, footer.
- **Theme toggle** with persistence (`localStorage`).
- **Responsive design** across sections. On mobile, grids stack and nav wraps.
- **Mobile Track modal**: On small screens, the navbar Track opens an in-page modal; on desktop it navigates to `track.html`.
- **Customer flows**:
  - Register: `frontend/customer/register.html` → POST `/Parcel/backend/customer_register.php`
  - Book: `frontend/customer/book.html` → POST `/Parcel/backend/book_submit.php`
  - Track: `frontend/track.html` → GET `/Parcel/backend/track_result.php`

## Tech Stack
- Frontend: HTML5, CSS, vanilla JS
- Styling: `css/style.css` (custom, dark/light via `[data-theme]`)
- Backend: PHP endpoints (not included in this README), MySQL
- Server: WAMP/XAMPP/Apache on Windows

## Project Structure
```
Parcel/
├─ css/
│  └─ style.css                # Global styles, responsive, modal styles
├─ frontend/
│  ├─ index.html               # Landing page (+ mobile Track modal & script)
│  ├─ track.html               # Full Track page
│  ├─ auth/
│  │  └─ login.html            # Admin login
│  └─ customer/
│     ├─ register.html         # Customer registration
│     └─ book.html             # Book a parcel
├─ backend/                    # PHP handlers (expected paths used in forms)
└─ schema.sql                  # MySQL database schema
```

## Getting Started (Local WAMP)
1. **Clone** the repo under your web root (WAMP default is `c:/wamp64/www`).
   - Example path used in code: `c:/wamp64/www/APLX/Parcel`
2. **Database**
   - Open phpMyAdmin and run `schema.sql` or via MySQL CLI:
     ```sql
     SOURCE c:/wamp64/www/APLX/Parcel/schema.sql;
     ```
   - This creates `parcel_db` with `users` and `customers` tables.
3. **Backend endpoints**
   - Ensure these PHP scripts exist and connect to `parcel_db`:
     - `/Parcel/backend/customer_register.php`
     - `/Parcel/backend/book_submit.php`
     - `/Parcel/backend/track_result.php`
     - `/Parcel/backend/auth_login.php`
   - Configure DB connection credentials inside your PHP files.
4. **Run**
   - Visit: `http://localhost/APLX/Parcel/frontend/index.html`

## Mobile Track Modal Behavior
- Defined in `frontend/index.html` near the end:
  - A hidden `<div id="trackModal">` contains the Track form.
  - A small JS snippet intercepts the navbar Track link on viewports `<= 640px` and opens the modal.
  - Desktop continues to navigate to `track.html`.
- Modal styling in `css/style.css` under `/* ===== Mobile Track Modal ===== */`.

## Theming
- Toggle button with id `themeToggle` sets `[data-theme="light"|"dark"]` on `<html>` and stores selection in `localStorage`.
- Many components adapt via `[data-theme="light"]` overrides in `css/style.css`.

## Accessibility & UX
- Buttons and links have clear focus/click targets.
- Reduced motion respected for some animations.
- Forms use semantic inputs with placeholders and required fields.

## Deployment
- Any static host with PHP support for `/backend` endpoints (Apache/Nginx + PHP-FPM) works.
- Ensure document root routing preserves `/Parcel/...` paths or adjust links accordingly.

## Customization
- Colors and spacing are managed via CSS variables at the top of `css/style.css`.
- Section backgrounds and image URLs can be customized directly in the HTML/CSS.

## Notes
- If your local path differs, update the absolute links in navbar or switch to relative links for portability.
- Security: hash passwords on the server (`password_hash`), validate and sanitize all inputs, protect admin routes.
