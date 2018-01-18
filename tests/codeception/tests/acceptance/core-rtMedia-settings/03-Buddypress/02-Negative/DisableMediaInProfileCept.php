<?php

/**
 * Scenario : To check if media tab is disabled on profile
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if media tab is disabled on profile' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );
    $verifyDisableStatusOfMediaInProfileCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableMediaInProLabel, ConstantsPage::$enableMediaInProCheckbox );

	if ( $verifyDisableStatusOfMediaInProfileCheckbox ) {
		$settings->disableSetting( ConstantsPage::$enableMediaInProCheckbox );
		$settings->saveSettings();
	} else {
		echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
	}

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoProfile();

    $I->dontSeeElement( ConstantsPage::$mediaLinkOnProfile );
?>
