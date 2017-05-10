<?php

/**
* Scenario : To check if media tab for group.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $saveSession = true;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To check if media tab is enabled for group' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password, $saveSession );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$buddypressTab, ConstantsPage::$buddypressTabUrl );
    $settings->verifyEnableStatus( ConstantsPage::$strEnableMediaInGrpLabel, ConstantsPage::$enableMediaInGrpCheckbox );

    $I->amOnPage( '/wp-admin/options-general.php?page=bp-components/' ); //Goto Settings->Buddypres page to enable User Group in front end
    $I->waitForElement( ConstantsPage::$grpTableRow, 10);
    $I->seeElement( ConstantsPage::$enableUserGrpCheckbox );
    $I->checkOption( ConstantsPage::$enableUserGrpCheckbox );
    $I->seeElement( ConstantsPage::$saveBPSettings );
    $I->click( ConstantsPage::$saveBPSettings );
    // $I->waitForElement( ConstantsPage::$grpTableRow, 10);
    $I->wait( 5 );

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoGroup();

    $temp = $buddypress->countGroup( ConstantsPage::$groupListSelector );
    echo "Total no. of groups = ".$temp;

    if( $temp > 0 ){

        $buddypress->checkMediaInGroup();
        $I->seeElement( ConstantsPage::$mediaLinkOnGroup );

    }else{

        $buddypress->createGroup();
        echo "group is created!";
        $buddypress->checkMediaInGroup();
        $I->seeElement( ConstantsPage::$mediaLinkOnGroup );
    }

?>
