# Project Overview

This project is a premium salon booking website named "Hair Cutting Hub". It is built with PHP, HTML, CSS, and JavaScript. The website features a modern, responsive design with a glassmorphism UI and premium themes. It includes an advanced booking system, an admin dashboard for managing bookings, and a customer authentication system.

The project does not use a major PHP framework like Laravel or Symfony. It is a custom-built application with a focus on front-end aesthetics and user experience.

## Building and Running

There is no formal build process for this project. To run the project locally, you can use a local web server that supports PHP.

**Using PHP's built-in server:**

```bash
php -S localhost:8000
```

**Using Python's built-in server (for static files only):**

```bash
python -m http.server 8000
```

**Using Node.js `live-server`:**

```bash
npx live-server
```

Then, open `http://localhost:8000` in your browser.

## Development Conventions

*   **Database:** The project uses a MySQL database. The schema is defined in `schema.sql`. Database connection is handled by `db_connect.php`, which uses PDO and prioritizes environment variables for credentials.
*   **API:** The backend functionality is exposed through a set of API scripts (e.g., `api_add_booking.php`, `api_get_bookings.php`). These scripts are responsible for interacting with the database and returning JSON responses.
*   **Theming:** The project has a dynamic theming system that allows users to switch between different color schemes. The themes are defined in CSS files in the `themes` directory and applied using JavaScript.
*   **Authentication:** The website has a simple authentication system. The `README.md` file states that any email containing "admin" can be used to log in as an admin.
*   **JavaScript:** The project uses GSAP for animations and vanilla JavaScript for other client-side functionality.
*   **CSS:** The project uses modern CSS features like CSS Grid, Flexbox, and CSS Variables. The styles are well-organized into separate files for different components (e.g., `styles.css`, `admin-styles.css`, `auth-styles.css`).
