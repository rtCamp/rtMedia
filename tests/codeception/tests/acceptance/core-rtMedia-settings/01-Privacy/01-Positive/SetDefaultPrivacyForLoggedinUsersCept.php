<?php

/**
 * Scenario : To set default privacy for logged in user.
 */

use Page\Login as LoginPage;
use Page\Logout as LogoutPage;
use Page\Constants as ConstantsPage;
use Page\UploadMedia as UploadMediaPage;
use Page\DashboardSettings as DashboardSettingsPage;
use Page\BuddypressSettings as BuddypressSettingsPage;

$status = 'For loggedin uses only..';

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'To set default privacy for logged in user.' );

$loginPage = new LoginPage( $I );
$loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, ConstantsPage::$saveSession );

$settings = new DashboardSettingsPage( $I );
$settings->gotoTab( ConstantsPage::$privacyTab, ConstantsPage::$privacyTabUrl );
$settings->verifyEnableStatus( ConstantsPage::$privacyLabel, ConstantsPage::$privacyCheckbox );
$settings->verifyEnableStatus( ConstantsPage::$privacyUserOverrideLabel, ConstantsPage::$privacyUserOverrideCheckbox );
$settings->verifySelectOption( ConstantsPage::$defaultPrivacyLabel, ConstantsPage::$loggedInUsersRadioButton );

$I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-bp' );
$I->waitForElement( ConstantsPage::$buddypressTab , 10);
$settings->verifyEnableStatus( ConstantsPage::$strMediaUploadFromActivityLabel, ConstantsPage::$mediaUploadFromActivityCheckbox );

$buddypress = new BuddypressSettingsPage( $I );
$buddypress->gotoActivityPage( ConstantsPage::$userName );

$uploadmedia = new UploadMediaPage( $I );
$uploadmedia->postStatus( $status );

$logout = new LogoutPage( $I );
$logout->logout();

$buddypress->gotoActivityPage( ConstantsPage::$userName );
$I->dontSeeElementInDOM( ConstantsPage::$activitySelector );

?>
