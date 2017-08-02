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
		
$I->moveMouseOver( '#wp-admin-bar-my-account' );
$I->executeJS("jQuery('#wp-admin-bar-my-account .ab-sub-wrapper').css({'display':'block'});");
// $I->waitForElement(ConstantsPage::$metaSection);
//		$I->seeElement( ConstantsPage::$metaSection );
//		$I->scrollTo( ConstantsPage::$metaSection );

//		$I->seeElement( ConstantsPage::$logoutLink );
		$I->click( ConstantsPage::$logoutLink );
//		$I->waitForElement( ConstantsPage::$logoutMsg, 10 );
	}

}
