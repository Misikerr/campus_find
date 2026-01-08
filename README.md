# Compus Finder – Campus Lost & Found

Compus Finder is a lightweight web app that helps students report lost items, post found items, and browse campus-wide listings to reconnect belongings with their owners.

---

## Features

- Report lost or found items with details and optional image
- Browse and search approved items
- Manage item status via a PIN (mark as resolved or owner met)
- Announcements feed for campus-wide notices
- Simple reviews/feedback collection
- Responsive, fast, and easy to deploy

---

## Tech Stack

- Frontend: HTML, CSS, JavaScript (vanilla)
- Backend: PHP (procedural), MySQL
- Hosting: Any PHP/MySQL host (e.g., XAMPP/WAMP locally, shared hosting)

---

## Project Structure

```
index.html
styles.css
scripts.js
admin/            # Admin-only pages (dashboard, categories, users, etc.)
php/              # API endpoints and DB connection
  announcements.php
  db.php
  db_config.php   # Optional local-only DB config (not committed)
  items.php
  report.php
  reviews.php
  update_status.php
  uploads/        # Image uploads (created at runtime)
```

---

## Getting Started (Local)

### Prerequisites
- PHP (7.4+) and MySQL server (e.g., XAMPP/WAMP)
- A web root such as `htdocs` or a virtual host pointing to this folder

### Setup
1. Create a MySQL database named `campus_find`.
2. Configure database credentials:
   - Option A: Set environment variables `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
   - Option B: Create `php/db_config.php` that returns an array with keys `host`, `user`, `pass`, `name` (kept out of git).
3. Start Apache and MySQL.
4. Place the project in your web root (e.g., `htdocs/Compus Finder`).
5. Visit `http://localhost/Compus%20Finder/index.html`.

The application reads configuration in [php/db.php](php/db.php) with the following precedence: `php/db_config.php` → environment variables → defaults (`localhost`, `root`, empty password, `campus_find`).

---

## API Endpoints

Base path assumes the project is served from the web root; adjust as needed.

- [php/report.php](php/report.php)
  - Method: `POST` (`application/json` or `multipart/form-data`)
  - Body (JSON example):
    ```json
    {
      "title": "Backpack",
      "description": "Black backpack near library",
      "category": "Bags",
      "location": "Main Library",
      "date_lost": "2025-12-01",
      "contact_name": "Alex",
      "contact_phone": "+123456789",
      "type": "lost",            // or "found"
      "manage_pin": "1234",      // used for later updates
      "image_url": "uploads/img_abc.jpg" // optional
    }
    ```
  - Response: `201` on success, `400/503/500` on error

- [php/items.php](php/items.php)
  - Method: `GET`
  - Returns approved items sorted by `created_at` (fallback to `id`)
  - Response: `200` with JSON array

- [php/update_status.php](php/update_status.php)
  - Method: `POST` (`application/json`)
  - Body:
    ```json
    { "id": 42, "field": "resolved", "manage_pin": "1234" }
    ```
    - `field`: `resolved` or `owner_met`
  - Response: `200` on success, `403` for wrong PIN, `400/500` on error

- [php/announcements.php](php/announcements.php)
  - Method: `GET`
  - Returns active announcements

- [php/reviews.php](php/reviews.php)
  - Methods: `GET` (list), `POST` (submit)
  - `POST` body example:
    ```json
    { "name": "Sam", "rating": 5, "comment": "Great tool!" }
    ```

---

## Database Notes

Expected tables (indicative):
- `items`: `id`, `title`, `description`, `category`, `location`, `date_lost`, `image_url`, `contact_name`, `contact_phone`, `type`, `manage_pin`, `resolved` (TINYINT), `owner_met` (TINYINT), `status` (e.g., `approved`), `created_at` (TIMESTAMP)
- `announcements`: `id`, `title`, `message`, `image_url`, `active`, `created_at`
- `reviews`: `id`, `name`, `rating`, `comment`, `created_at`

Adjust column names/types to match your deployment; see the PHP files for exact expectations.

---

## Security & Configuration

- Do not commit `php/db_config.php` with credentials. Keep it local or use environment variables.
- Image uploads go to [php/uploads](php/uploads) and should be protected on production (file type checks, size limits).
- Consider adding rate limits and basic auth to admin endpoints if exposed.

---

## Deployment

- Any shared host with PHP/MySQL will work.
- For InfinityFree or similar, configure `php/db_config.php` with production credentials or set environment variables from your host panel.

---

## License

See [LICENSE](LICENSE).

---

## Contributing

Issues and PRs are welcome. Please include clear steps to reproduce and proposed changes.
