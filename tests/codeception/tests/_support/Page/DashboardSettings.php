<?php
namespace Page;

class DashboardSettings
{
    public static $rtMediaSettingsUrl = '/wp-admin/admin.php?page=rtmedia-settings';
    public static $saveSettingsButtonBottom = '.rtm-button-container.bottom .rtmedia-settings-submit';
    public static $rtMediaSeetings = '#toplevel_page_rtmedia-settings';

    public function route($param)
    {
        return static::$URL.$param;
    }

    // protected $tester;
    // public function __construct(\AcceptanceTester $I)
    // {
    //     $this->tester = $I;
    // }

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
        $I->seeInCurrentUrl(self::$rtMediaSettingsUrl);

    }

    /**
    * gotoTab() -> Will goto the respective tab under rtmedia-settings tab.
    */
    public function gotoTab($I,$tabSelector,$tabUrl,$tabScrollPosition='no'){

        self::gotortMediaSettings($I);

        $urlStr = self::$rtMediaSettingsUrl.$tabUrl;

        $I->seeElementInDOM($tabSelector);

        if('no' !== $tabScrollPosition){
            $I->scrollTo($tabScrollPosition);
        }

        $I->click($tabSelector);
        $I->wait(5);
        $I->seeInCurrentUrl($urlStr);

        return $this;
    }

    /**
    * enableSetting() -> Will enable the respective checkbox under rtmedia-settings tab.
    */
    public function enableSetting($I,$strLabel,$checkboxSelector,$scrollPosition='no'){

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

    public function selectPaginationPattern($I,$strLabel,$radioButtonSelector){

        $I->see($strLabel);

        $I->checkOption($radioButtonSelector);

        $I->wait(3);

        self::saveSettings($I);

        return $this;

    }

    public function setValue($I,$strLabel,$cssSelector,$mediaPerPage){

        $I->see($strLabel);

        $I->seeElementInDOM($cssSelector);
        $I->fillField($cssSelector,$mediaPerPage);

        self::saveSettings($I);

        return $this;

    }

}
