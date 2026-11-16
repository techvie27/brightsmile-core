# BrightSmile Core

Plugin for the BrightSmile dental clinic website. It holds the clinic's content types, the booking form and the REST routes. The look of the site lives in the separate `brightsmile-theme` repo.

## What it does

- Services (treatments) with a service type and a price field
- Doctors with speciality and experience fields
- Appointment booking form with the shortcode `[brightsmile_appointment_form]`
- Private appointments, shown only in the "Appointments" admin page
- Dental tip box with the shortcode `[brightsmile_dental_tip]`
- REST routes under `/wp-json/brightsmile/v1/`

## REST routes

| Route | Who can read it |
|---|---|
| `/services` | Everyone |
| `/appointments` | Administrators only (401 when logged out) |

## Roles

On activation the plugin creates the **Receptionist** role. Receptionists and administrators can open the Appointments page.

## Requirements

- WordPress with PHP 8.3
- Advanced Custom Fields (field groups are stored in `acf-json/`)

## Local development

1. Put the folder in `wp-content/plugins/`.
2. Run `composer install`.
3. Check the code style before every commit:

   `./vendor/bin/phpcs --standard=WordPress -n --ignore=vendor .`

Rules for contributing are in `CONTRIBUTING.md`.

## Deploy

Every change goes through a Pull Request. CI runs `php -l` and PHPCS. When the PR is merged to `main`, the deploy job uploads the plugin to the Hostinger server over SSH. The server details are stored as GitHub Secrets, never in the repo.

If a release changes the activation code (roles or permalinks), deactivate and activate the plugin once on the live site.