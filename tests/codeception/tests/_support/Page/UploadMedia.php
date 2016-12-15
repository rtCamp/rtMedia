<?php
namespace Page;

class UploadMedia
{

    public static $galleryLable = '.rtm-gallery-title';
    public static $uploadLink = '.rtm-media-options .rtmedia-upload-media-link';
    public static $selectFileButton = '.rtm-select-files #rtMedia-upload-button';
    public static $fileList = '#rtmedia_uploader_filelist';
    public static $uploadTermsCheckbox = '#rtmedia_upload_terms_conditions';
    public static $uploadMediaButton = '.start-media-upload';
    public static $firstChild = 'ul.rtm-gallery-list li:first-child';
    public static $commentTextArea = '#comment_content';
    public static $commentSubmitButton = '.rt_media_comment_submit';
    public static $uploadMediaButtonOnActivity = '.rtmedia-add-media-button';
    public static $whatIsNewTextarea = '#whats-new';

    public static function route($param)
    {
        return static::$URL.$param;
    }

    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    /**
    * gotoMediaPage() -> Will take the user to media page
    */
    public function gotoMediaPage($userName,$I){

        $url = 'members/'.$userName.'/media/photo/';

        $I->amonPage($url);
        $I->wait(10);

        $I->seeElement(self::$galleryLable);
        $I->wait(3);
        $I->scrollTo(self::$galleryLable);

    }

    public function uploadTermasCheckbox($I){

        $I->dontSeeCheckboxIsChecked(self::$uploadTermsCheckbox);
        $I->checkOption(self::$uploadTermsCheckbox); //Assuming that "rtMedia Uplaod terms" plugin is enabled

    }


    /**
    * uploadMedia() -> Will perform neccessary steps to uplpad media. In this case it will work for image media type.
    */
    public function uploadMedia($userName,$I){

        self::gotoMediaPage($userName, $I);

        $I->seeElement(self::$uploadLink);
        $I->click(self::$uploadLink);
        $I->wait(2);

        $I->seeElement(self::$selectFileButton);
        $I->attachFile('input[type="file"]','test.jpg');
        $I->wait(5);

        $I->seeElement(self::$fileList);

    }

    /**
    * uploadMediaUsingStartUplaodButton() -> Will the media when 'Direct Uplaod' is not enabled
    */
    public function uploadMediaUsingStartUploadButton($userName){

        $I = $this->tester;

        self::uploadMedia($userName,$I);

        self::uploadTermasCheckbox($I);

        $I->seeElement(self::$uploadMediaButton);
        $I->click(self::$uploadMediaButton);

        $I->wait(3);

        return $this;

    }

    /**
    * uploadMediaDirectly() -> Will the media when 'Direct Uplaod' is enabled
    */
    public function uploadMediaDirectly($userName){

        $I = $this->tester;

        self::uploadMedia($userName,$I);

        $I->dontSeeElement(self::$uploadMediaButton);

        self::uploadTermasCheckbox($I);

        $I->wait(3);

        return $this;

    }

    public function uploadMediaFromActivity($I){

        $I->click(self::$whatIsNewTextarea);
        $I->waitForElementVisible(self::$uploadMediaButtonOnActivity,2);
        $I->click(self::$uploadMediaButtonOnActivity);
        $I->pressKey(self::$uploadMediaButtonOnActivity,array('command','tab'));
        $I->wait(3);
        $I->pressKey(self::$uploadMediaButtonOnActivity,array('shift','command','g'));
        $I->sendKeys('/Users/javalnanda/Desktop/1.jpeg');
        $I->pressKey(self::$uploadMediaButtonOnActivity,\Facebook\WebDriver\WebDriverKeys::ENTER);
        $I->wait(5);

        // $I->pressKey('#page','a'); // => olda
        // $I->pressKey('#page',array('ctrl','a'),'new'); //=> new
        // $I->pressKey('#page',array('shift','111'),'1','x'); //=> old!!!1x
        // $I->pressKey('descendant-or-self::*[ * `id='page']','u');
        // $I->pressKey('#name', array('ctrl', 'a'), \Facebook\WebDriver\WebDriverKeys::DELETE);

    }


    /**
    * fisrtThumbnailMedia() -> Will click on the first element(media thumbnail) from the list
    */
    public function fisrtThumbnailMedia($I){

        $I->click(self::$firstChild);
        $I->wait(5);

        return $this;

    }

}
