# Skate Club Interactive Screen Application

**Version:** 1.0.0  
**Author:** Mir M  
**Text Domain:** skate-club-screen  

## Overview

The **Skate Club Interactive Screen Application** is a WordPress plugin designed to power an interactive screen experience for a skate club. It facilitates user engagement through QR code-based activities, real-time displays, and administrative management tools.

Key features include:
- **Session Management**: Manage skating sessions with date and time tracking.
- **Song Requests**: Allow users to search and request songs.
- **Song Voting**: Users can vote on approved song requests.
- **Media Uploads**: Users can upload photos and videos, which require admin approval before being displayed.
- **Spinner Wheel**: A gamified spinner wheel module for prizes or activities.
- **Raffle Draw**: A digital raffle system for selecting winners from entries.
- **Live Display**: A dedicated full-screen display mode for the venue screen.

## Requirements

- **PHP**: 7.4 or higher
- **WordPress**: 5.8 or higher
- **Composer**: Dependencies are managed via Composer.

## Installation

1.  Clone or place the plugin folder `skate-club-screen-app` into `wp-content/plugins/`.
2.  Run `composer install` within the plugin directory to install dependencies (if `vendor` is missing).
3.  Activate the plugin via the WordPress Admin Dashboard.

## Project Structure

```text
skate-club-screen-app/
├── admin/                  # Admin area functionality (controllers, pages, assets)
│   ├── assets/             # Admin CSS and JS
│   ├── pages/              # Admin page templates (Session, Media, Spinner, etc.)
│   ├── class-admin-menu.php # Admin menu definitions
│   └── class-admin-ajax.php # Admin-side AJAX handlers
├── includes/               # Core business logic and classes
│   ├── modules/            # Feature modules (Song Request, Media Upload, etc.)
│   ├── class-skate-club-core.php # Main plugin loader
│   ├── class-access-control.php  # Dashboard access restriction logic
│   ├── class-database.php        # Custom table management
│   ├── class-security.php        # Security utilities
│   └── ...
├── public/                 # Public-facing functionality
│   ├── assets/             # Frontend CSS and JS
│   ├── class-public-display.php # Screen display rendering
│   └── class-form-handler.php   # Public form handling
├── templates/              # PHP templates for frontend views and forms
├── skate-club-screen-app.php # Main plugin file
└── README.md               # This documentation
```

## Dashboard Access Control

This plugin implements custom dashboard access control in `includes/class-access-control.php`.

- **Restricted Access**: By default, **ALL** WordPress admin menus are hidden for all users.
- **Allowed Users**: Only the following users have full access to the dashboard:
    - User/Login: `[Super Admin Username]`
    - Email: `[superadmin@example.com]`
- **Limited Access**: For all other users (who have permissions to access the dashboard), **ONLY** the "Skate Club" menu is visible. All standard WordPress menus (Dashboard, Posts, Settings, etc.) are hidden.

## Key Modules

### 1. Session Management
- **Table**: `wp_skate_sessions`
- Allows creating, activating, and archiving sessions. All data (requests, votes, uploads) is linked to a specific session.

### 2. Media System
- **Table**: `wp_skate_media_uploads`
- Files are uploaded to `wp-content/uploads/skate-club-media/`.
- **Approval Workflow**: Uploads are `pending` by default. Admins approve/reject them via the "Media Approval" page.

### 3. Song Requests & Voting
- **Tables**: `wp_skate_song_requests`, `wp_skate_voting_songs`
- Users submit requests. Admins can move requests to a "Voting List".
- Users vote on the voting list.

### 4. Interactive Display
- **URL**: `/skate-club-screen-display/` (rewritten URL).
- Displays a rotating view of approved media, active song votes, and announcements.

## Development

### Adding a New Module
1.  Create a class in `includes/modules/`.
2.  Register the module in `includes/class-skate-club-core.php` inside `load_dependencies`.
3.  Add any necessary admin pages in `admin/pages/`.
4.  Add AJAX handlers in `admin/class-admin-ajax.php` or `public/class-ajax-handler.php`.

### AJAX & Security
- All AJAX actions are prefixed with `skate_`.
- Nonces are required for all form submissions and AJAX calls.
- Use `Skate_Club_Security` class for validation and sanitization helper methods.

## Support

For support or questions, contact the author at [https://mirm.pro/](https://mirm.pro/).
