<?php

/**
* Scenario : Enable Json API.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $scrollToTab = ConstantsPage::$mediaSizesTab;
    $scrollPos = ConstantsPage::$otherSeetingsTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Enable Json API.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$otherSeetingsTab, ConstantsPage::$otherSeetingsTabUrl, $scrollToTab );
    $settings->verifyEnableStatus( ConstantsPage::$strEnableJsonDataLabel, ConstantsPage::$enableJsonDataCheckbox, $scrollPos );

?>
