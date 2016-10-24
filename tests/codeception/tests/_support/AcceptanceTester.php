<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */

    public function login($userName,$password){
        $I = $this;
        $I->amonPage('/');
        $I->fillfield( 'input#bp-login-widget-user-login', $userName );
        $I->fillfield( 'input#bp-login-widget-user-pass', $password );
        $I->click('Log In');
        $I->seeElement('.logout');
    }

    public function uploadPhoto($userName){
        $url = 'members/'.$userName.'/media/photo/';
        $I = $this;
        $I->amonPage($url);
        $I->seeElement('.rtm-gallery-title');
        $I->seeElement('.rtm-media-options .rtmedia-upload-media-link');
        $I->click('.rtm-media-options .rtmedia-upload-media-link');
        $I->seeElement('.rtm-select-files #rtMedia-upload-button');
        $I->attachFile('input[type="file"]','test.jpg');
        $I->wait(5);
        $I->seeElement('#rtmedia_uploader_filelist');
        $I->click('#rtmedia_upload_terms_conditions'); //Assuming that "rtMedia Uplaod terms" plugin is enabled
        $I->click('.start-media-upload');
        $I->wait(3);
    }


}
