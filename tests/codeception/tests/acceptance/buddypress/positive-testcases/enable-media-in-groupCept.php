<?php

/**
* Scenario : To check if media tab appears for group.
*/

    use Page\Login as LoginPage;
    use Page\Constants as ConstantsPage;
    use Page\BuddypressSettings as BuddypressSettingsPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if media tab is disabled on profile');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin(ConstantsPage::$userName,ConstantsPage::$password);

    $settings = new DashboardSettingsPage($I);
    $settings->gotoTab($I,ConstantsPage::$buddypressTab,ConstantsPage::$buddypressTabUrl);
    $settings->verifyEnableStatus($I,ConstantsPage::$strEnableMediaInGrpLabel,ConstantsPage::$enableMediaInGrpCheckbox);

    $buddypress = new BuddypressSettingsPage( $I );
    $buddypress->gotoGroup();

    $temp = $buddypress->countGroup( ConstantsPage::$groupListSelector );
    echo $temp;

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
