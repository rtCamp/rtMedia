<?php

/**
 * Scenario : To check if gallery media search is disable
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if gallery media search is disable' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
    $verifyDisableStatusOfGalleryMediaSearchCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableGalleryMediaSearchLabel, ConstantsPage::$mediaSearchCheckbox );

    if ( $verifyDisableStatusOfGalleryMediaSearchCheckbox ) {
        $settings->disableSetting( ConstantsPage::$mediaSearchCheckbox );
        $settings->saveSettings();
    } else {
        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
    }


    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $I->dontSeeElement( ConstantsPage::$mediaSearchSelector );
?>
