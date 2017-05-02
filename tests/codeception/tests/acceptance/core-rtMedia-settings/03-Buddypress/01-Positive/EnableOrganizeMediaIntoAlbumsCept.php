<?php

/**
* Scenario : Allow user to Organize media into albums.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Check if the user is allowed to Organize media into albums.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$strEnableAlbumLabel, ConstantsPage::$enableAlbumCheckbox );

    $gotoMediaPage = new BuddypressSettingsPage( $I );
    $gotoMediaPage->gotoMedia( ConstantsPage::$userName );

    $I->seeElement( ConstantsPage::$mediaAlbumLink );

    $logout = new LogoutPage( $I );
    $logout->logout();

?>
