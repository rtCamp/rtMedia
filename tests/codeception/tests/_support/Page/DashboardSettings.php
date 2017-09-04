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

	/**
	 * saveSettings() -> Will save the settings after any changes made by the user in backend.
	 */
	public function saveSettings() {

		$I = $this->tester;

		$I->seeElementInDOM( ConstantsPage::$saveSettingsButtonBottom );
		$I->scrollTo( ConstantsPage::$saveSettingsButtonBottom );
		$I->click( ConstantsPage::$saveSettingsButtonBottom );
		$I->waitForText( 'Settings saved successfully!', 30 );
	}

	/**
	 * gotortMediaSettings() -> Will goto rtmedia-settings tab.
	 */
	public function gotortMediaSettings() {

		$I = $this->tester;

		$I->click( ConstantsPage::$rtMediaSettings );
		$I->waitForElement( ConstantsPage::$buddypressTab, 10 );
	}

	/**
	 * gotoTab() -> Will goto the respective tab under rtmedia-settings tab.
	 */
	public function gotoTab( $tabSelector, $tabUrl, $tabScrollPosition = 'no' ) {

		$I = $this->tester;

		self::gotortMediaSettings();

		$urlStr = ConstantsPage::$rtMediaSettingsUrl . $tabUrl;

		$I->seeElementInDOM( $tabSelector );

		if ( 'no' !== $tabScrollPosition ) {
			$I->scrollTo( $tabScrollPosition );
		}

		$I->click( $tabSelector );
		$I->waitForElement( ConstantsPage::$topSaveButton, 5 );
	}

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

	/**
	 * enableSetting() -> Will enable the respective checkbox under rtmedia-settings tab.
	 */
	public function enableSetting( $checkboxSelector ) {

		$I = $this->tester;

		$I->dontSeeCheckboxIsChecked( $checkboxSelector );

		if ( preg_match( '/"([^"]+)"/', $checkboxSelector, $m ) ) {
			$script = 'return document.getElementsByName("' . $m[ 1 ] . '")[0].click()';
			$I->executeJs( $script );
		}

		self::saveSettings();


		$I->seeCheckboxIsChecked( $checkboxSelector );
	}

	/**
	 * disableSetting() -> Will disable the respective checkbox under rtmedia-settings tab.
	 */
	public function disableSetting( $checkboxSelector ) {

		$I = $this->tester;

		$I->seeCheckboxIsChecked( $checkboxSelector );
//		$I->uncheckOption( $checkboxSelector );
		if ( preg_match( '/"([^"]+)"/', $checkboxSelector, $m ) ) {
			$script = 'return document.getElementsByName("' . $m[ 1 ] . '")[0].click()';
			$I->executeJs( $script );
		}

		self::saveSettings();

		$I->dontSeeCheckboxIsChecked( $checkboxSelector );
	}

	/**
	 * selectOption() -> Will select the radio button provided with css selector id
	 */
	public function selectOption( $radioButtonSelector ) {

		$I = $this->tester;

		$I->checkOption( $radioButtonSelector );

		self::saveSettings();
	}

	/**
	 * setValue() -> Will fill the textbox/textarea
	 */
	public function setValue( $strLabel, $cssSelector, $valueToBeSet, $scrollPosition = 'no' ) {

		$I = $this->tester;

		$I->see( $strLabel );

		if ( 'no' != $scrollPosition ) {

			$I->scrollTo( $scrollPosition );
		}

		$I->seeElementInDOM( $cssSelector );

		$I->fillField( $cssSelector, $valueToBeSet );

		self::saveSettings();
	}

	/**
	 * verifyEnableStatus() -> Will verify if the checkbox is enabled or not
	 */
	public function verifyEnableStatus( $strLabel, $cssSelector, $scrollPosition = 'no' ) {

		$I = $this->tester;

		if ( 'no' != $scrollPosition ) {

			$I->scrollTo( $scrollPosition );
		}
		$I->see( $strLabel );
		$I->seeElementInDOM( $cssSelector );

		if ( $I->grabAttributeFrom( $cssSelector, "checked" ) == "true" ) {
			echo nl2br( "Setting is already enabled \n" );
		} else {
			echo nl2br( "Call to enableSetting()... \n" );
			self::enableSetting( $cssSelector );
		}
	}

	/**
	 * verifyDisableStatus() -> Will verify if the checkbox is disabled or not
	 */
	public function verifyDisableStatus( $strLabel, $cssSelector, $scrollPosition = 'no' ) {

		$I = $this->tester;

		if ( 'no' != $scrollPosition ) {
			$I->scrollTo( $scrollPosition );
		}
		$I->see( $strLabel );
		$I->seeElementInDOM( $cssSelector );

		if ( $I->grabAttributeFrom( $cssSelector, "checked" ) == "true" ) {
			echo nl2br( "Call to disableSetting()... \n" );
			self::disableSetting( $cssSelector );
			return false;
		} else {
			echo nl2br( "Setting is already disabled \n" );
			return true;
		}
	}

	/**
	 * verifySelectOption() -> Will verify if the radio button is selected or not
	 */
	public function verifySelectOption( $strLabel, $cssSelector, $scrollPosition = 'no' ) {

		$I = $this->tester;

		$I->see( $strLabel );

		if ( 'no' != $scrollPosition ) {

			$I->scrollTo( $scrollPosition );
		}

		$I->seeElementInDOM( $cssSelector );

		if ( $I->grabAttributeFrom( $cssSelector, "checked" ) == "true" ) {
			echo nl2br( "Option is already selected... \n" );
		} else {
			echo nl2br( "Call to selectOption()... \n" );
			self::selectOption( $cssSelector );
		}
	}

	public function disableDirectUpload() {
		$I = $this->tester;
		$I->amOnPage( '/wp-admin/admin.php?page=rtmedia-settings#rtmedia-display' );
		$I->waitForElement( ConstantsPage::$displayTab, 10 );
		$status = $this->verifyDisableStatus( ConstantsPage::$strDirectUplaodCheckboxLabel, ConstantsPage::$directUploadCheckbox, ConstantsPage::$masonaryCheckbox ); //This will check if the direct upload is disabled
	}

}
