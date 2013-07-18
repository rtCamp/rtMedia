=== rtMedia for WordPress, BuddyPress and bbPress ===
Contributors: rtcamp, rahul286, gagan0123, saurabhshukla, JoshuaAbenazer, faishal, desaiuditd, nitun.lanjewar, umesh.nevase, suhasgirgaonkar, neerukoul, hrishiv90
Donate link: http://rtcamp.com/donate
Tags: BuddyPress, media, multimedia, album, audio, songs, music, video, photo, image, upload, share, MediaElement.js, ffmpeg, kaltura, media-node, rtMedia, WordPress, bbPress
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: WordPress 3.5
Tested up to: WordPress 3.5.2 + BuddyPress 1.7
Stable tag: 3.0.6

rtMedia adds albums, audio/video encoding, privacy/sharing, front-end uploads & more. All this works nicely on mobile/tablets devices.

== Description ==

rtMedia is an all-in-one media solution for WordPress, BuddyPress and bbPress. It extends existing media features as well as adds many others for itself, its addons and other themes/plugins.

Built with a mobile-first philosophy, it works on mobile devices (like iPhone/iPad, Android, BlackBerry, Windows Mobile, etc) and comes with automatic audio/video conversion among other features *(see list below)*.

= Live Demos =

In case you are in hurry, you can skip the long list of features in subsequent sections and just explore live demos! :-)

* [rtMedia Demo](http://demo.rtcamp.com/buddypress-media/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media) (includes [Instagram-effects](http://rtcamp.com/store/buddypress-media-instagram/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media) and [Photo-tagging](http://rtcamp.com/store/buddypress-media-photo-tagging/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media) addon. [Audio/Video conversion service](http://rtcamp.com/buddypress-media/addons/audio-video-encoding-service/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media ))
* [rtMedia with Kaltura Add-on](http://demo.rtcamp.com/bpm-kaltura/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media) (Uses Kaltura.com account for video conversion)
* [rtMedia with FFMPEG Add-on](http://demo.rtcamp.com/bpm-media/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media) (Uses FFMPEG-based video conversion)


= Core Concepts =

rtMedia at its core consists of 2 concepts - rtMedia uploader and rtMedia gallery.

**#1. rtMedia Uploader**

 * Use `[rtmedia_uploader]` shortcode or `<?php rtmedia_uploader() ?>` template tag, to show drag-n-drop uploader in any WordPress area (post, page, custom-post, etc).
 * All uploads integrated with the *context*. Context can be BuddyPress profiles/groups, WordPress posts/pages, custom post types or another plugin.
 * Mostly rtMedia tries to *guess* context for WordPress, BuddyPress & bbPress areas. For other plugin, it provides API to define context.

**#2. rtMedia Gallery**

 * Display media gallery anywhere on your site using `[rtmedia_gallery]` shortcode or `<?php rtmedia_gallery ?>` template tag.
 * In most cases, gallery can be accessed by simply appending `/media` in the end of a WordPress URL. If it's a valid context, media uploaded from rtMedia Uploader will show up automatically!

= Key Features *(Free ones)* =

**WordPress Integration**

 * Display media on WordPress author pages (eg: `http://example.com/author/admin/media/`)
 * Media Attachment for WordPress comments on posts/pages *(coming soon)*

**BuddyPress Integration**

 * Adds media tab to BuddyPress Profiles and Groups.
 * Attach media to activity status updates.
 * Create activity on uploads and sync comments on them with WordPress comments.
 * Works even if BuddyPress activity is disabled.

**bbPress Integration**

 * bbPress profile integration
 * Attachment support for topics and replies (coming soon)

**Albums**

 * Albums are used to organise media. Since rtMedia 3.0, you can create albums even if BuddyPress is not present.
 * Apart from creation of albums, moving media between albums and merging albums is also supported.
 * Global albums can be used to define preset albums. "Wall Posts" is an example of global album.
 * Option to disable albums (just in case you don't like them!)

**Responsive**

 * Lightbox/Album Slideshow works on mobiles & tablets.
 * Video player resizing is also supported.
 * Swipe gestures (coming soon)

**Privacy**

 * Allows different privacy levels for each media
 * *Bonus* Allows true privacy on regular BuddyPress activities
 * Privacy works with BuddyPress friends disabled. Also works with standalone WordPress.

**Template system**

 * Completely customise rtMedia by modifying the template files. Just copy over the template folder to your theme.

**Other Features**

 * [Featured Media](http://rtcamp.com/buddypress-media/docs/admin/featured-media/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "Featured Media documentation") - Using a template tag, this can be then displayed on the user profile as a cover photo/video.


= Premium Features =

**Audio/Video Conversion**

rtMedia has 3 premium solutions to take care of audio/video conversion.

 * [Audio/Video Encoding Subscription Service](http://rtcamp.com/buddypress-media/addons/audio-video-encoding-service/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "rtMedia FFMPEG Addon") - Monthly subscription service. Easiest to setup.
 * [FFMPEG-Addon](http://rtcamp.com/store/buddypress-media-ffmpeg-converter/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "rtMedia FFMPEG Addon") - Requires FFMPEG & Media-Node installed on a VPS/Dedicated server.
 * [Kaltura-Addon](http://rtcamp.com/store/buddypress-media-kaltura/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "rtMedia Kaltura Addon for Kaltura.com/Kaltura-CE/Kaltura On-Prem version") - Rquries a Kaltura.com account or Kaltura-CE or Kaltura-on-Prem server.

If all your music files is mp3 formats and videos in mp4 formats, you may not need any of above add-ons.

**Images Addons**

* [Instagram-Effects](http://rtcamp.com/store/buddypress-media-instagram/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "rtMedia FFMPEG Addon"): User can apply Instagram like filters to photos.
* [Photo-Tagging](http://rtcamp.com/store/buddypress-media-photo-tagging/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "rtMedia FFMPEG Addon"): Users can tag their friends/other users in photos.

**Coming Soon - Premium Addons on the way**

* **Watermark Addon** - Adds advanced, customised watermark text to photos. Supports Google Fonts. *(Development completed. Under testing)*
* **Membership Addon** - Users can be given controlled upload quotas and media type access on their profiles and groups. *(planning stage)*


= Roadmap =

* For latest update, check [rtMedia's Roadmap page](http://rtcamp.com/buddypress-media/roadmap/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "Visit rtMedia's Roadmap page")

= GitHub Code =

* [Fork rtMedia on **GitHub**](http://github.com/rtCamp/rtMedia/)
* We are accepting pull requests on Github.
* For translations, please do NOT use Github. Instead use [this GlotPress project](http://rtcamp.com/translate/projects/rtmedia?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media)
* Please do not use GitHub for support requests.

= Support =

**Important:** Please provide a **URL** of the site/web page when requesting support.

We only provide support on our [free support forum](http://rtcamp.com/groups/buddypress-media/forum/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media).

== Installation ==

* Install the plugin from the 'Plugins' section in your dashboard (Go to `Plugins > Add New > Search` and search for rtMedia).
* Alternatively, you can [download](http://downloads.wordpress.org/plugin/buddypress-media.zip "Download rtMedia") the plugin from the repository. Unzip it and upload it to the plugins folder of your WordPress installation (`wp-content/plugins/` directory of your WordPress installation).
* Activate it through the 'Plugins' section.

== Frequently Asked Questions ==

Please visit [rtMedia's FAQ page](http://rtcamp.com/buddypress-media/faq/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "Visit rtMedia's FAQ page").

Read rtMedia [Documentation](http://rtcamp.com/buddypress-media/docs/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media)

== Screenshots ==

Please visit [rtMedia's Features page](http://rtcamp.com/buddypress-media/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "Visit rtMedia's Features page").

1. Media Settings
2. Privacy Settings
3. Builtin Support
4. Media View
5. Album View
6. Uploader
7. User Privacy Settings
8. Single Media View
9. Media Edit View
10. Media Activity
11. Media Widget
12. Lightbox

== Changelog ==

Please visit [rtMedia's Roadmap page](http://rtcamp.com/buddypress-media/roadmap/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media "Visit rtMedia's Features page") to get some details about future releases.

= 3.0.6 =
* Fixed image size issue
* Fixed group create album permission and add setting for group admin
* Fixed Activity Delete issue
* Fixed Delete redirection
* Fixed template url issue for backbone
* Fixed photo tagging lightbox issue
* Other minor bug fixes

= 3.0.5 =
* Fixed privacy issue in media
* Fixed activity media upload issue
* Fixed encoding download issue
* Remove grid from gallery and activity for use defined size
* Fixed template issue
* Other minor bug fixes

= 3.0.4 =
* Handle delete media and activity
* Add like button count
* Hide buddypress tab if not active
* Fixed https problem in plugin update
* Fixed image size issue
* Fixed privacy issue
* Other minor bug fixes

= 3.0.3 =
* Improved template system
* Fixed bug with audio/video uploads
* Fixed bug with featured media
* Fixed activity duplication issue
* Other minor bug fixes

= 3.0.2 =
* Legacy code added for Addon Updates
* Added album enable /disable option
* Fixed lightbox option
* Other minor bug fixes

= 3.0.1 =
* Legacy path support for Addon Updates
* Added database check for migration
* Fixed a few errors

= 3.0 =
* Renamed to rtMedia for WordPress, BuddyPress and bbPress
* Adds Anywhere uploader
* Adds Anywhere media
* Author page integration (in the absence of BuddyPress)
* Fixes lightbox
* Fixes comments and media actions in the absence of activities

= 2.13.2 =
* Adds parameter to include/exclude media title in shortcode
* Resolved admin menu warnings for members

= 2.13.1 =
* Fixes bug in navigation
* Fixes bug in admin menu
* Resolves delete album issue (when activity is enabled)
* Adds option to disable encoding
* Translations Updated

= 2.13 =
* Adds support for audio/video conversion via rtCamp's Encoding Service

= 2.12.1 =
* Fixes bug in featured media that occurred when the featured media was deleted
* Optimises db queries for privacy

= 2.12 =
* Lets users add a featured image/video/audio for their profiles. Can be used to create "Cover Photos" or videos for each user using a template tag.
* Feature sponsored by [Henry Wright](http://profiles.wordpress.org/henrywright-1)

= 2.11 =
* Added media editing, deleting and other actions when activity stream is disabled
* Fixes comment posting on lightbox

= 2.10.3 =
* Removes lightbox from mobile devices
* Fixes a few bugs related to notifications

= 2.10.2 =
* Fixes a [bug in admin](https://github.com/rtCamp/buddypress-media/issues/264)
* Fixes text-domain issues
* Adds framework for notifications
* Adds support for Tagging Addon
* Updated translations, especially German and Persian

= 2.10.1 =
* Fixes bug in shortcode

= 2.10 =
* Adds album management options (Merge/Move/Delete)
* Adds shortcode to display media [bpmedia]
* Adds localization to JS
* Added partial Arabic and Persion translations

= 2.9 =
* Adds options to specify Media Sizes
* Adds options to modify the image ( Crop, Rotate, Flip & Scale )
* Creates only required image sizes ( Rather than all registered image sizes )
* Adds thickbox to BuddyPress Media Widget
* Fixes bug in js
* Adds framework for shortcode support ( Functionality will be added in the next release )

= 2.8.1 =
* Improved i18n support, thanks to [David Decker](http://profiles.wordpress.org/daveshine/)
* Updated translations

= 2.8 =
* Adds importer for BP Album
* Tested with [reallifescrapped](http://reallifescrapped.com), an aswesome social network for scrapbookers by [Meg](http://profiles.wordpress.org/oceanwidedesigns)
* Other minor bug fixes, especially for Groups

= 2.7.6 =
* Fixes errors due to absence of EXIF
* Fixes duplicate comment box on lightbox
* Fixes multimedia display on single media view
* Rewrites activity uploader to fix a lot of issues with themes

= 2.7.5 =
* Fixes image rotation for PHP < 5.3 that caused upload failure

= 2.7.4 =
* Added french translation
* Fixed Widget privacy

= 2.7.3 =
* Added option to toggle lightbox functionality to prevent theme conflicts
* Fixed conflict with Bottstrap based themes
* Minor code revision

= 2.7.2 =
* Fixes warning related to scalar variables
* Improves Group Wall Post handling
* Adds lightbox to activity media
* Fixes mediaelement display
* Implements forced download for media
* Fixes image rotation on upload
* Fixes broken spinner image
* Fixes some styling

= 2.7.1 =
* Fixes bug related to group ids.
* Fixes bug with stylesheet loading
* Adds ajax loader to lightbox
* Revamped uploader UI

= 2.7 =
* Added activity uploader
* Added lightbox
* Fixed bug in friends' privacy
* Fixed bug due to which edit/delete buttons would show up
* Refactored code and styling

= 2.6.7 =
* Fixes modular compatibility with friends component. Thanks to [Cat555](http://rtcamp.com/support/users/cat555/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media) for reporting this.
* Fixes modular compatibility with friends component.
* Fixes default privacy levels.
* Minor Admin UI revisions.
* Fixes load more on albums.

= 2.6.6 =
* Fixes automatic count update.
* Fixes other bugs in Privacy Update.

= 2.6.5 =
* Fixed all warnings and errors with 2.6 thanks to [dzapata79](http://wordpress.org/support/profile/dzapata79), [Scott](http://wordpress.org/support/profile/davidsons).
* Special thanks are due to [Stephan Oberlander](http://cg-creatives.com) who let us use his site for testing and reproducing the errors!

= 2.6.4 =
* Improved group compatibility

= 2.6.3 =
* Refactored database update checker to fix bugs

= 2.6.2 =
* Fixed database upgrade issues due to js and css caching

= 2.6.1 =
* Fixed warnings that appeared on non-upgrade of database.

= 2.6 =
* Added Privacy for Media.
* Added crude support for activity privacy (due for revision)
* Revised media count functionality
* Fixed widget to recognise enabled/disabled media types
* Renamed 'Featured' to 'Set as Album Cover' in the UI for clarity
* Fixed featured functionality
* Fixed download functionality
* Rewritten query functions
* Improved css and js
* Major code refactoring

= 2.5.5 =
* Fixes thumbnail appearance and height issues with some themes.
* Other minor UI changes

= 2.5.4 =
* Added option to enable/disable BuddyPress Media on Groups. (Profile toggle, coming soon)
* Added Polish language.
* Media tabs display now responds to admin settings
* Improved Uploader UI.
* Improved settings screen.
* More code comments and documentation added.
* Fixed gallery responsiveness.
* A few bug fixes.

= 2.5.3 =
* Added option to toggle BuddyPress Media menu in admin bar
* Added incomplete translations for German, Italian, French and Dutch languages
* A few bug fixes.

= 2.5.2 =
* Fixes warning on admin side.

= 2.5.1 =
* Fixed bug where when a user visits another member's media tab when groups are inactive, they'd get an error.
* Improved long album title and count display.

= 2.5 =
* Bug fixes for admin notices on multisite installs.
* Bug fixes for activity on multiple uploads.
* Updated upload UI. Now uploads are possible from all tabs.
* Fixed translation readiness.
* Added Brazilian Portuguese, Spanish and Japanese languages.
* Added Album renaming and deleting functionality.

= 2.4.3 =
* Fixed latest activity formatting.
* Added auto-update for add-ons.
* Made minor changes for add-on compatibility.

= 2.4.2 =
* Fixed bug where settings weren't getting saved on multisites.
* Workaround for bug where the last activity wouldn't show up.
* Fixed bug with iOS uploads.
* Some minor code changes

= 2.4.1 =
* New Widget added with more options!
* Fixed 'Show More' action on Group Album thanks to [bowoolley](http://profiles.wordpress.org/bowoolley/)
* Fixed conflicts with 'BuddyPress Activity Plus', thanks to [number_6](http://profiles.wordpress.org/number_6/) and [param-veer](https://github.com/param-veer)
* Some more housekeeping, code cleanup and documentation.

= 2.4 =
* Total code overhaul. Fixed a lot of bugs and optimised a lot of other code.
* Added proper translation support!
* Removed extra jQuery UI scripts and styles, for speed and optimisation

= 2.3.2 =
* Album creation on a single file upload. Thanks to [Josh Levinson](http://profiles.wordpress.org/joshlevinson/) for providing the fix.
* Fixed Version number constant.

= 2.3.1 =
* Default permission for album creation in groups set to admin.
* Fixed the warning on the "New Post" about the MYSQL query.

= 2.3 =
* Groups Media feature added
* Featured image selection in albums

= 2.2.8 =
* Fixed some screen functions

= 2.2.7 =
* Fixed the "Upgrade" button issue

= 2.2.6  =
* Fixed the Multisite issue for the options page.

= 2.2.5 =
* Fixed a bug in upgrade script

= 2.2.4 =
* Added support for media-count on albums
* fixes bbPress conflict in_array() expects parameter 2

= 2.2.3 =
* Added more verification to check whether the object being used is available or not.
* Added custom message on delete activity action.
* Modified the upgrade loop to handle the sites with large number of media files.

= 2.2.2 =
* Fixed the Notice that was generated on the albums page.

= 2.2.1 =
* Removed anonymous function since its not supported in PHP versions < 5.3

= 2.2 =
* Album Support for Users
* Ajaxified pagination to make it easy to view large albums.
* Multiple file uploads with progress bar
* Easy access to the backend admin-options
* Admin-option to disable download button below media files.

= 2.1.5 =
* Fixed the postmeta box bug

= 2.1.4 =
* Added video thumbnail support for addons.
* Updated the MediaElementJS player library.

= 2.1.3 =
* Fixed file uploading via iPhone.

= 2.1.2 =
* Changed some default values and normalized all files with end of file as line feed only

= 2.1.1 =
* Some changes in readme file

= 2.1 =
* Added necessary hooks & filters to support buddypress-media add-on creation.
* Support for video format added including *.avi, *.mkv, *.asf, *.flv, *.wmv, *.rm, *.mpg.
* Support for audio format added including *.mp3, *.ogg, *.wav, *.aac, *.m4a, *.wma.

= 2.0.4 =
* Added remaining modules of getID3 php library
* Added checking for MP3 filetype and its content before uploading

= 2.0.3 =
* Added a few filters and actions for addon support
* Fixed the short open tag bug

= 2.0.2 =
* Delete functionality fixed
* Edit functionality for Media Title and Media Description
* Admins can manage which media types to allow

= 2.0.1 =
* Replaced codec finding library
* Fixed warning on activities page

= 2.0 =
* Integration into BuddyPress Activities
* HTML5 Audio Tag Support (with fallback)
* HTML5 Video Tag Support (with fallback)

== Upgrade Notice ==

= 3.0.6 =
Fixed image size creation isssue,create album permission,Activity Delete, Delete redirection,photo tagging lightbox issues.

== Sponsors ==

* *[Henry Wright](http://profiles.wordpress.org/henrywright-1)* has kindly sponsored the *Featured Media* feature.
* 优素映像 (Yousu Image) has sponsored the latest *Like* feature which doesn't depend on BuddyPress, any more.


== Translation ==

rtMedia includes [full translation support](https://rtcamp.com/tutorials/buddypress-media-translation/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media). Head over to the [translation project](http://rtcamp.com/translate/projects/buddypress-media/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media) to contribute your translations. If you don't see the language of your choice, let us know in the support forum, we'll add it.

* [Persian](https://rtcamp.com/translate/projects/buddypress-media/fa/default) translation by [mahdiar](http://profiles.wordpress.org/mahdiar/)
* [Spanish](https://rtcamp.com/translate/projects/buddypress-media/es/default) translation by [Andrés Felipe](http://profiles.wordpress.org/naturalworldstm/)
* [German](https://rtcamp.com/translate/projects/buddypress-media/de/default) translation by [hannes.muc]

(**Note**: Credits are given for translations that are at least 50% complete.)

== Credits ==

rtMedia uses the following projects/sources for some functionality

* [MediaElement.js](http://mediaelementjs.com/) for html5 audio/video player
* [Maginific Popup](http://dimsemenov.com/plugins/magnific-popup/) for responsive lightbox
* [getID3](http://getid3.sourceforge.net/) gets us some ID tags for the media
* [Foundation](http://foundation.zurb.com/) for the media grid and layout
* [Backbone.js](http://backbonejs.org/) for an MVC architecture for the frontend
