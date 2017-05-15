<?php

namespace Page;

use Page\Constants as ConstantsPage;

class Logout {

	protected $tester;

	public function __construct( \AcceptanceTester $I ) {
		$this->tester = $I;
	}

	public function logout() {

		$I = $this->tester;

		$I->seeElement( ConstantsPage::$metaSection );
		$I->scrollTo( ConstantsPage::$metaSection );

		$I->seeElement( ConstantsPage::$logoutLink );
		$I->click( ConstantsPage::$logoutLink );
		// $I->wait( 5 );
		$I->waitForElement( ConstantsPage::$logoutMsg, 10 );
	}

}
