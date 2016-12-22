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
        $I->wait(3);
    }

    public function setMediaSize($I,$strLabel,$widthTextbox,$width,$heightTextbox,$height){

        $I->see($strLabel);

        $I->seeElementInDOM($widthTextbox);
        $I->fillField($widthTextbox,$width);

        $I->seeElementInDOM($heightTextbox);
        $I->fillField($heightTextbox,$height);

        self::saveSettings($I);

        $I->wait(3);

    }

    /**
    * enableSetting() -> Will enable the respective checkbox under rtmedia-settings tab.
    */
    public function enableSetting($I,$checkboxSelector){

        $I->dontSeeCheckboxIsChecked($checkboxSelector);
        $I->checkOption($checkboxSelector);

        self::saveSettings($I);

        $I->seeCheckboxIsChecked($checkboxSelector);

    }

    /**
    * disableSetting() -> Will disable the respective checkbox under rtmedia-settings tab.
    */
    public function disableSetting($I,$checkboxSelector){

        $I->seeCheckboxIsChecked($checkboxSelector);
        $I->uncheckOption($checkboxSelector);

        self::saveSettings($I);

        $I->dontSeeCheckboxIsChecked($checkboxSelector);
    }

    /**
    * selectOption() -> Will select the radio button provided with css selector id
    */
    public function selectOption($I,$radioButtonSelector){

        $I->checkOption($radioButtonSelector);
        $I->wait(3);

        self::saveSettings($I);

    }

    /**
    * setValue() -> Will fill the textbox/textarea
    */
    public function setValue($I,$strLabel,$cssSelector,$valueToBeSet){

        $I->see($strLabel);

        $I->seeElementInDOM($cssSelector);
        $I->fillField($cssSelector,$valueToBeSet);

        self::saveSettings($I);

    }

    /**
    * verifyEnableStatus() -> Will verify if the checkbox is enabled or not
    */
    public function verifyEnableStatus($I,$strLabel,$cssSelector){

        $I->see($strLabel);

        $I->seeElementInDOM($cssSelector);
        $I->wait(3);

        if($I->grabAttributeFrom($cssSelector,"checked") == "true"){
            echo nl2br("Setting is already enabled \n");
        }else{
            echo nl2br("Call to enableSetting()... \n");
            self::enableSetting($I,$cssSelector);
        }
    }

    /**
    * verifyDisableStatus() -> Will verify if the checkbox is disabled or not
    */
    public function verifyDisableStatus($I,$strLabel,$cssSelector){

        $I->see($strLabel);

        $I->seeElementInDOM($cssSelector);
        $I->wait(3);

        if($I->grabAttributeFrom($cssSelector,"checked") == "true"){
            echo nl2br("Call to disableSetting()... \n");
            self::disableSetting($I,$cssSelector);
        }else{
            echo nl2br("Setting is already disabled \n");
        }
    }

    /**
    * verifySelectOption() -> Will verify if the radio button is selected or not
    */
    public function verifySelectOption($I,$strLabel,$cssSelector){

        $I->see($strLabel);

        $I->seeElementInDOM($cssSelector);
        $I->wait(3);

        if($I->grabAttributeFrom($cssSelector,"checked") == "true"){
            echo nl2br("Option is already selected... \n");
        }else{
            echo nl2br("Call to selectOption()... \n");
            self::selectOption($I,$cssSelector);
        }
    }
}
