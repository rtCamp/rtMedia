<?php

/**
* Scenario : To check if media tab is disabled for group.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disabled media for group.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyDisableStatus( ConstantsPage::$strEnableMediaInGrpLabel, ConstantsPage::$enableMediaInGrpCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoGroup();

    $I->dontSeeElement( ConstantsPage::$mediaLinkOnGroup );
?>
