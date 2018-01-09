<?php

namespace Page;

use Page\Constants as ConstantsPage;

class UploadMedia {

	protected $tester;

	public function __construct( \AcceptanceTester $I ) {
		$this->tester = $I;
	}

	// uploadMedia() -> Will perform necessary steps to attach media.
	public function uploadMedia( $mediaFile ) {

		$I = $this->tester;

		$I->seeElement( ConstantsPage::$mediaPageScrollPos );
		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );
		$I->waitForElementVisible( ConstantsPage::$uploadLink, 10 );
		$I->click( ConstantsPage::$uploadLink );
		$I->waitForElement( ConstantsPage::$uploadContainer, 20 );
		$I->seeElementInDOM( ConstantsPage::$selectFileButton );
		$I->attachFile( ConstantsPage::$uploadFile, $mediaFile );
		$I->waitForElement( ConstantsPage::$fileList, 20 );
	}

	//uploadMediaUsingStartUploadButton() -> Will the media when 'Direct Upload' is disabled
	public function uploadMediaUsingStartUploadButton() {

		$I = $this->tester;

		$I->waitForElementVisible( ConstantsPage::$uploadMediaButton, 20 );
		$I->click( ConstantsPage::$uploadMediaButton );

		$I->waitForElementNotVisible( ConstantsPage::$fileList, 20 );
	}

	// uploadMediaDirectly() -> Will upload the media when 'Direct Upload' is enabled
	public function uploadMediaDirectly() {

		$I = $this->tester;

		$I->waitForElementNotVisible( ConstantsPage::$fileList, 20 );
	}


	// addStatus() -> Will add the string received as a parameter to textarea and post it.
	public function addStatus( $status = 'no' ) {

		$I = $this->tester;

		$I->seeElementInDOM( ConstantsPage::$whatIsNewTextarea );
		$I->click( ConstantsPage::$whatIsNewTextarea );

		if( 'no' != $status ){
			$I->fillfield( ConstantsPage::$whatIsNewTextarea, $status );
		}

		$I->waitForElementVisible( ConstantsPage::$postUpdateButton, 10 );
	}

	// uploadMediaFromActivity() -> Will upload the media from activity
	public function uploadMediaFromActivity( $mediaFile, $numOfMedia, $allowed = 'no' ) {

		$I = $this->tester;

		$I->seeElement( ConstantsPage::$uploadButtonOnAtivityPage );

		for ( $i = $numOfMedia; $i > 0; $i-- ) {
			$I->attachFile( ConstantsPage::$uploadFromActivity, $mediaFile );
		}

		$I->waitForElement( ConstantsPage::$fileList, 20 );

		if( 'no' != $allowed ){
			$I->waitForElementVisible( ConstantsPage::$fileNotSupportedSelector, 20 );
		}

		$I->click( ConstantsPage::$postUpdateButton );
		$I->waitForElementNotVisible( ConstantsPage::$fileList, 20 );
	}

}
