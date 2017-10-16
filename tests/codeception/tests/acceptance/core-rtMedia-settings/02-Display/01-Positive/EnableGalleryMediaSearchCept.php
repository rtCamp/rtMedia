<?php

/**
 * Scenario : To check if gallery media search is enable
 */
use Page\Login as LoginPage;
use Page\Constants as ConstantsPage;
use Page\BuddypressSettings as BuddypressSettingsPage;
use Page\DashboardSettings as DashboardSettingsPage;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'To check if gallery media search is enable' );

$loginPage = new LoginPage( $I );
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$displayTab, ConstantsPage::$displayTabUrl );
$settings->verifyEnableStatus( ConstantsPage::$strEnableGalleryMediaSearchLabel, ConstantsPage::$mediaSearchCheckbox );

$buddypress = new BuddypressSettingsPage( $I );
$buddypress->gotoMedia( ConstantsPage::$userName );

$I->seeElement( ConstantsPage::$mediaSearchSelector );
?>
