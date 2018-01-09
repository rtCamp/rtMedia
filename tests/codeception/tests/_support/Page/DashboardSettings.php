<?php

namespace Page;

use Page\Constants as ConstantsPage;

class DashboardSettings {

	public function route( $param ) {
		return static::$URL . $param;
	}

	protected $tester;

	public function __construct( \AcceptanceTester $I ) {
		$this->tester = $I;
	}

	// saveSettings() -> Will save the settings after any changes made by the user in backend.
	public function saveSettings( $customMsg = 'no' ) {

		$I = $this->tester;

		$I->seeElementInDOM( ConstantsPage::$saveSettingsButtonBottom );
		$I->executeJS( "jQuery('.rtm-button-container.bottom .rtmedia-settings-submit').click();" );

		if ( 'no' != $customMsg ) {
			// Verify Custom message
		} else {
			$I->waitForText( 'Settings saved successfully!', 30 );
		}
	}

	// gotoSettings() -> Will goto the requested url.
	public function gotoSettings( $url ) {

		$I = $this->tester;

		$I->amOnPage( $url );
		$I->waitForElement( ConstantsPage::$topSaveButton, 20 );
	}

	// setMediaSize() -> It will set the media size
	public function setMediaSize( $strLabel, $widthTextbox, $width, $heightTextbox = 'no', $height = 'no', $scrollPos = 'no' ) {

		$I = $this->tester;

		$I->see( $strLabel );

		if ( 'no' != $scrollPos ) {
			$I->scrollTo( $scrollPos );
		}

		$I->seeElementInDOM( $widthTextbox );
		$I->fillField( $widthTextbox, $width );

		if ( 'no' !== $heightTextbox && 'no' != $height ) {
			$I->seeElementInDOM( $heightTextbox );
			$I->fillField( $heightTextbox, $height );
		}

		self::saveSettings();
	}

	// enableSetting() -> Will enable the respective checkbox.
	public function enableSetting( $checkboxSelector ) {

		$I = $this->tester;

		$I->dontSeeCheckboxIsChecked( $checkboxSelector );

		if ( preg_match( '/"([^"]+)"/', $checkboxSelector, $m ) ) {
			$script = 'return document.getElementsByName("' . $m[ 1 ] . '")[0].click()';
			$I->executeJs( $script );
		}
	}

	//disableSetting() -> Will disable the respective checkbox.
	public function disableSetting( $checkboxSelector ) {

		$I = $this->tester;

		$I->seeCheckboxIsChecked( $checkboxSelector );

		if ( preg_match( '/"([^"]+)"/', $checkboxSelector, $m ) ) {
			$script = 'return document.getElementsByName("' . $m[ 1 ] . '")[0].click()';
			$I->executeJs( $script );
		}

	}

	// selectOption() -> Will select the respective radio button.
	public function selectOption( $radioButtonSelector ) {

		$I = $this->tester;

		$I->checkOption( $radioButtonSelector );
	}

	// setValue() -> Will fill the textbox
	public function setValue( $strLabel, $cssSelector, $valueToBeSet, $scrollPosition = 'no' ) {

		$I = $this->tester;

		$I->see( $strLabel );

		if ( 'no' != $scrollPosition ) {

			$I->scrollTo( $scrollPosition );
		}

		$I->seeElementInDOM( $cssSelector );
		$I->fillField( $cssSelector, $valueToBeSet );
	}

	// verifyStatus() -> Will verify and return the status of checkbox and/or radio button.
	public function verifyStatus( $strLabel, $cssSelector, $scrollPosition = 'no' ) {

		$I = $this->tester;

		if ( 'no' != $scrollPosition ) {

			$I->scrollTo( $scrollPosition );
		}

		$I->see( $strLabel );
		$I->seeElementInDOM( $cssSelector );

		return $I->grabAttributeFrom( $cssSelector, "checked" );

	}

	// disableDirectUpload() -> Will disable direct upload setting.
	public function disableDirectUpload() {

		$I = $this->tester;

		self::gotoSettings( ConstantsPage::$displaySettingsUrl );
	    $flag = self::verifyStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$scrollPosForDirectUpload );

	    if ( $flag ) {
	        self::disableSetting( ConstantsPage::$directUploadCheckbox );
	        self::saveSettings();
	    } else {
	        echo nl2br( ConstantsPage::$disabledSettingMsg . "\n" );
	    }

	}

	// enableRequestedMediaTypes() -> Will enable the requested media types
	public function enableRequestedMediaTypes( $strLabel, $mediaTypeCheckboxSelector ){

		$I = $this->tester;

		self::gotoSettings( ConstantsPage::$typesSettingsUrl );
		$verifyEnableStatusOfMediaTypeCheckbox = self::verifyStatus( $strLabel, $mediaTypeCheckboxSelector );

	    if ( $verifyEnableStatusOfMediaTypeCheckbox ) {
	        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
	    } else {
	        self::enableSetting( $mediaTypeCheckboxSelector );
	        self::saveSettings();
	    }

	}

	// enableUploadFromActivity() -> Will allow the user to upload them media from activity
	public function enableUploadFromActivity(){

		$I = $this->tester;

		self::gotoSettings( ConstantsPage::$buddypressSettingsUrl );

		$verifyEnableStatusOfUploadFromActivityCheckbox = self::verifyStatus( ConstantsPage::$strMediaUploadFromActivityLabel, ConstantsPage::$mediaUploadFromActivityCheckbox );

	    if ( $verifyEnableStatusOfUploadFromActivityCheckbox ) {
	        echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
	    } else {
	        self::enableSetting( ConstantsPage::$mediaUploadFromActivityCheckbox );
	        self::saveSettings();
	    }

	}

	// enableBPGroupComponent() -> Will enable the Grops Component from Setting
	public function enableBPGroupComponent(){

		$I = $this->tester;

		$I->amOnPage( ConstantsPage::$bpComponentsUrl );
		$I->waitForElement( ConstantsPage::$grpTableRow, 10 );
		$I->seeElement( ConstantsPage::$enableUserGrpCheckbox );

		if ( $I->grabAttributeFrom( ConstantsPage::$enableUserGrpCheckbox, "checked" ) == "true" ) {
			echo nl2br( ConstantsPage::$enabledSettingMsg . "\n" );
		} else {
			$I->checkOption( ConstantsPage::$enableUserGrpCheckbox );
			$I->seeElement( ConstantsPage::$saveBPSettings );
			$I->click( ConstantsPage::$saveBPSettings );
			$I->waitForElement( ConstantsPage::$saveMsgSelector, 20 );
		}
	}
}
