<?php
namespace Page;

class Constants
{
    // include url of current page
    public static $URL = '';

    public static $userName = 'krupa';
    public static $password = 'Test123';

    public static $strCommentCheckboxLabel = 'Allow user to comment on uploaded media';
    public static $strDirectUplaodCheckboxLabel = 'Enable Direct Upload';
    public static $strLightboxCheckboxLabel = 'Use lightbox to display media';
    public static $strMasonaryCheckboxLabel = 'Enable Masonry Cascading grid layout';
    public static $strMediaDisplayPaginationLabel = 'Media display pagination option';

    public static $commentCheckbox = 'input[name="rtmedia-options[general_enableComments]"]';
    public static $directUploadCheckbox = 'input[name="rtmedia-options[general_direct_upload]"]';
    public static $lightboxCheckbox = 'input[name="rtmedia-options[general_enableLightbox]"]';
    public static $masonaryCheckbox = 'input[name="rtmedia-options[general_masonry_layout]"]';
    public static $loadmoreRadioButton = 'input[value="load_more"]';
    public static $paginationRadioButton = 'input[value="pagination"]';

    public static $directUploadScrollPosition = '//*[@id="rtmedia-display"]/div[6]/h3';
    public static $lightboxScrollPosition = '//*[@id="rtmedia-display"]/div[4]/h3';
    public static $masonaryScrollPostion = '//*[@id="rtmedia-display"]/div[5]/h3';

    public static $closeButton = '.rtm-mfp-close';
    public static $masonryLayoutXpath = '//*[@id="rtm-gallery-title-container"]/h2';
    public static $masonryLayout = 'ul.masonry';

    public static function route($param)
    {
        return static::$URL.$param;
    }


}
