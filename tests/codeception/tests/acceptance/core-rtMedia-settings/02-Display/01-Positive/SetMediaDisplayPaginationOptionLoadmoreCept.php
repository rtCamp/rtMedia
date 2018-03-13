<?php

/**
 * Scenario : To check if Load More - Media display pagination option is enabled
 * Pre condition : The available no of Media should be  > ConstantsPage::$numOfMediaPerPage
 */
    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;

    $scrollPosition = ConstantsPage::$numOfMediaTextbox;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if Load More - Media display pagination option is enabled' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoSettings( ConstantsPage::$displaySettingsUrl );
    $checkSelectionStatusOfLoadmoreRadioButton = $settings->verifyStatus( ConstantsPage::$strMediaDisplayPaginationLabel, ConstantsPage::$loadmoreRadioButton, $scrollPosition );

	if ( $checkSelectionStatusOfLoadmoreRadioButton ) {
        echo nl2br( "Option is already selected." . "\n" );
    } else {
        $settings->selectOption( ConstantsPage::$loadmoreRadioButton );
        $settings->saveSettings();
    }

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoMedia();

    $I->seeElementInDOM( ConstantsPage::$loadMore );
?>
