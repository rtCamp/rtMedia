<?php

/**
* Scenario : To disable the privacy settings for user.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To disable the privacy settings for user.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$privacyTab, ConstantsPage::$privacyTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$privacyLabel, ConstantsPage::$privacyCheckbox );
    $settings->verifyDisableStatus( ConstantsPage::$privacyUserOverrideLabel, ConstantsPage::$privacyUserOverrideCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoActivityPage( ConstantsPage::$userName );

    $I->dontSeeElement( ConstantsPage::$privacyDropdown );

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
