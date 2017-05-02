<?php

/**
* Scenario : Disable media notification.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Check if the user is allowed to disable media notification.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyDisableStatus( ConstantsPage::$strMediaNotificationLabel, ConstantsPage::$mediaNotificationCheckbox );

    $I->amOnPage( '/' );
    $I->wait( 5 );
    
    $logout = new LogoutPage( $I );
    $logout->logout();
?>
