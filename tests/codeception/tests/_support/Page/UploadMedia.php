<?php
namespace Page;

use Page\Constants as ConstantsPage;

class UploadMedia
{

    public static $galleryLable = '.rtm-gallery-title';
    public static $uploadLink = '.rtm-media-options .rtmedia-upload-media-link';
    public static $selectFileButton = '.rtm-select-files #rtMedia-upload-button';
    public static $fileList = '#rtmedia_uploader_filelist';
    public static $uploadTermsCheckbox = '#rtmedia_upload_terms_conditions';
    public static $uploadMediaButton = '.start-media-upload';
    public static $firstChild = 'ul.rtm-gallery-list li:first-child';
    public static $commentSubmitButton = '.rt_media_comment_submit';
    public static $whatIsNewTextarea = '#whats-new';
    public static $scrollPosOnActivityPage = '#user-activity';
    public static $postUpdateButton = 'input#aw-whats-new-submit';
    public static $uploadFile = 'div.moxie-shim.moxie-shim-html5 input[type=file]';
    public static $uploadFromActivity = 'div#whats-new-options div input[type="file"]';
    public static $commentTextArea = '#comment_content';
    public static $uploadContainer = '#rtmedia-upload-container';
    public static $mediaButtonOnActivity = 'button.rtmedia-add-media-button';

    protected $tester;
    public function __construct( \AcceptanceTester $I )
    {
        $this->tester = $I;
    }

    /**
    * gotoMediaPage() -> Will take the user to media page
    */
    public function gotoMediaPage( $userName, $link ){

        $I = $this->tester;

        $url = 'members/'.$userName.'/media';

        $I->amonPage( $url );
        $I->waitForElement( ConstantsPage::$profilePicture, 5 );

        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );
        $I->seeElement( $link );
        $I->click( $link );

        $I->waitForElement( ConstantsPage::$profilePicture, 5 );

        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $I->wait( 5 );

    }

    /**
    * uploadTermasCheckbox() -> will check `terms of service checkbox`
    */
    public function uploadTermasCheckbox(){

        $I = $this->tester;

        $I->dontSeeCheckboxIsChecked( self::$uploadTermsCheckbox );
        $I->checkOption( self::$uploadTermsCheckbox ); //Assuming that "rtMedia Uplaod terms" plugin is enabled

    }

    /**
    * uploadMedia() -> Will perform neccessary steps to uplpad media. In this case it will work for image media type.
    */
    public function uploadMedia( $userName, $mediaFile, $link ){

        $I = $this->tester;

        //self::gotoMediaPage( $userName, $link );

        $I->seeElement( ConstantsPage::$mediaPageScrollPos );
        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );
        $I->seeElement( $link );
        $I->click( $link );

        $I->wait( 5 );

        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );
        $I->wait( 5 );

        $I->seeElement( self::$uploadLink );
        $I->click( self::$uploadLink );
        $I->waitForElement( self::$uploadContainer, 10 );
        $I->seeElement(self::$selectFileButton);
        $I->attachFile( self::$uploadFile, $mediaFile );
        $I->wait( 10 );

    }

    /**
    * uploadMediaUsingStartUplaodButton() -> Will the media when 'Direct Uplaod' is not enabled
    */
    public function uploadMediaUsingStartUploadButton( $userName, $mediaFile, $link ){

        $I = $this->tester;

        self::uploadMedia( $userName, $mediaFile, $link );

    //    self::uploadTermasCheckbox( $I );

        $I->seeElement( self::$uploadMediaButton );
        $I->click( self::$uploadMediaButton );

        $I->wait( 10 );

    }

    /**
    * uploadMediaDirectly() -> Will the media when 'Direct Uplaod' is enabled
    */
    public function uploadMediaDirectly( $userName, $mediaFile, $link ){

        $I = $this->tester;

        self::uploadMedia( $userName, $mediaFile, $link );
    //    self::uploadTermasCheckbox();

        $I->wait( 3 );

    }

    /**
    * addStatus() -> Will perform the neccessary steps to add status
    */
    public function addStatus(){

        $I = $this->tester;

        $I->seeElementInDOM( self::$scrollPosOnActivityPage );
        $I->scrollTo( self::$scrollPosOnActivityPage );

        $I->seeElementInDOM( self::$whatIsNewTextarea );
        $I->click( self::$whatIsNewTextarea );

        $I->wait( 3 );
    }

    /**
    * uploadMediaFromActivity() -> Will upload the media from activity page when it is enabled from dashboard
    */
    public function uploadMediaFromActivity( $mediaFile ){

        $I = $this->tester;

        self::addStatus();

        $I->fillfield( self::$whatIsNewTextarea, "test from activity stream" );
        $I->seeElement( self::$mediaButtonOnActivity );
        $I->attachFile( self::$uploadFromActivity, $mediaFile );
        $I->wait( 5 );

    //    self::uploadTermasCheckbox();

        $I->click( self::$postUpdateButton );

    }
    /**
    * bulkUploadMediaFromActivity() -> Will upload the media in bulk from activity page when it is enabled from dashboard
    */
    public function bulkUploadMediaFromActivity( $mediaFile, $numOfMedia ){

        $I = $this->tester;

        self::addStatus();

        $I->fillfield( self::$whatIsNewTextarea, "test from activity stream.." );
        $I->wait( 3 );

        //if $numOfMedia > 0 then it will execute if condition else for $numOfMedia = 0 it will execute else part
        if( $numOfMedia > 0 ){
            for ( $i = 0; $i < $numOfMedia; $i++ ) {

                $I->attachFile( self::$uploadFromActivity, $mediaFile );
                $I->wait( 3 );
            }
        }else{
            $tempMedia = 5;
            for ( $i = 0; $i < $tempMedia; $i++) {
                $I->attachFile( self::$uploadFromActivity, $mediaFile );
                $I->wait( 3 );
            }
        }

    //    self::uploadTermasCheckbox();

        $I->click( self::$postUpdateButton );
        $I->wait( 5 );

    }

    /**
    * fisrtThumbnailMedia() -> Will click on the first element(media thumbnail) from the list
    */
    public function fisrtThumbnailMedia(){

        $I = $this->tester;

        $I->click( self::$firstChild );
        $I->wait( 5 );
    }

}
