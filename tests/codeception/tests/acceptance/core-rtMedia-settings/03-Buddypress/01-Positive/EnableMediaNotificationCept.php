<?php

/**
* Scenario : Allow user to enable media notification.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Check if the user is allowed to enable media notification.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$strMediaNotificationLabel, ConstantsPage::$mediaNotificationCheckbox );

    $I->amOnPage( '/' );
    $I->wait( 5 );
    
    $logout = new LogoutPage( $I );
    $logout->logout();

?>
