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
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyDisableStatus( ConstantsPage::$strEnableAlbumLabel, ConstantsPage::$enableAlbumCheckbox );

    $gotoMediaPage = new BuddypressSettingsPage( $I );
    $gotoMediaPage->gotoMedia( ConstantsPage::$userName );

    $I->dontSeeElement( ConstantsPage::$mediaAlbumLink );

?>
