<?php

/**
 * Scenario : To check if media tab appears on profile
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if media tab appears on profile' );

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

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoProfile();

    $I->seeElement( ConstantsPage::$mediaLinkOnProfile );
?>
