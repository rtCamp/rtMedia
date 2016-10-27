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
    public static $masonryLayout = 'ul.masonry';
    public static $loadMore = 'a#rtMedia-galary-next';
    public static $paginationPattern = '.rtm-pagination .rtmedia-page-no';
    public static $closeButton = '.rtm-mfp-close';

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

    /**
    * gotoMediaPage() -> Will take the user to media page
    */
    public function gotoMediaPage($userName,$I){

        $url = 'members/'.$userName.'/media/photo/';
        $I->amonPage($url);
        $I->seeElement(self::$galleryLable);

    }

    /**
    * uploadMedia() -> Will perform neccessary steps to uplpad media. In this case it will work for image media type.
    */
    public function uploadMedia($userName,$I){

        self::gotoMediaPage($userName, $I);
        $I->seeElement(self::$uploadLink);
        $I->click(self::$uploadLink);
        $I->seeElement(self::$selectFileButton);
        $I->attachFile('input[type="file"]','test.jpg');
        $I->wait(5);
        $I->seeElement(self::$fileList);
        $I->click(self::$uploadTermsCheckbox); //Assuming that "rtMedia Uplaod terms" plugin is enabled

    }

    /**
    * uploadMediaUsingStartUplaodButton() -> Will the media when 'Direct Uplaod' is not enabled
    */
    public function uploadMediaUsingStartUploadButton($userName){

        $I = $this->tester;

        self::uploadMedia($userName,$I);

        $I->seeElement(self::$uploadMediaButton);
        $I->click(self::$uploadMediaButton);
        $I->wait(3);

        return $this;

    }

    /**
    * uploadMediaUsingStartUplaodButton() -> Will the media when 'Direct Uplaod' is enabled
    */
    public function uploadMediaDirectly($userName){

        $I = $this->tester;

        self::uploadMedia($userName,$I);

        $I->dontSeeElement(self::$uploadMediaButton);
        $I->wait(3);

        return $this;

    }

    /**
    * fisrtThumbnailMedia() -> Will click on the first element(media thumbnail) from the list
    */
    public function fisrtThumbnailMedia($I){

        $I = $this->tester;

        $I->click(self::$firstChild);
        $I->wait(5);
    
        return $this;

    }

}
