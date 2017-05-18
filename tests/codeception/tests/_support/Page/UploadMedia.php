<?php

namespace Page;

use Page\Constants as ConstantsPage;

class UploadMedia {

	public static $galleryLable = '.rtm-gallery-title';
	public static $uploadLink = '.rtmedia-upload-media-link';
	public static $selectFileButton = 'input#rtMedia-upload-button';
	public static $fileList = '#rtmedia_uploader_filelist';
	public static $uploadTermsCheckbox = '#rtmedia_upload_terms_conditions';
	public static $uploadMediaButton = '.start-media-upload';
	public static $firstChild = 'ul.rtm-gallery-list li:first-child';
	public static $commentSubmitButton = '.rt_media_comment_submit';
	public static $whatIsNewTextarea = '#whats-new';
	public static $scrollPosOnActivityPage = '#user-activity';
	public static $postUpdateButton = 'input#aw-whats-new-submit';
	public static $uploadFile = 'div.moxie-shim.moxie-shim-html5 input[type=file]';
	public static $uploadFromActivity = 'div#whats-new-options div input[type="file"]';
	public static $commentTextArea = '#comment_content';
	public static $uploadContainer = '#rtmedia-upload-container';
	public static $mediaButtonOnActivity = 'button.rtmedia-add-media-button';
	protected $tester;

	public function __construct( \AcceptanceTester $I ) {
		$this->tester = $I;
	}

	/**
	 * gotoMediaPage() -> Will take the user to media page
	 */
	public function gotoMediaPage( $userName, $link ) {

		$I = $this->tester;

		$url = 'members/' . $userName . '/media';

		$I->amonPage( $url );
		$I->waitForElement( ConstantsPage::$profilePicture, 5 );

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );
		$I->seeElement( $link );
		$I->click( $link );

		$I->waitForElement( ConstantsPage::$profilePicture, 5 );

		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );

		$I->wait( 5 );
	}

	/**
	 * uploadTermasCheckbox() -> will check `terms of service checkbox`
	 */
	public function uploadTermasCheckbox() {

		$I = $this->tester;

		$I->dontSeeCheckboxIsChecked( self::$uploadTermsCheckbox );
		$I->checkOption( self::$uploadTermsCheckbox ); //Assuming that "rtMedia Uplaod terms" plugin is enabled
	}

	/**
	 * uploadMedia() -> Will perform necessary steps to upload media. In this case it will work for image media type.
	 */
	// public function uploadMedia( $userName, $mediaFile, $link ){
	public function uploadMedia( $userName, $mediaFile ) {

		$I = $this->tester;

		$I->seeElement( ConstantsPage::$mediaPageScrollPos );
		$I->scrollTo( ConstantsPage::$mediaPageScrollPos );
		// $I->seeElement( $link );
		// $I->click( $link );
		//
        // // $I->wait( 5 );
		// $I->waitForElement( $link, 10);
		// $I->seeElementInDOM( ConstantsPage::$mediaPageScrollPos );
		// $I->scrollTo( ConstantsPage::$mediaPageScrollPos );
		// $I->wait( 5 );

		$I->waitForElementVisible( self::$uploadLink, 20 );
		// $I->seeElement( self::$uploadLink );
		$I->click( self::$uploadLink );
		// $I->wait( 5 );
		$I->waitForElement( self::$uploadContainer, 10 );
		// $I->seeElement( self::$uploadContainer);
		// $I->wait( 5 );
		$I->seeElementInDOM( self::$selectFileButton );

		$I->attachFile( self::$uploadFile, $mediaFile );
		// $I->wait( 10 );
		$I->waitForElement( ConstantsPage::$fileList, 20 );
	}

	/**
	 * uploadMediaUsingStartUploadButton() -> Will the media when 'Direct Upload' is not enabled
	 */
	// public function uploadMediaUsingStartUploadButton( $userName, $mediaFile, $link ){
	public function uploadMediaUsingStartUploadButton( $userName, $mediaFile ) {

		$I = $this->tester;

		// self::uploadMedia( $userName, $mediaFile, $link );
		self::uploadMedia( $userName, $mediaFile );

		$I->seeElement( self::$uploadMediaButton );
		$I->click( self::$uploadMediaButton );

		// $I->wait( 10 );
		$I->waitForElementNotVisible( ConstantsPage::$fileList, 20 );
	}

	/**
	 * uploadMediaDirectly() -> Will upload the media when 'Direct Upload' is enabled
	 */
	public function uploadMediaDirectly( $userName, $mediaFile ) {

		$I = $this->tester;

		self::uploadMedia( $userName, $mediaFile );

		// $I->wait( 3 );
		$I->waitForElementNotVisible( ConstantsPage::$fileList, 20 );
	}

	/**
	 * addStatus() -> Will perform the necessary steps to add status
	 */
	public function addStatus() {

		$I = $this->tester;

		$I->seeElementInDOM( self::$scrollPosOnActivityPage );
		$I->scrollTo( self::$scrollPosOnActivityPage );

		$I->seeElementInDOM( self::$whatIsNewTextarea );
		$I->click( self::$whatIsNewTextarea );

		// $I->wait( 3 );
		$I->waitForElementVisible( self::$postUpdateButton, 10 );
	}

	public function postStatus( $status ) {

		$I = $this->tester;

		self::addStatus();

		$I->fillfield( self::$whatIsNewTextarea, $status );
		$I->seeElement( ConstantsPage::$privacyDropdown );

		$I->click( self::$postUpdateButton );
		$I->waitForText( $status, 20 );
		;
		// $I->reloadPage();
	}

	/**
	 * uploadMediaFromActivity() -> Will upload the media from activity page when it is enabled from dashboard
	 */
	public function uploadMediaFromActivity( $mediaFile ) {

		$I = $this->tester;

		self::addStatus();

		$I->fillfield( self::$whatIsNewTextarea, "test from activity stream" );
		$I->seeElement( self::$mediaButtonOnActivity );
		$I->attachFile( self::$uploadFromActivity, $mediaFile );
		// $I->wait( 10 );
		$I->waitForElement( ConstantsPage::$fileList, 20 );

		$I->click( self::$postUpdateButton );
		// $I->wait( 10 );
		$I->waitForElementNotVisible( ConstantsPage::$fileList, 20 );
	}

	/**
	 * bulkUploadMediaFromActivity() -> Will upload the media in bulk from activity page when it is enabled from dashboard
	 */
	public function bulkUploadMediaFromActivity( $mediaFile, $numOfMedia ) {

		$I = $this->tester;
		$bulkUploadStatus = 'test from activity stream while bulk upload';

		self::addStatus();

		$I->fillfield( self::$whatIsNewTextarea, $bulkUploadStatus );
		// $I->wait( 3 );
		$I->seeElement( self::$mediaButtonOnActivity );

		//if $numOfMedia > 0 then it will execute if condition else for $numOfMedia = 0 it will execute else part
		if ( $numOfMedia > 0 ) {
			for ( $i = 0; $i < $numOfMedia + 1; $i ++ ) {

				$I->attachFile( self::$uploadFromActivity, $mediaFile );
				// $I->wait( 5 );
				$I->waitForElement( ConstantsPage::$fileList, 20 );
			}
		} else {
			$tempMedia = 5;
			for ( $i = 0; $i < $tempMedia; $i ++ ) {
				$I->attachFile( self::$uploadFromActivity, $mediaFile );
				// $I->wait( 5 );
				$I->waitForElement( ConstantsPage::$fileList, 20 );
			}
		}

		$I->click( self::$postUpdateButton );
		$I->waitForElementNotVisible( ConstantsPage::$fileList, 20 );
		$I->reloadPage();
		// $I->wait( 20 );
	}

	/**
	 * firstThumbnailMedia() -> Will click on the first element(media thumbnail) from the list
	 */
	public function firstThumbnailMedia() {

		$I = $this->tester;

		$I->click( self::$firstChild );
		// $I->wait( 10 );
		$I->waitForElement( ConstantsPage::$mediaContainer, 10 );
	}

}
