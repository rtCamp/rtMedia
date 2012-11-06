=== BuddyPress Media ===
Contributors: rtcamp, rahul286, gagan0123, umeshnevase
Donate link: http://rtcamp.com/donate
Tags: BuddyPress, media, multimedia, album, audio, songs, music, video, photo, image, upload, share, MediaElement.js, ffmpeg, kaltura, media-node
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.3.2
Tested up to: 3.4.2
Stable tag: 2.2.2

Adds Photos, Music, Videos & Albums to your BuddyPress. Supports mobile devices (iPhone/iPad, etc) and automatic audio/video conversion.

== Description ==

BuddyPress Media adds Photos, Music, Videos & Albums to your BuddyPress. Supports mobile devices (iPhone/iPad, etc) and automatic audio/video conversion.

= Features =

* Images, Music, Videos Upload
* User-Albums Support
* Multiple files upload with Drag-n-Drop
* Uploading Photos/Videos via mobile (Tested on iPhone running iOS6)
* HTML5 player (with fall back to flash/silverlight player support)
* Automatic conversion of common audio & video formats to mp3/mp4. via [Kaltura Add-On](http://rtcamp.com/store/buddypress-media-kaltura/ "BuddyPress Media Kaltura Addon for Kaltura.com/Kaltura-CE/Kaltura On-Prem version") and [FFMPEG Add-On](http://rtcamp.com/store/buddypress-media-ffmpeg-converter/ "BuddyPress Media FFMPEG Addon")

= Roadmap =

* Group Albums
* Activity-update form media upload
* Paid membership plans, i.e. "Upload Quota" for buddypress members  (in planning stage).

= Demo =
* [BuddyPress-Media Demo](http://demo.rtcamp.com/buddypress-media) (Stand-alone)
* [BuddyPress-Media + Kaltura Add-on](http://demo.rtcamp.com/bpm-kaltura)
* [BuddyPress-Media + FFMPEG Add-on](http://demo.rtcamp.com/bpm-ffmpeg)

== Installation ==

= BuddyPress Media Plugin =

* Install the plugin from the 'Plugins' section in your dashboard (Go to `Plugins > Add New > Search` and search for BuddyPress Media).
* Alternatively, you can [download](http://downloads.wordpress.org/plugin/buddypress-media.zip "Download BuddyPress Media") the plugin from the repository. Unzip it and upload it to the plugins folder of your WordPress installation (`wp-content/plugins/` directory of your WordPress installation).
* Activate it through the 'Plugins' section.

= BuddyPress Media Add-ons =

[**BuddyPress-Media Kaltura addon**](http://rtcamp.com/store/buddypress-media-kaltura/ "BuddyPress Media Kaltura Addon for Kaltura.com/Kaltura-CE/Kaltura On-Prem version")

* It also supports many video formats including *.avi, *.mkv, *.asf, *.flv, *.wmv, *.rm, *.mpg.
* You can use Kaltura.com/Kaltura On-Prem or self-hosted Kaltura-CE server with this.

You can purchase it from [here](http://rtcamp.com/store/buddypress-media-kaltura/ "BuddyPress Media Kaltura Addon for Kaltura.com/Kaltura-CE/Kaltura On-Prem version")

--

[**BuddyPress-Media FFMPEG addon**](http://rtcamp.com/store/buddypress-media-ffmpeg-converter/ "BuddyPress Media FFMPEG Addon").

* It also supports many video formats including *.avi, *.mkv, *.asf, *.flv, *.wmv, *.rm, *.mpg.
* It also supports many audio formats including *.mp3, *.ogg, *.wav, *.aac, *.m4a, *.wma.

You can purchase it from [here](http://rtcamp.com/store/buddypress-media-ffmpeg-converter/ "BuddyPress Media FFMPEG Addon").


== Frequently Asked Questions ==

Please visit [BuddyPress Media's FAQ page](http://rtcamp.com/buddypress-media/faq/ "Visit BuddyPress Media's FAQ page").

== Screenshots ==

Please visit [BuddyPress Media's Features page](http://rtcamp.com/buddypress-media/features/ "Visit BuddyPress Media's Features page").

== Changelog ==

Please visit [BuddyPress Media's Roadmap page](http://rtcamp.com/buddypress-media/roadmap/ "Visit BuddyPress Media's Features page") to get some details about fuure releases.

= 2.2.2 =
Fixed the Notice that was generated on the albums page

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
Added album support for user-profile. Important update with plenty of new features.
