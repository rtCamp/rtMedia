<?php
namespace Page;

class Constants
{
    public static $userName = 'rtcamp';
    public static $password = 'Test@1230';

    public static $displayTabUrl = '#rtmedia-display';
    public static $buddypressTabUrl = '#rtmedia-bp';
    public static $otherSeetingsTabUrl = '#rtmedia-general';

    public static $displayTab = 'a#tab-rtmedia-display';
    public static $buddypressTab = 'a#tab-rtmedia-bp';
    public static $otherSeetingsTab = 'a#tab-rtmedia-general';

    public static $strCommentCheckboxLabel = 'Allow user to comment on uploaded media';
    public static $strDirectUplaodCheckboxLabel = 'Enable Direct Upload';
    public static $strLightboxCheckboxLabel = 'Use lightbox to display media';
    public static $numOfMediaLabel = 'Number of media per page';
    public static $strMasonaryCheckboxLabel = 'Enable Masonry Cascading grid layout';
    public static $strMediaDisplayPaginationLabel = 'Media display pagination option';
    public static $numOfMediaLabelActivity = 'Number of media items to show in activity stream';
    public static $strEnableAlbumLabel = 'Organize media into albums';

    public static $strEnableMediaInProLabel = 'Enable media in profile';
    public static $strEnableMediaInGrpLabel = 'Enable media in group';

    public static $adminbarMenuLabel = 'Admin bar menu integrat';
    public static $footerLinkLabel = 'Add a link to rtMedia in footer';

    public static $numOfMediaTextbox = 'input[name="rtmedia-options[general_perPageMedia]"]';
    public static $numOfMediaTextboxActivity = 'input[name="rtmedia-options[buddypress_limitOnActivity]"]';

    public static $commentCheckbox = 'input[name="rtmedia-options[general_enableComments]"]';
    public static $directUploadCheckbox = 'input[name="rtmedia-options[general_direct_upload]"]';
    public static $lightboxCheckbox = 'input[name="rtmedia-options[general_enableLightbox]"]';
    public static $masonaryCheckbox = 'input[name="rtmedia-options[general_masonry_layout]"]';

    public static $enableMediaInProCheckbox = 'input[name="rtmedia-options[buddypress_enableOnProfile]"]';
    public static $enableMediaInGrpCheckbox = 'input[name="rtmedia-options[buddypress_enableOnGroup]"]';
    public static $enableAlbumCheckbox = 'input[name="rtmedia-options[general_enableAlbums]"]';

    public static $adminbarMenuCheckbox = 'input[name="rtmedia-options[general_showAdminMenu]"]';
    public static $footerLinkCheckbox = 'input[name="rtmedia-options[rtmedia_add_linkback]"]';

    public static $loadmoreRadioButton = 'input[value="load_more"]';
    public static $paginationRadioButton = 'input[value="pagination"]';

    // public static $directUploadScrollPosition = '//*[@id="rtmedia-display"]/div[6]/h3';
    // public static $lightboxScrollPosition = '//*[@id="rtmedia-display"]/div[2]/h3';
    // public static $masonaryScrollPostion = '//*[@id="rtmedia-display"]/div[3]/h3';
    // public static $othersSettingsTabScrollPos = 'a.rtmedia-tab-title.privacy';
    // public static $adminbarScrollPos = '//*[@id="rtmedia-general"]/div[1]/h3';
    // public static $footerLinkScrollPos = '//*[@id="rtmedia-general"]/div[8]/h3';

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

}
