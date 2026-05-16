# Changelog

All notable changes to the "Skate Club Interactive Screen Application" plugin will be documented in this file.

## [1.1.0] - 2026-03-30
### Added
- Added a beautifully customized "Waiting Room" page with automatic refresh polling for frontend forms when an event is closed.
- Placed the upload media QR code natively inside the first slot of both photo and video gallery grids for persistent scanning visibility.

### Changed
- Re-architected QR codes and form routing to use unified, clean URLs instead of session ID hashing.
- Forms now dynamically resolve the active session behind the scenes, ensuring the same scannable QR code remains valid forever across endless events.
- Removed redundant Upload Media QR block from the display headers.

### Fixed
- Fixed an issue where static form URLs were aggressively cached by browsers, causing users to see old session names after a new session was activated.

## [1.0.1] - 2026-01-31
### Added
- Added `CHANGELOG.md` to track project updates.
- Added responsive design to all public forms (Song Request, Voting, Raffle, Media Upload) for better mobile experience.
- Added media queries and global scrolling support in `forms.css` to fix touch screen scrolling issues.
- Added privacy policy consent checkbox to all forms.

### Changed
- Removed personal information fields (Email, Phone, Date of Birth) from Song Request form.
- Removed personal information fields (Email, Phone) from Raffle Entry form.
- Removed personal information fields (Email, Phone) from Song Voting form.
- Updated backend validation logic to optionally allow missing email/phone/dob fields while maintaining security via session IDs and browser fingerprints.
- Disabled WordPress Admin Bar on the frontend for all users to improve display experience.

### Fixed
- Fixed scrolling issues on touch devices by enabling `overflow-y: auto` on body and removing fixed height constraints.
