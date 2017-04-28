<?php

/**
* Scenario : enable activity for media likes.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'Enable activity for media likes.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$strActivityMediaLikeLabel, ConstantsPage::$activityMediaLikeCheckbox );

?>
