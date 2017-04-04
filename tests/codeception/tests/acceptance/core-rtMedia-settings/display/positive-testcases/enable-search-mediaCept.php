<?php

/**
* Scenario : To Check if media search is enabled
*/
    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To Check if media search is enabled' );

    $loginPage = new LoginPage( $I);
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$strMediaSearchLabel, ConstantsPage::$mediaSearchCheckbox );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );

    $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

    $I->seeElement( ConstantsPage::$mediaSeachSelector );


?>
