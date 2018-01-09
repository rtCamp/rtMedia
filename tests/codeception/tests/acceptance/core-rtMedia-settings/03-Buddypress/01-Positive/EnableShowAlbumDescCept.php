<?php

/**
 * Scenario : Enable Show Album description.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Enable Show Album description.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );

    $verifyEnableStatusOfMediaInProfileCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableMediaInProLabel, ConstantsPage::$enableMediaInProCheckbox );
	if ( $verifyEnableStatusOfMediaInProfileCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$enableMediaInProCheckbox );
        $settings->saveSettings();
    }

    $verifyEnableStatusOfAlbumCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableAlbumLabel, ConstantsPage::$enableAlbumCheckbox );
	if ( $verifyEnableStatusOfAlbumCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$enableAlbumCheckbox );
        $settings->saveSettings();
    }

    $verifyEnableStatusOfAlbumDescCheckbox = $settings->verifyStatus( ConstantsPage::$strShowAlbumDescLabel, ConstantsPage::$albumDescCheckbox );
	if ( $verifyEnableStatusOfAlbumDescCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$albumDescCheckbox );
        $settings->saveSettings();
    }

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->createNewAlbum();
    $buddypress->editAlbumDesc();
?>
