<?php
namespace Page;

class UploadMedia
{
    // include url of current page

    public static $galleryLable = '.rtm-gallery-title';
    public static $uploadLink = '.rtm-media-options .rtmedia-upload-media-link';
    public static $selectFileButton = '.rtm-select-files #rtMedia-upload-button';
    public static $fileList = '#rtmedia_uploader_filelist';
    public static $uploadTermsCheckbox = '#rtmedia_upload_terms_conditions';
    public static $uploadMediaButton = '.start-media-upload';
    public static $firstChild = 'ul.rtm-gallery-list li:first-child';
    public static $commentTextArea = '#comment_content';
    public static $commentSubmitButton = '.rt_media_comment_submit';



    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static $usernameField = '#username';
     * public static $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: Page\Edit::route('/123-post');
     */
    public static function route($param)
    {
        return static::$URL.$param;
    }

    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function uploadMedia($userName){

        $I = $this->tester;

        $url = 'members/'.$userName.'/media/photo/';

        $I->amonPage($url);

        $I->seeElement(self::$galleryLable);
        $I->seeElement(self::$uploadLink);
        $I->click(self::$uploadLink);
        $I->seeElement(self::$selectFileButton);
        $I->attachFile('input[type="file"]','test.jpg');
        $I->wait(5);
        $I->seeElement(self::$fileList);
        $I->click(self::$uploadTermsCheckbox); //Assuming that "rtMedia Uplaod terms" plugin is enabled
        $I->click(self::$uploadMediaButton);
        $I->wait(3);

        return $this;

    }

}
