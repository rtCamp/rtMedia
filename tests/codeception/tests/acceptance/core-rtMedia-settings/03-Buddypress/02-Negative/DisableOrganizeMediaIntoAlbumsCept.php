<?php

/**
 * Scenario : Disable organize media into albums.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable organize media into albums.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );
    $verifyDisableStatusOfAlbumCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableAlbumLabel, ConstantsPage::$enableAlbumCheckbox );

	if ( $verifyDisableStatusOfAlbumCheckbox ) {
		$settings->disableSetting( ConstantsPage::$enableAlbumCheckbox );
		$settings->saveSettings();
	} else {
		echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
	}

    $verifyEnableStatusOfMediaInProfileCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableMediaInProLabel, ConstantsPage::$enableMediaInProCheckbox );
	if ( $verifyEnableStatusOfMediaInProfileCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$enableMediaInProCheckbox );
        $settings->saveSettings();
    }

    $gotoMediaPage = new BuddypressSettingsPage( $I );
    $gotoMediaPage->gotoMedia();

    $I->dontSeeElement( ConstantsPage::$mediaAlbumLink );
?>
