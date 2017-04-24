<?php

/**
* Scenario : To check if media tab is disabled on profile
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;


    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if media tab is disabled on profile' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyDisableStatus( ConstantsPage::$strEnableMediaInProLabel, ConstantsPage::$enableMediaInProCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoProfile( ConstantsPage::$userName );

    $I->dontSeeElement( ConstantsPage::$mediaLinkOnProfile );
?>
