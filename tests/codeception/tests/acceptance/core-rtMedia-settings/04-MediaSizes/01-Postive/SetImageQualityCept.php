<?php

/**
* Scenario : To set JPEG/JPG Image quality.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $scrollPos = ConstantsPage::$customCssTab;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set JPEG/JPG Image quality.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab,ConstantsPage::$mediaSizesTabUrl );
    $settings->setValue( ConstantsPage::$imgQualityLabel, ConstantsPage::$imgQualityTextbox, ConstantsPage::$imgQualityValue, $scrollPos );

    $I->amOnPage( '/' );
    $I->wait( 5 );
    
    $logout = new LogoutPage( $I );
    $logout->logout();
?>
