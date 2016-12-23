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
    public static $whatIsNewTextarea = '#whats-new';
    public static $scrollPosOnActivityPage = '#user-activity';
    public static $postUpdateButton = 'input#aw-whats-new-submit';
    public static $mediaPageScrollPos = '#user-activity';

    /**
    * gotoMediaPage() -> Will take the user to media page
    */
    public function gotoMediaPage($I,$userName,$link){

        $url = 'members/'.$userName.'/media';
        $I->amonPage($url);

        $I->wait(10);

        $I->seeElementInDOM($link);
        $I->scrollTo(self::$mediaPageScrollPos);
        $I->wait(2);
        $I->click($link);
        $I->wait(10);

        $I->seeElement(self::$galleryLable);
        $I->wait(3);
        $I->scrollTo(self::$galleryLable);

    }

    /**
    * uploadTermasCheckbox() -> will check `terms of service checkbox`
    */
    public function uploadTermasCheckbox($I){

        $I->dontSeeCheckboxIsChecked(self::$uploadTermsCheckbox);
        $I->checkOption(self::$uploadTermsCheckbox); //Assuming that "rtMedia Uplaod terms" plugin is enabled

    }

    /**
    * uploadMedia() -> Will perform neccessary steps to uplpad media. In this case it will work for image media type.
    */
    public function uploadMedia($I,$userName,$mediaFile,$link){

        self::gotoMediaPage($I,$userName,$link);

        $I->seeElement(self::$uploadLink);
        $I->click(self::$uploadLink);
        $I->wait(2);

        $I->seeElement(self::$selectFileButton);
        $I->attachFile('input[type="file"]',$mediaFile);
        $I->wait(10);

        $I->seeElement(self::$fileList);

    }

    /**
    * uploadMediaUsingStartUplaodButton() -> Will the media when 'Direct Uplaod' is not enabled
    */
    public function uploadMediaUsingStartUploadButton($I,$userName,$mediaFile,$link){

        self::uploadMedia($I,$userName,$mediaFile,$link);

        self::uploadTermasCheckbox($I);

        $I->seeElement(self::$uploadMediaButton);
        $I->click(self::$uploadMediaButton);

        $I->wait(10);

    }

    /**
    * uploadMediaDirectly() -> Will the media when 'Direct Uplaod' is enabled
    */
    public function uploadMediaDirectly($I,$userName,$mediaFile,$link){

        self::uploadMedia($I,$userName,$mediaFile,$link);

        $I->dontSeeElement(self::$uploadMediaButton);

        self::uploadTermasCheckbox($I);

        $I->wait(3);

    }

    /**
    * addStatus() -> Will perform the neccessary steps to add status
    */
    public function addStatus($I){

        $I->seeElementInDOM(self::$scrollPosOnActivityPage);
        $I->scrollTo(self::$scrollPosOnActivityPage);

        $I->seeElementInDOM(self::$whatIsNewTextarea);
        $I->click(self::$whatIsNewTextarea);
        $I->wait(3);
    }

    /**
    * uploadMediaFromActivity() -> Will upload the media from activity page when it is enabled from dashboard
    */
    public function uploadMediaFromActivity($I,$mediaFile){

        self::addStatus($I);

        $I->fillfield(self::$whatIsNewTextarea,"test from activity stream");
        $I->wait(3);

        $I->attachFile('input[type="file"]',$mediaFile);
        $I->wait(5);

        $I->click(self::$postUpdateButton);
        $I->wait(5);

    }
    /**
    * bulkUploadMediaFromActivity() -> Will upload the media in bulk from activity page when it is enabled from dashboard
    */
    public function bulkUploadMediaFromActivity($I,$mediaFile,$numOfMedia){

        self::addStatus($I);

        $I->fillfield(self::$whatIsNewTextarea,"test from activity stream");
        $I->wait(3);

        //if $numOfMedia > 0 then it will execute if condition else for $numOfMedia = 0 it will execute else part
        if($numOfMedia > 0){
            for ($i = 0; $i < $numOfMedia; $i++) {
                $I->attachFile('input[type="file"]',$mediaFile);
                $I->wait(3);
            }
        }else{
            $tempMedia = 5;
            for ($i = 0; $i < $tempMedia; $i++) {
                $I->attachFile('input[type="file"]',$mediaFile);
                $I->wait(3);
            }
        }

        $I->click(self::$postUpdateButton);
        $I->wait(5);

    }

    /**
    * fisrtThumbnailMedia() -> Will click on the first element(media thumbnail) from the list
    */
    public function fisrtThumbnailMedia($I){

        $I->click(self::$firstChild);
        $I->wait(5);

    }
}
