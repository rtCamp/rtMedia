# rtMedia E2E Tests

This directory contains Playwright E2E tests for the rtMedia plugin, ensuring core functionality and integrations work as expected.

## Test Structure & File Descriptions

### Core Settings
- **`prerequisite.spec.js`**:
  - **Purpose**: Prepares the environment for testing.
  - **Actions**: Enables essential settings like direct upload, media types (Photo, Video, Music), BuddyPress integration, and comments.
- **`cleanup.spec.js`**:
  - **Purpose**: Resets the environment after tests.
  - **Actions**: Disables all settings enabled during the test suite to leave the site in a clean state.
- **`media_size.spec.js`**:
  - **Purpose**: Verifies media dimension settings.
  - **Actions**: Sets custom sizes for photos and videos in the backend and validates them on the frontend (thumbnails, activity player, single player).
- **`types.spec.js`**:
  - **Purpose**: Validates file type restrictions.
  - **Actions**: Disables specific media types (Photo, Video, Music) and confirms that uploading restricted types triggers the correct error messages.

### BuddyPress Integration (`buddypress/`)
- **`01_integrated-features.spec.js`**:
  - **Purpose**: Tests general BuddyPress integration points.
  - **Actions**: Checks media tabs in profiles and groups, upload buttons in activity streams, and activity creation for comments and likes.
- **`02_comment_media.spec.js`**:
  - **Purpose**: Tests media attachments in comments.
  - **Actions**: Verifies the ability to upload media within BuddyPress activity comments.
- **`03_album_settings.spec.js`**:
  - **Purpose**: Tests album functionality.
  - **Actions**: Enables albums and verifies the "Albums" tab appears in the user profile.

### Display Features (`display/`)
- **`01_single-media-view.spec.js`**:
  - **Purpose**: Tests frontend display options.
  - **Actions**: Validates the media search bar, lightbox functionality, pagination (load more/numbers), and masonry layout.

### Other Settings (`other_settings/`)
- **`01_admin-settings.spec.js`**:
  - **Purpose**: Tests admin bar integration.
  - **Actions**: Verifies the rtMedia menu appears in the WordPress admin bar.
- **`02_user_terms.spec.js`**:
  - **Purpose**: Tests Terms of Service compliance.
  - **Actions**: Configures TOS links and messages, and verifies users must accept terms before uploading.
- **`03_footer_link.spec.js`**:
  - **Purpose**: Tests the footer credit link.
  - **Actions**: Checks if the rtMedia footer link is displayed and points to the correct URL.
- **`custom_css.spec.js`**:
  - **Purpose**: Tests custom CSS injection.
  - **Actions**: Applies custom CSS in settings and verifies it is rendered in the frontend source.
- **`privacy.spec.js`**:
  - **Purpose**: Tests privacy controls.
  - **Actions**: Enables privacy settings and checks for the privacy dropdown selector on the activity upload form.

## Running Tests

Ensure you have the necessary environment set up and run the tests using your test runner command (i.e., `npm run test-e2e:playwright:dev`).
