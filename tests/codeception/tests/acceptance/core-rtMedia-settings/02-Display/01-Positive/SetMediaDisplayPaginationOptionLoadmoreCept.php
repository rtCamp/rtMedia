<?php

/**
* Scenario : To check if Load More - Media display pagination option is enabled
*Pre condition : The available no of Media should be  > ConstantsPage::$numOfMediaPerPage
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollPosition = ConstantsPage::$numOfMediaTextbox;
    $saveSession = true;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if Load More - Media display pagination option is enabled' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, $saveSession );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
    $settings->verifySelectOption( ConstantsPage::$strMediaDisplayPaginationLabel, ConstantsPage::$loadmoreRadioButton, $scrollPosition );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia( ConstantsPage::$userName );

    $I->seeElementInDOM( ConstantsPage::$loadMore );

?>
