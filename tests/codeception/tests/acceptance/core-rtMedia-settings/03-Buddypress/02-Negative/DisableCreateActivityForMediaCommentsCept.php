<?php

/**
* Scenario : Allow user to disable activity for media comments.
*/

    use Page\Login as LoginPage;
    use Page\Logout as LogoutPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Disable activity for media comments.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyDisableStatus( ConstantsPage::$strActivityMediaCommentLabel, ConstantsPage::$activityMediaCommentCheckbox );

    $I->amOnPage( '/' );
    $I->wait( 5 );

    $logout = new LogoutPage( $I );
    $logout->logout();
?>
