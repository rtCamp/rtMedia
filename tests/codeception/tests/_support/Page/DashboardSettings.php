<?php
namespace Page;

class DashboardSettings
{

    public static $Url = '/wp-admin/admin.php?page=rtmedia-settings';
    public static $saveSettingsButton = '.rtm-button-container.top .rtmedia-settings-submit';
    public static $saveSettingsButtonBottom = '.rtm-button-container.bottom .rtmedia-settings-submit';
    public static $rtMediaSeetings = '#toplevel_page_rtmedia-settings';
    public static $displayTab = '#tab-rtmedia-display';
    public static $commentCheckbox = 'input[name="rtmedia-options[general_enableComments]"]';
    public static $directUploadCheckbox = 'input[name="rtmedia-options[general_direct_upload]"]';
    public static $lightboxCheckbox = 'input[name="rtmedia-options[general_enableLightbox]"]';


    public function route($param)
    {
        return static::$URL.$param;
    }
    protected $tester;
    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    /**
    * saveSettings() -> Will save the settings after any changes made by the user in backend.
    */

    public function saveSettings($I){

        $I->seeElementInDOM(self::$saveSettingsButtonBottom);
        $I->scrollTo(self::$saveSettingsButtonBottom);
        $I->click(self::$saveSettingsButtonBottom);
        $I->wait(5);
        $I->see('Settings saved successfully!');

    }

    /**
    * gotortMediaSettings() -> Will goto rtmedia-settings tab.
    */

    public function gotortMediaSettings($I){

        $I->click(self::$rtMediaSeetings);
        $I->wait(5);
        $I->seeInTitle('Settings ‹ rtMedia Demo Site — WordPress');

    }

    /**
    * gotoDisplayTab() -> Will goto Display tab under rtmedia-settings tab.
    */

    public function gotoDisplayTab($I){

        self::gotortMediaSettings($I);

        $displayTabUrl = self::$Url.'#rtmedia-display';

        $I->seeElement(self::$displayTab);
        $I->click(self::$displayTab);
        $I->wait(5);
        $I->seeInCurrentUrl('/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display');

    }

    /**
    * enableComment() -> Will enable the comment.
    */

    public function enableComment($I){

        $I = $this->tester;

        self::gotoDisplayTab($I);

        $I->see('Allow user to comment on uploaded media');

        $I->seeElementInDOM(self::$commentCheckbox);
        $I->wait(3);
        $I->dontSeeCheckboxIsChecked(self::$commentCheckbox);
        $I->checkOption(self::$commentCheckbox);

        self::saveSettings($I);

        $I->seeCheckboxIsChecked(self::$commentCheckbox);

        return $this;

    }

    /**
    * disableComment() -> Will disable the comment.
    */

    public function disableComment($I){

        $I = $this->tester;

        self::gotoDisplayTab($I);

        $I->see('Allow user to comment on uploaded media');

        $I->seeElementInDOM(self::$commentCheckbox);
        $I->seeCheckboxIsChecked(self::$commentCheckbox);
        $I->uncheckOption(self::$commentCheckbox);

        self::saveSettings($I);

        $I->dontSeeCheckboxIsChecked(self::$commentCheckbox);

        return $this;

    }

    public function enableLightbox($I){

        $I = $this->tester;

        self::gotoDisplayTab($I);

        $I->see('Use lightbox to display media');

        $I->scrollTo('//*[@id="rtmedia-display"]/div[4]/h3');
        $I->seeElementInDOM(self::$lightboxCheckbox);
        $I->dontSeeCheckboxIsChecked(self::$lightboxCheckbox);
        $I->checkOption(self::$lightboxCheckbox);

        $I->wait(3);

        self::saveSettings($I);

        $I->seeCheckboxIsChecked(self::$lightboxCheckbox);

        return $this;
    }

    public function disableLightbox($I){

        $I = $this->tester;

        self::gotoDisplayTab($I);

        $I->see('Use lightbox to display media');

        $I->scrollTo('//*[@id="rtmedia-display"]/div[4]/h3');
        $I->seeElementInDOM(self::$lightboxCheckbox);
        $I->seeCheckboxIsChecked(self::$lightboxCheckbox);
        $I->uncheckOption(self::$lightboxCheckbox);

        $I->wait(3);

        self::saveSettings($I);

        $I->dontSeeCheckboxIsChecked(self::$lightboxCheckbox);

        return $this;
    }


    /**
    * enableDirectUpload() -> Will enable the direct upload.
    */

    public function enableDirectUpload($I){

        $I = $this->tester;

        self::gotoDisplayTab($I);

        $I->see('Enable Direct Upload');

        $I->scrollTo('//*[@id="rtmedia-display"]/div[6]/h3');
        $I->seeElementInDOM(self::$directUploadCheckbox);
        $I->dontSeeCheckboxIsChecked(self::$directUploadCheckbox);
        $I->checkOption(self::$directUploadCheckbox);

        $I->wait(3);

        self::saveSettings($I);

        $I->seeCheckboxIsChecked(self::$directUploadCheckbox);

        return $this;

    }

    /**
    * disableDirectUpload() -> Will disable the direct upload.
    */

    public function disableDirectUpload($I){

        $I = $this->tester;

        self::gotoDisplayTab($I);

        $I->scrollTo('//*[@id="rtmedia-display"]/div[6]/h3');
        $I->seeElementInDOM(self::$directUploadCheckbox);
        $I->seeCheckboxIsChecked(self::$directUploadCheckbox);
        $I->uncheckOption(self::$directUploadCheckbox);

        self::saveSettings($I);

        $I->dontSeeCheckboxIsChecked(self::$directUploadCheckbox);

        return $this;

    }

}
