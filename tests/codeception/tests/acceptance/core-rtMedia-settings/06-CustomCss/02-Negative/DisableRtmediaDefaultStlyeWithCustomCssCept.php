<?php

/**
 * Scenario : set custom css when default rtmedia style is disabled.
 */
use Page\Login as LoginPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\Constants as ConstantsPage;
use Page\BuddypressSettings as BuddypressSettingsPage;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Set custom css code when defulat rtMedia style is disabled.' );

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Set custom css code when default rtMedia style is disabled.' );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$customCssTab, ConstantsPage::$customCssTabUrl );
$settings->verifyDisableStatus( ConstantsPage::$defaultStyleLabel, ConstantsPage::$defaultStyleCheckbox );
$settings->setValue( ConstantsPage::$customCssLabel, ConstantsPage::$cssTextarea, ConstantsPage::$customCssValue );

$buddypress = new BuddypressSettingsPage( $I );
$buddypress->gotoActivityPage( ConstantsPage::$userName );

$I->seeInPageSource( ConstantsPage::$customCssValue );
?>
