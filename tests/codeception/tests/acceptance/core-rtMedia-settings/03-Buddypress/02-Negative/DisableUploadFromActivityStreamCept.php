<?php

/**
 * Scenario : Disable upload from activity stream.
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable upload from activity stream.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );

    $verifyDisableStatusOfUploadFromActivityCheckbox = $settings->verifyStatus( ConstantsPage::$strMediaUploadFromActivityLabel, ConstantsPage::$mediaUploadFromActivityCheckbox );

	if ( $verifyDisableStatusOfUploadFromActivityCheckbox ) {
		$settings->disableSetting( ConstantsPage::$mediaUploadFromActivityCheckbox );
		$settings->saveSettings();
	} else {
		echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
	}

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivity();

    $I->dontSeeElementInDOM( ConstantsPage::$uploadButtonOnAtivityPage );
?>
