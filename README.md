# group members:
    John Nikolai Camarillo
    Gabriel Jee Buena
    Alfred Angelo Bisas



# GearHub Website

GearHub is a PHP + MySQL ecommerce-style website for ordering gaming peripherals such as keyboards, mice, and headphones.

## Tech Stack

- PHP (procedural pages + shared includes)
- MySQL (via `mysqli`)
- Composer dependencies:
	- `phpmailer/phpmailer`
	- `vlucas/phpdotenv`

## Local Setup (XAMPP)

1. Place the project inside `htdocs`.
2. Create/import the database used by `configurations/database_settings.php`.
3. Install dependencies:

```bash
composer install
```

4. Create a `.env` file (see `.env.example`) for email sending.
5. Start Apache and MySQL in XAMPP.
6. Open the site at `http://localhost/Website`.

## Project Layout

- `configurations/`: DB config, auth helpers, logging helpers
- `includes/`: shared layout parts (navigation, header, footer)
- `processes/`: POST handlers and update actions
- `check-out/`: checkout route/page
- `css/`: stylesheets
- `javascript/`: client-side scripts
- `Assets/`: images and icons

## Notes

- URL generation for shared includes is centralized in `configurations/url_helpers.php`.
- Checkout and cart updates are handled through `processes/` endpoints.
