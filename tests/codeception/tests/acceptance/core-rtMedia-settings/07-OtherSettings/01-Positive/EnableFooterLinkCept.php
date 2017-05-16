<?php

/**
 * Scenario : To check if rtMedia footer link is enabled.
 */
use Page\Login as LoginPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\Constants as ConstantsPage;

$scrollToTab = ConstantsPage::$mediaSizesTab;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'To check Add a link to rtMedia in footer is enabled.' );

$loginPage = new LoginPage( $I );
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$otherSeetingsTab, ConstantsPage::$otherSeetingsTabUrl, $scrollToTab );
$settings->verifyEnableStatus( ConstantsPage::$footerLinkLabel, ConstantsPage::$footerLinkCheckbox );

$I->amOnPage( '/' );
$I->seeElement( ConstantsPage::$footerLink );
?>
