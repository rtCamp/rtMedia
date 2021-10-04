<?php

/**
 * Scenario : To check if media tab for group.
 */
	use Page\Login as LoginPage;
	use Page\Constants as ConstantsPage;
	use Page\DashboardSettings as DashboardSettingsPage;
	use Page\BuddypressSettings as BuddypressSettingsPage;

	$I = new AcceptanceTester( $scenario );
	$I->wantTo( 'To check if media tab is enabled for group' );

	$loginPage = new LoginPage( $I );
	$loginPage->loginAsAdmin();

	$settings = new DashboardSettingsPage( $I );
	$settings->enableBPGroupComponent();
	$settings->gotoSettings( ConstantsPage::$buddypressSettingsUrl );


	$verifyEnableStatusOfMediaInGroupCheckbox = $settings->verifyStatus( ConstantsPage::$strEnableMediaInGrpLabel, ConstantsPage::$enableMediaInGrpCheckbox );
	if ( $verifyEnableStatusOfMediaInGroupCheckbox ) {
        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
    } else {
        $settings->enableSetting( ConstantsPage::$enableMediaInGrpCheckbox );
        $settings->saveSettings();
    }

	$buddypress = new BuddypressSettingsPage( $I );
	$buddypress->gotoGroup();

	$I->waitForElementVisible( 'div.entry-content', 10 );

	$totalCount = $buddypress->countGroup( ConstantsPage::$groupListSelector ); 
	echo "Total no. of groups = " . $totalCount;

	if ( $totalCount > 0 ) {

		$buddypress->checkMediaInGroup();
		$I->seeElement( ConstantsPage::$mediaLinkOnGroup );
	} else {

		$buddypress->createGroup();
		echo "group is created!";
		$I->waitForElementVisible( ConstantsPage::$groupNameLink, 10 );
		$buddypress->checkMediaInGroup();
		$I->seeElement( ConstantsPage::$mediaLinkOnGroup );
	}
?>
