<?php

namespace Page;

use Page\Constants as ConstantsPage;

class Login {

	protected $tester;

	public function __construct( \AcceptanceTester $I ) {
		$this->tester = $I;
	}

	public function loginAsAdmin( $wpUserName, $wpPassword, $saveSession = true ) {

		$I = $this->tester;

		$I->amOnPage( '/wp-admin' );

		// Will load the session saved in saveSessionSnapshot().
		if ( $I->loadSessionSnapshot( 'login' ) ) {
			echo nl2br( " skipping login steps... \n" );
			return;
		}

		if ( ! $saveSession ) {
			$I->waitForElement( ConstantsPage::$wpSubmitButton, 10 );
		}

		$I->seeElement( ConstantsPage::$wpUserNameField );
		$I->fillfield( ConstantsPage::$wpUserNameField, $wpUserName );

		$I->seeElement( ConstantsPage::$wpPasswordField );
		$I->fillfield( ConstantsPage::$wpPasswordField, $wpPassword );

		$I->click( ConstantsPage::$wpSubmitButton );
		$I->waitForElement( ConstantsPage::$wpDashboard, 10 );
		$I->waitForElement( ConstantsPage::$dashBoardMenu, 10 );

		// Will Save the Session
		if ( $saveSession ) {
			$I->saveSessionSnapshot( 'login' );
			echo nl2br( "Session Saved! \n" );
		} else {
			echo nl2br( "Session Not Saved! \n" );
		}

		$I->reloadPage();
		$I->maximizeWindow();
	}

}
