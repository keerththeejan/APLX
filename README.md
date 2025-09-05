# Flask + MySQL Starter (HTML/CSS frontend, Python backend)

A minimal full‑stack app using Flask, SQLAlchemy (PyMySQL driver), Jinja templates, and vanilla HTML/CSS.

## Features
- Add/list/delete simple items
- MySQL database via SQLAlchemy
- .env config using python‑dotenv

## Prerequisites
- Python 3.10+
- MySQL Server (8.0+ recommended)
- pip

## Setup
1. Clone or open this folder.
2. Create and fill your environment file:
   - Copy `.env.example` to `.env` and edit values (MySQL user/password/DB name).
3. Create the MySQL database (one‑time):
   ```sql
   CREATE DATABASE myapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```
5. Run the app:
   ```bash
   python app.py
   ```
   The app will auto‑create tables on first run.

## Configuration
- Env vars in `.env`:
  - `SECRET_KEY`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`, `DB_NAME`, `FLASK_RUN_HOST`, `FLASK_RUN_PORT`

## Notes
- For Windows, ensure MySQL is running and accessible at the configured host/port.
- If connection fails, verify credentials and that the `PyMySQL` driver is installed.
