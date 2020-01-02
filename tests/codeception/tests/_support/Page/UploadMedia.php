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
	public function uploadMediaUsingStartUploadButton( $clickonCheckbox = 'no' ) {

		$I = $this->tester;

		$I->waitForElementVisible( ConstantsPage::$uploadMediaButton, 20 );
		$I->click( ConstantsPage::$uploadMediaButton );

		if ( 'no' != $clickonCheckbox ) {
			$I->waitForElementVisible( ConstantsPage::$alertMessageClass , 10);
		} else {
			$I->waitForElementNotVisible( ConstantsPage::$fileList, 20 );
		}

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

	// Tick the checkbox for upload terms services
	public function checkUploadTerms(){

		$I = $this->tester;

		$I->seeElement( ConstantsPage::$uploadTermsCheckbox );
	    $I->dontSeeCheckboxIsChecked( ConstantsPage::$uploadTermsCheckbox );
		$I->checkOption( ConstantsPage::$uploadTermsCheckbox );

	}

	// uploadMediaFromActivity() -> Will upload the media from activity
	public function uploadMediaFromActivity( $mediaFile, $numOfMedia, $allowed = 'no' ) {

		$I = $this->tester;

		$I->seeElement( ConstantsPage::$uploadButtonOnAtivityPage );

		for ( $i = $numOfMedia; $i > 0; $i-- ) {
			$I->attachFile( ConstantsPage::$uploadFromActivity, $mediaFile );
		}

		$I->waitForElement( ConstantsPage::$fileList, 20 );

		$I->click( ConstantsPage::$postUpdateButton );
		if( 'no' != $allowed ){
			$I->waitForElementVisible('.rt_alert_msg', 5);
			$I->see('Please check terms of service.');
		}else{
				$I->waitForElementNotVisible( ConstantsPage::$fileList, 30 );
		}

	}

	// uploadMediaFromActivityWhenMediaFormatNotSupported() -> will show the error when media is disabled from back end.
	public function uploadMediaFromActivityWhenMediaFormatNotSupported( $mediaFile ){

		$I = $this->tester;

		$I->seeElement( ConstantsPage::$uploadButtonOnAtivityPage );
		$I->attachFile( ConstantsPage::$uploadFromActivity, $mediaFile );
		$I->waitForElement( ConstantsPage::$fileList, 20 );

		$I->waitForElementVisible( ConstantsPage::$fileNotSupportedSelector, 20 );
		echo nl2br( "Medi is not uploaded.. \n" );

	}

}
