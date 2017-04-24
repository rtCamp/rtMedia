<?php
namespace Page;

class Constants
{
    public static $userName = 'ADMINUSER';
    public static $password = 'ADMINPASS';

    public static $plugin = 'li#menu-plugins';

    public static $imageName = 'test-data/images/test.jpg';
    public static $videoName = 'test-data/videos/testmpfour.mp4';
    public static $audioName = 'test-data/music/mpthreetest.mp3';

    public static $customCssValue = '#buddypress #whats-new { height: 500px !important; overflow: hidden;}';
    public static $customCssEmptyValue = '';

    public static $numOfMediaPerPage = '2';
    public static $numOfMediaPerPageOnActivity = '2';
    public static $minValue = '1';

    public static $thumbnailWidth = '150';
    public static $thumbnailHeight = '150';
    public static $mediumWidth = '320';
    public static $mediummHeight = '240';
    public static $LargeWidth = '700';
    public static $LargeHeight = '0';

    public static $activityVideoPlayerWidth = '320';
    public static $activityVideoPlayerHeight = '240';
    public static $singleVideoWidth = '500';
    public static $singleVideoHeight = '480';

    public static $activityMusicPlayerWidth = '350';
    public static $singleMusicPlayerWidth = '640';

    public static $imgQualityValue = '80';

    public static $rtMediaSettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings';
    public static $saveSettingsButtonBottom = '.rtm-button-container.bottom .rtmedia-settings-submit';
    public static $rtMediaSeetings = '#toplevel_page_rtmedia-settings';

    public static $topSaveButton = '.rtm-button-container.top input.rtmedia-settings-submit';

    public static $displayTabUrl = '#rtmedia-display';
    public static $buddypressTabUrl = '#rtmedia-bp';
    public static $typesTabUrl = '#rtmedia-types';
    public static $mediaSizesTabUrl  = '#rtmedia-sizes';
    public static $privacyTabUrl = '#rtmedia-privacy';
    public static $customCssTabUrl = '#rtmedia-custom-css-settings';
    public static $otherSeetingsTabUrl = '#rtmedia-general';

    public static $displayTab = 'a#tab-rtmedia-display';
    public static $buddypressTab = 'a#tab-rtmedia-bp';
    public static $typesTab = 'a#tab-rtmedia-types';
    public static $mediaSizesTab = 'a#tab-rtmedia-sizes';
    public static $privacyTab = 'a#tab-rtmedia-privacy';
    public static $customCssTab = 'a#tab-rtmedia-custom-css-settings';
    public static $otherSeetingsTab = 'a#tab-rtmedia-general';

    public static $strCommentCheckboxLabel = 'Allow user to comment on uploaded media';
    public static $strDirectUplaodCheckboxLabel = 'Enable Direct Upload';
    public static $strLightboxCheckboxLabel = 'Use lightbox to display media';
    public static $numOfMediaLabel = 'Number of media per page';
    public static $strMasonaryCheckboxLabel = 'Enable Masonry Cascading grid layout';
    public static $strMediaDisplayPaginationLabel = 'Media display pagination option';
    public static $strMediaSearchLabel = 'Enable gallery media search';

    public static $strEnableMediaInProLabel = 'Enable media in profile';
    public static $strEnableMediaInGrpLabel = 'Enable media in group';
    public static $numOfMediaLabelActivity = 'Number of media items to show in activity stream';
    public static $strEnableAlbumLabel = 'Organize media into albums';
    public static $strMediaNotificationLabel = 'Enable media notification';
    public static $strActivityMediaLikeLabel = 'Create activity for media likes';
    public static $strActivityMediaCommentLabel = 'Create activity for media comments';
    public static $strMediaUploadFromActivityLabel = 'Allow upload from activity stream';
    public static $strMediaInCommnetLabel = 'Enable media in comment';
    public static $strShowAlbumDescLabel = 'Show album description';

    public static $photoLabel = 'Photo';
    public static $videoLabel = 'Video';
    public static $musicLabel = 'Music';

    public static $mediaPhotoLabel = 'Photo';
    public static $photoThumbnailLabel = 'Thumbnail';
    public static $photoMediumLabel = 'Medium';
    public static $photoLargeLabel = 'Large';
    public static $activityPlayerLabel = 'ActivityPlayer';
    public static $singlePlayerLabel = 'SinglePlayer';
    public static $featuredLabel = 'Featured';
    public static $imgQualityLabel = 'JPEG/JPG image quality (1-100)';

    public static $privacyLabel = 'Enable privacy';
    public static $defaultPrivacyLabel = 'Default privacy';
    public static $privacyUserOverrideLabel = 'Allow users to set privacy for their content';

    public static $defaultStyleLabel = 'rtMedia default styles';
    public static $customCssLabel = 'Paste your CSS code';

    public static $strEnableJsonDataLabel = 'Enable JSON API';
    public static $strEnableUsageDataTrackingLabel = 'Allow usage data tracking';
    public static $adminbarMenuLabel = 'Admin bar menu integration';
    public static $footerLinkLabel = 'Add a link to rtMedia in footer';

    public static $commentCheckbox = 'input[name="rtmedia-options[general_enableComments]"]';
    public static $directUploadCheckbox = 'input[name="rtmedia-options[general_direct_upload]"]';
    public static $lightboxCheckbox = 'input[name="rtmedia-options[general_enableLightbox]"]';
    public static $masonaryCheckbox = 'input[name="rtmedia-options[general_masonry_layout]"]';
    public static $loadmoreRadioButton = 'input[value="load_more"]';
    public static $paginationRadioButton = 'input[value="pagination"]';
    public static $numOfMediaTextbox = 'input[name="rtmedia-options[general_perPageMedia]"]';
    public static $mediaSearchCheckbox = 'input[name="rtmedia-options[general_enableGallerysearch]"]';

    public static $enableMediaInProCheckbox = 'input[name="rtmedia-options[buddypress_enableOnProfile]"]';
    public static $enableMediaInGrpCheckbox = 'input[name="rtmedia-options[buddypress_enableOnGroup]"]';
    public static $enableAlbumCheckbox = 'input[name="rtmedia-options[general_enableAlbums]"]';
    public static $mediaNotificationCheckbox = 'input[name="rtmedia-options[buddypress_enableNotification]"]';
    public static $activityMediaLikeCheckbox = 'input[name="rtmedia-options[buddypress_mediaLikeActivity]"]';
    public static $activityMediaCommentCheckbox = 'input[name="rtmedia-options[buddypress_mediaCommentActivity]"]';
    public static $mediaUploadFromActivityCheckbox = 'input[name="rtmedia-options[buddypress_enableOnActivity]"]';
    public static $numOfMediaTextboxActivity = 'input[name="rtmedia-options[buddypress_limitOnActivity]"]';
    public static $mediaInCommentCheckbox = 'input[name="rtmedia-options[buddypress_enableOnComment]"]';
    public static $mediaButtonInComment = 'button.rtmedia-comment-media-upload';
    public static $uploadFileInComment = 'div.rtm-media-single-comments div.moxie-shim.moxie-shim-html5 input[type=file]';
    public static $albumDescCheckbox = 'input[name="rtmedia-options[general_enableAlbums_description]"]';

    public static $photoCheckbox = 'input[name="rtmedia-options[allowedTypes_photo_enabled]"]';
    public static $videoCheckbox = 'input[name="rtmedia-options[allowedTypes_video_enabled]"]';
    public static $musicCheckbox = 'input[name="rtmedia-options[allowedTypes_music_enabled]"]';

    public static $thumbnailWidthTextbox = 'input[name="rtmedia-options[defaultSizes_photo_thumbnail_width]"]';
    public static $thumbnailHeightTextbox = 'input[name="rtmedia-options[defaultSizes_photo_thumbnail_height]"]';
    public static $thumbnailCropCheckbox = 'input[name="rtmedia-options[defaultSizes_photo_thumbnail_crop]"]';

    public static $mediumWidthTextbox = 'input[name="rtmedia-options[defaultSizes_photo_medium_width]"]';
    public static $mediumHeightTextbox = 'input[name="rtmedia-options[defaultSizes_photo_medium_height]"]';
    public static $mediumCropCheckbox = 'input[name="rtmedia-options[defaultSizes_photo_medium_crop]"]';

    public static $largeWidthTextbox = 'input[name="rtmedia-options[defaultSizes_photo_large_width]"]';
    public static $largeHeightTextbox = 'input[name="rtmedia-options[defaultSizes_photo_large_height]"]';
    public static $largeCropChrckbox = 'input[name="rtmedia-options[defaultSizes_photo_large_crop]"]';

    public static $activityVideoWidthTextbox = 'input[name="rtmedia-options[defaultSizes_video_activityPlayer_width]"]';
    public static $activityVideoHeightTextbox = 'input[name="rtmedia-options[defaultSizes_video_activityPlayer_height]"]';
    public static $singleVideoWidthTextbox = 'input[name="rtmedia-options[defaultSizes_video_singlePlayer_width]"]';
    public static $singleVideoHeightTextbox = 'input[name="rtmedia-options[defaultSizes_video_singlePlayer_height]"]';

    public static $activityMusicWidthTextbox = 'input[name="rtmedia-options[defaultSizes_music_activityPlayer_width]"]';
    public static $singleMusicWidthTextbox = 'input[name="rtmedia-options[defaultSizes_music_singlePlayer_width]"]';

    public static $featuredWidthTextbox = 'input[name="rtmedia-options[defaultSizes_featured_default_width]"]';
    public static $featuredHeightTextbox = 'input[name="rtmedia-options[defaultSizes_featured_default_height]"]';

    public static $imgQualityTextbox = 'input[name="rtmedia-options[general_jpeg_image_quality]"]';

    public static $privacyCheckbox = 'input[name="rtmedia-options[privacy_enabled]"]';
    public static $privateRadioButton = 'input[value="60"]';
    public static $loggedInUsersRadioButton = 'input[value="20"]';
    public static $publicRadioButton = 'input#rtm-form-radio-4';
    public static $privacyUserOverrideCheckbox = 'input[name="rtmedia-options[privacy_userOverride]"]';

    public static $defaultStyleCheckbox = 'input[name="rtmedia-options[styles_enabled]"]';
    public static $cssTextaear= '.rtm-form-textarea';
    public static $cssTextarea= 'span.rtm-field-wrap #rtmedia-custom-css';

    public static $enableJsonDataCheckbox = 'input[name="rtmedia-options[rtmedia_enable_api]"]';
    public static $enableUsageDataTrackingCheckbox = 'input[name="rtmedia-options[general_AllowUserData]"]';
    public static $adminbarMenuCheckbox = 'input[name="rtmedia-options[general_showAdminMenu]"]';
    public static $footerLinkCheckbox = 'input[name="rtmedia-options[rtmedia_add_linkback]"]';

    public static $photoLink = 'a#rtmedia-nav-item-photo';
    public static $videoLink = 'a#rtmedia-nav-item-video';
    public static $musicLink = 'a#rtmedia-nav-item-music';

    public static $thumbnailSelector = '.rtmedia-item-thumbnail img';
    public static $videoSelectorSingle = 'div#rtm-mejs-video-container';
    public static $videoSelectorActivity = 'div.mejs-container.svg.wp-video-shortcode.mejs-video';
    public static $audioSelectorActivity = 'div.mejs-container.svg.wp-audio-shortcode.mejs-audio';
    public static $audioSelectorSingle = 'div.mejs-container.svg.wp-audio-shortcode.mejs-audio';

    public static $closeButton = '.rtm-mfp-close';
    public static $masonryLayoutXpath = '//*[@id="rtm-gallery-title-container"]/h2';
    public static $masonryLayout = 'ul.masonry';
    public static $rtMediaAdminbar = '#wp-admin-bar-rtMedia';
    public static $footerLink = '.rtmedia-footer-link';
    public static $loadMore = 'a#rtMedia-galary-next';
    public static $paginationPattern = '.rtm-pagination .rtmedia-page-no';
    public static $mediaLinkOnProfile = 'a#user-media';
    public static $mediaLinkOnGroup = 'a#media';
    public static $mediaAlbumLink = 'a#rtmedia-nav-item-albums';
    public static $uploadButtonOnAtivityPage = '.rtmedia-add-media-button';
    public static $privacyDropdown = 'select#rtSelectPrivacy';

    public static $mediaPerPageActivitySelector = 'ul#activity-stream  > li:first-child ul li';
    public static $mediaPerPageOnMediaSelector = 'ul.rtm-gallery-list li';

    public static $groupListSelector = 'div#groups-dir-list > ul#groups-list li';
    public static $createGroupLink = 'ul li#group-create-nav';
    public static $createGroupTabs = 'div#group-create-tabs';
    public static $groupNameTextbox = 'input#group-name';
    public static $groupDescTextarea = 'textarea#group-desc';
    public static $createGroupButton = 'input#group-creation-create';

    public static $mediaUplaodButtonOnComment = 'div button.rtmedia-comment-media-upload';
    public static $mediaOptionButton = 'div.rtmedia-action-buttons';
    public static $addAlbumButtonLink = 'ul.rtm-options li a.rtmedia-modal-link';
    public static $createAlbumPopup = 'div.rtmedia-popup';
    public static $albumNameTextbox = 'input.rtm-input-medium';
    public static $createAlbumButton = 'button#rtmedia_create_new_album';
    public static $closeAlbumButton = 'button.mfp-close';
    public static $firstAlbum = 'ul.rtmedia-album-list li:nth-child(1)';
    public static $albumEditLink = 'a.rtmedia-edit';
    public static $albumDescTeaxtarea = 'textarea.rtmedia-desc-textarea';
    public static $saveAlbumButton = 'input.rtmedia-save-album';
    public static $albumDescSelector = '.gallery-description.gallery-album-description';

    public static $mediaPageScrollPos = '#user-activity';   //Scroll position for activity and media pages
    public static $profilePicture = 'div#item-header-avatar';
    public static $commentLink = 'span > a.rtmedia-comment-link';

    public static $mediaSeachSelector = '.media_search';

    public static $scrollPosToForPlugin = '.wp-list-table.plugins tr[data-slug="buddypress-media"]';
    public static $activateLink = 'tr[data-slug="buddypress-media"] .row-actions span.activate a';
    public static $deactivateLink = 'tr[data-slug="buddypress-media"] .row-actions span.deactivate a';

    public static $addNewPluginButton = 'a.page-title-action';
    public static $uploadPluginButton = 'a.upload-view-toggle';
    public static $choosefileButton = 'input#pluginzip';
    public static $installPluginButton = 'input#install-plugin-submit';
    public static $searchPluginTextbox = 'input.wp-filter-search';
    public static $installNowButton = '#the-list > div.plugin-card.plugin-card-buddypress-media > div.plugin-card-top > div.action-links > ul > li:nth-child(1) > a';
    public static $activatePluginButton = '#the-list > div.plugin-card.plugin-card-buddypress-media > div.plugin-card-top > div.action-links > ul > li:nth-child(1) > a';
    public static $pluginDataslug = '.wp-list-table.plugins tr[data-slug="buddypress-media"]';
    public static $deletePluginLink = 'tr[data-slug="buddypress-media"] .row-actions span.delete a';
    public static $allPluginsUrl = '/wp-admin/plugins.php?plugin_status=all';

    public static $gotoPluginPage = '/wp-admin/plugins.php';
    public static $gotoPluginInstallPage = '/wp-admin/plugin-install.php';
    public static $gotoActivePluginPage = '/wp-admin/plugins.php?plugin_status=active';
    public static $gotoInactivePluginPage = '/wp-admin/plugins.php?plugin_status=inactive';
    public static $uploadPluginPage = '/wp-admin/update.php?action=upload-plugin';
    public static $refLabel = '<strong>rtMedia for WordPress, BuddyPress and bbPress</strong>';

}
