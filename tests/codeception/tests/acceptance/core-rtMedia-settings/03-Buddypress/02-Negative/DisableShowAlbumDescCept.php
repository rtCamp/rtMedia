<?php

/**
 * Scenario : Disable Show Album description.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable Show Album description.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );

    $checkEnableMediaInProfile = $settings->verifyStatus( ConstantsPage::$strEnableMediaInProLabel, ConstantsPage::$enableMediaInProCheckbox );
	if ( $checkEnableMediaInProfile ) {
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

    $verifyDisableStatusOfAlbumDescCheckbox = $settings->verifyStatus( ConstantsPage::$strShowAlbumDescLabel, ConstantsPage::$albumDescCheckbox );

	if ( $verifyDisableStatusOfAlbumDescCheckbox ) {
		$settings->disableSetting( ConstantsPage::$albumDescCheckbox );
		$settings->saveSettings();
	} else {
		echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
	}

    $buddypress = new BuddypressSettingsPage( $I );

    $buddypress->gotoAlbumPage();

    $I->seeElement( ConstantsPage::$firstAlbum );
    $I->click( ConstantsPage::$firstAlbum );
    $I->waitForElement( ConstantsPage::$profilePicture, 10 );

    $I->dontSeeElement( ConstantsPage::$albumDescSelector );
?>
