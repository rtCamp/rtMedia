=== BuddyPress Media Component ===
Contributors: rtcamp
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9488824
Tags: BuddyPress, media, multimedia, audio, video, photo, images, upload, share, MediaElement.js, ffmpeg, kaltura
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.3.2
Tested up to: 3.4.2
Stable tag: 2.1.1

Adds multimedia features to your BuddyPress based social network. Support mobile devices.& audio/video conversion.

== Description ==

BuddyPress Media Component adds multimedia features to your BuddyPress based social network, so that your members can upload and share photos, audio and videos with their friends.

= Features =
* Images, Audio and Video Support
* Uploading Photos/Videos via mobile (Tested on iPhone running iOS6)
* HTML5 player (with fall back to flash/silverlight player support)
* Automatic conversion of common audio & video formats to mp3/mp4. via [Premium Add-On](http://rtcamp.com/store "Visit rtCamp's Store")

= Roadmap =
* Kaltura Integration (work already started).
* Paid membership plans, i.e. "Upload Quota" for buddypress members  (in planning stage).

== Installation ==

= BuddyPress Media Plugin =

Install the plugin from the 'Plugins' section in your dashboard (Go to `Plugins > Add New > Search` and search for BuddyPress Media).

Alternatively, you can [download](http://downloads.wordpress.org/plugin/buddypress-media.zip "Download BuddyPress Media") the plugin from the repository. Unzip it and upload it to the plugins folder of your WordPress installation (`wp-content/plugins/` directory of your WordPress installation).

Activate it through the 'Plugins' section.

= BuddyPress Media Add-ons =

= bpm-ffmpeg addon =

It also supports many video formats including *.avi, *.mkv, *.asf, *.flv, *.wmv, *.rm, *.mpg.
It also supports many audio formats including *.mp3, *.ogg, *.wav, *.aac, *.m4a, *.wma.

You can purchase it from [here](http://rtcamp.com/store/buddypress-media-ffmpeg-converter/ "Buy bpm-ffmpeg from rtCamp").

Important: bpm-ffmpeg addon needs free & open-source [Media Node](https://github.com/rtCamp/media-node "Media Node on GitHub").


== Frequently Asked Questions ==

Please visit [BuddyPress Media Component's FAQ page](http://rtcamp.com/buddypress-media/faq/ "Visit BuddyPress Media Component's FAQ page").

== Screenshots ==

Please visit [BuddyPress Media Component's Features page](http://rtcamp.com/buddypress-media/features/ "Visit BuddyPress Media Component's Features page").

== Changelog ==

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
Added support for common video & audio format conversion using FFMPEG. Also added support for third-party add-ons.
