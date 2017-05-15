<?php

/**
 * Scenario : To check Admin bar menu integration is enabled.
 */
use Page\Login as LoginPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\Constants as ConstantsPage;

$scrollToTab = ConstantsPage::$mediaSizesTab;
$scrollPos = ConstantsPage::$displayTab;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'To check if Admin bar menu integration is enabled.' );

$loginPage = new LoginPage( $I );
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$otherSeetingsTab, ConstantsPage::$otherSeetingsTabUrl, $scrollToTab );
$settings->verifyEnableStatus( ConstantsPage::$jsonApiLabel, ConstantsPage::$jsonApiCheckbox, $scrollPos );

$I->amOnPage( '/' );
$I->seeElement( ConstantsPage::$rtMediaAdminbar );
?>
