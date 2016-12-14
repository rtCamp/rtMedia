<?php
namespace Page;

class DashboardSettings
{
    public static $Url = '/wp-admin/admin.php?page=rtmedia-settings';
    public static $saveSettingsButtonBottom = '.rtm-button-container.bottom .rtmedia-settings-submit';
    public static $rtMediaSeetings = '#toplevel_page_rtmedia-settings';
    public static $displayTab = 'a#tab-rtmedia-display';
    public static $commentCheckbox = 'input[name="rtmedia-options[general_enableComments]"]';
    public static $directUploadCheckbox = 'input[name="rtmedia-options[general_direct_upload]"]';
    public static $lightboxCheckbox = 'input[name="rtmedia-options[general_enableLightbox]"]';
    public static $masonaryCheckbox = 'input[name="rtmedia-options[general_masonry_layout]"]';
    public static $loadmoreRadioButton = '#rtm-form-radio-0';
    public static $listMediaViewLable = '//*[@id="rtmedia-display"]/div[4]/h3';
    public static $directUploadLabel = '//*[@id="rtmedia-display"]/div[6]/h3';
    public static $masonaryViewLabel = '//*[@id="rtmedia-display"]/div[5]/h3';


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

        $I->seeElement(self::$displayTab);
        $I->click(self::$displayTab);
        $I->wait(5);
        $I->seeInCurrentUrl('/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display');

    }

    /**
    * enableSetting() -> Will enable the respective checkbox under rtmedia-settings tab.
    */
    public function enableSetting($I,$strLabel,$checkboxSelector,$scrollPosition='no'){

        $I = $this->tester;

        self::gotoDisplayTab($I);

        $I->see($strLabel);

        if('no' !== $scrollPosition){
            $I->scrollTo($scrollPosition);
        }

        $I->seeElementInDOM($checkboxSelector);
        $I->wait(3);
        $I->dontSeeCheckboxIsChecked($checkboxSelector);
        $I->checkOption($checkboxSelector);

        self::saveSettings($I);

        $I->seeCheckboxIsChecked($checkboxSelector);

        return $this;

    }

    /**
    * disableSetting() -> Will disable the respective checkbox under rtmedia-settings tab.
    */
    public function disableSetting($I,$strLabel,$checkboxSelector,$scrollPosition='no'){

        $I = $this->tester;

        self::gotoDisplayTab($I);

        $I->see($strLabel);

        if('no' !== $scrollPosition){
            $I->scrollTo($scrollPosition);
        }

        $I->seeElementInDOM($checkboxSelector);
        $I->wait(3);
        $I->seeCheckboxIsChecked($checkboxSelector);
        $I->uncheckOption($checkboxSelector);

        self::saveSettings($I);

        $I->dontSeeCheckboxIsChecked($checkboxSelector);

        return $this;

    }

}
