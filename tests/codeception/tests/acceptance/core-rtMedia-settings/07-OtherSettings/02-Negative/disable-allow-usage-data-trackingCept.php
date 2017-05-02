<?php

/**
* Scenario : Disable Data tracking.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $scrollToTab = ConstantsPage::$mediaSizesTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable data tracking.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$otherSeetingsTab, ConstantsPage::$otherSeetingsTabUrl, $scrollToTab );
    $settings->verifyDisableStatus( ConstantsPage::$strEnableUsageDataTrackingLabel, ConstantsPage::$enableUsageDataTrackingCheckbox);

    $I->amOnPage( '/' );
    $I->wait( 5 );
    
    $logout = new LogoutPage( $I );
    $logout->logout();

?>
