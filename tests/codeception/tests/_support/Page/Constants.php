<?php

namespace Page;

class Constants {

	// Username and Password
	public static $userName = 'ADMINUSER';
	public static $password = 'ADMINPASS';

	// Media files Name with path
	public static $imageName = 'test-data/images/test.jpg';
	public static $audioName = 'test-data/music/mpthreetest.mp3';
	public static $videoName = 'test-data/videos/testmpfour.mp4';

	// Constant Values
	public static $commentStr = 'Test comment';
	public static $customCssValue = '#buddypress #whats-new { height: 500px !important; overflow: hidden;}';
	public static $customCssEmptyValue = '';
	public static $numOfMediaPerPage = '2';
	public static $numOfMediaPerPageOnActivity = '2';
	public static $minValue = '1';
	public static $thumbnailWidth = '150';
	public static $thumbnailHeight = '150';
	public static $mediumWidth = '320';
	public static $mediumHeight = '240';
	public static $largeWidth = '700';
	public static $largeHeight = '300';
	public static $activityVideoPlayerWidth = '320';
	public static $activityVideoPlayerHeight = '240';
	public static $singleVideoWidth = '500';
	public static $singleVideoHeight = '480';
	public static $activityMusicPlayerWidth = '350';
	public static $singleMusicPlayerWidth = '600';
	public static $imgQualityValue = '80';

	// URLs to target setting tabs
	public static $rtMediaSettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings';
	public static $displaySettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display';
	public static $buddypressSettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-bp';
	public static $typesSettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-types';
	public static $mediaSizeSettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-sizes';
	public static $privacySettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-privacy';
	public static $customCssSettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-custom-css-settings';
	public static $otherSettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-general';
	public static $bpComponentsUrl = '/wp-admin/options-general.php?page=bp-components/';

	// Labels
	public static $strCommentCheckboxLabel = 'Allow user to comment on uploaded media';
	public static $strEnableGalleryMediaSearchLabel = 'Enable gallery media search';
	public static $strDirectUplaodCheckboxLabel = 'Enable Direct Upload';
	public static $strLightboxCheckboxLabel = 'Use lightbox to display media';
	public static $numOfMediaLabel = 'Number of media per page';
	public static $strMasonaryCheckboxLabel = 'Enable Masonry Cascading grid layout';
	public static $strMediaDisplayPaginationLabel = 'Media display pagination option';
	public static $strEnableMediaInProLabel = 'Enable media in profile';
	public static $strEnableMediaInGrpLabel = 'Enable media in group';
	public static $numOfMediaLabelActivity = 'Number of media items to show in activity stream';
	public static $strEnableAlbumLabel = 'Organize media into albums';
	public static $strMediaNotificationLabel = 'Enable media notification';
	public static $strCreateActivityMediaLikeLabel = 'Create activity for media likes';
	public static $strCreateActivityForMediaCommentLabel = 'Create activity for media comments';
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

	// Checkbox, Textbox and Radio buttons selectors
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
	public static $privateRadioButton = '.rtm-form-radio input[value="60"]';
	public static $loggedInUsersRadioButton = '.rtm-form-radio input[value="20"]';
	public static $publicRadioButton = '.rtm-form-radio input[value="0"]';
	public static $privacyUserOverrideCheckbox = 'input[name="rtmedia-options[privacy_userOverride]"]';
	public static $defaultStyleCheckbox = 'input[name="rtmedia-options[styles_enabled]"]';
	public static $cssTextaear = '.rtm-form-textarea';
	public static $cssTextarea = 'span.rtm-field-wrap #rtmedia-custom-css';
	public static $enableJsonDataCheckbox = 'input[name="rtmedia-options[rtmedia_enable_api]"]';
	public static $enableUsageDataTrackingCheckbox = 'input[name="rtmedia-options[general_AllowUserData]"]';
	public static $adminbarMenuCheckbox = 'input[name="rtmedia-options[general_showAdminMenu]"]';
	public static $footerLinkCheckbox = 'input[name="rtmedia-options[rtmedia_add_linkback]"]';
	public static $scrollPosForDirectUpload = 'input[name="rtmedia-options[general_masonry_layout]"]';
	public static $saveSettingsButtonBottom = '.rtm-button-container.bottom .rtmedia-settings-submit';
	public static $rtMediaSettings = '#toplevel_page_rtmedia-settings';
	public static $topSaveButton = '.rtm-button-container.top input.rtmedia-settings-submit';
	public static $grpTableRow = 'tbody#the-list tr#groups';
	public static $userGroupsLabelStr = 'User Groups';
	public static $enableUserGrpCheckbox = 'input[name="bp_components[groups]"]';
	public static $saveBPSettings = 'input#bp-admin-component-submit';
	public static $displayTab = 'a#tab-rtmedia-display';
	public static $buddypressTab = 'a#tab-rtmedia-bp';
	public static $typesTab = 'a#tab-rtmedia-types';
	public static $mediaSizesTab = 'a#tab-rtmedia-sizes';
	public static $privacyTab = 'a#tab-rtmedia-privacy';
	public static $customCssTab = 'a#tab-rtmedia-custom-css-settings';
	public static $otherSettingsTab = 'a#tab-rtmedia-general';

	// Frontend selectors
	public static $photoLink = 'a#rtmedia-nav-item-photo';
	public static $videoLink = 'a#rtmedia-nav-item-video';
	public static $musicLink = 'a#rtmedia-nav-item-music';
	public static $thumbnailImgSelector = '.rtmedia-item-thumbnail img';
	public static $mediumImgSelector = '.rtmedia-activity-container .media-type-photo .rtmedia-item-thumbnail img';
	public static $largImgeSelector = '.rtmedia-media img';
	public static $videoSelectorSingle = 'div#rtm-mejs-video-container';
	// public static $videoSelectorActivity = 'div.mejs-container.svg.wp-video-shortcode.mejs-video';
	public static $videoSelectorActivity = 'div.mejs-container.wp-video-shortcode.mejs-video';
	// public static $audioSelector = '.mejs-container';
	public static $audioSelector = '.mejs-container.wp-audio-shortcode.mejs-audio';
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
	public static $uploadButtonOnAtivityPage = 'button.rtmedia-add-media-button';
	public static $privacyDropdown = 'select#rtSelectPrivacy';
	public static $mediaPerPageActivitySelector = 'ul#activity-stream > li:first-child ul li';
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
	public static $albumDescSelector = '.gallery-album-description';
	public static $scrollSelector = 'a#rtmedia-nav-item-albums';
	public static $mediaPageScrollPos = '#user-activity';   //Scroll position for activity and media pages
	public static $profilePicture = 'div#item-header-avatar';
	public static $commentLink = 'span > a.rtmedia-comment-link';
	public static $mediaSearchSelector = '.media_search';
	public static $activityPrivacyStatus = '#activity-stream.activity-list.item-list > li.activity.activity_update.activity-item > div.activity-content > div.activity-inner p';
	public static $commentDiv = 'div.rtmedia-comment-content p';
	public static $goBackToAlbumPage = 'a.rtm-button-back';
	public static $metaSection = '#wp-admin-bar-logout';
	public static $logoutLink = '#wp-admin-bar-user-actions #wp-admin-bar-logout a';
	public static $logoutMsg = 'p.message';
	public static $saveSession = false;
	public static $fileList = '#rtmedia_uploader_filelist';
	public static $siteEntryTitle = '.entry-title';
	public static $mediaContainer = '.rtmedia-container.rtmedia-single-container';
	public static $fileListOnMediaComment = 'div.rtmedia-plupload-notice';
	public static $saveMsgSelector = '#setting-error-settings_updated';
	public static $nextGrpButton = '#group-creation-next';
	public static $manageGrpLink = '#admin';
	public static $optionsPopup = '.rtm-options';
	public static $albumMsg = ' album created successfully.';
	public static $activitySelector = '#activity-stream';
	public static $mediaLikeCheckboxLabel = 'Enable likes for media';
	public static $mediaLikeCheckbox = 'input[name="rtmedia-options[general_enableLikes]"]';
	public static $likeButton = '.rtmedia-like';
	public static $uploadFromActivity = 'div#whats-new-options div input[type="file"]';
	public static $whatIsNewTextarea = '#whats-new';
	public static $postUpdateButton = 'input#aw-whats-new-submit';
	public static $fileNotSupportedSelector = '.dashicons-info';
	public static $firstMediaChild = 'ul.rtm-gallery-list li:first-child';
	public static $uploadLink = '.rtmedia-upload-media-link';
	public static $uploadContainer = '#rtmedia-upload-container';
	public static $selectFileButton = 'input#rtMedia-upload-button';
	public static $uploadFile = 'div.moxie-shim.moxie-shim-html5 input[type=file]';
	public static $uploadMediaButton = '#rtmedia-upload-container .start-media-upload';
	public static $commentTextArea = '#comment_content';
	public static $commentSubmitButton = '.rt_media_comment_submit';
	public static $likeInfoSelector = '.rtm-like-comments-info';
	public static $firstMusicElementOnActivity = 'ul#activity-stream > li:first-child li.rtmedia-list-item.media-type-music';
	public static $firstPhotoElementOnActivity = 'ul#activity-stream > li:first-child li.rtmedia-list-item.media-type-photo';
	public static $firstVideoElementOnActivity = 'ul#activity-stream > li:first-child li.rtmedia-list-item.media-type-video';
	public static $activityMediaCommentSelector = 'ul#activity-stream > li:first-child.rtmedia_comment_activity';
	public static $activityMediaLikeSelector = 'ul#activity-stream > li:first-child.rtmedia_like_activity';
	public static $groupNameLink = '#groups-dir-list ul#groups-list > li:first-child  a img';

	// Custom Messages
	public static $enabledSettingMsg = "Setting is already enabled.";
	public static $disabledSettingMsg = "Setting is already disabled.";

	// Login Selectors
	public static $wpUserNameField = 'input#user_login';
	public static $wpPasswordField = 'input#user_pass';
	public static $wpDashboard = '#adminmenu';
	public static $wpSubmitButton = 'input#wp-submit';
	public static $loginLink = 'li#wp-admin-bar-bp-login';
	public static $dashBoardMenu = 'li#menu-dashboard';

	//Logout Selectors
	public static $adminBarMenuSelector = '#wp-admin-bar-my-account';

}
