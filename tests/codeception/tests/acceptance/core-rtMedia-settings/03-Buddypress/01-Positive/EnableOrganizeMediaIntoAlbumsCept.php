<?php

/**
* Scenario : Allow user to Organize media into albums.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $saveSession = true;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Check if the user is allowed to Organize media into albums.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, $saveSession );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$strEnableAlbumLabel, ConstantsPage::$enableAlbumCheckbox );

    $gotoMediaPage = new BuddypressSettingsPage( $I );
    $gotoMediaPage->gotoMedia( ConstantsPage::$userName );

    $I->seeElement( ConstantsPage::$mediaAlbumLink );

?>
