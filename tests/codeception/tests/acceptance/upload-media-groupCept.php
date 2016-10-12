<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('Upload an image at a group');
require('demo-login.php');
$I->amonPage('/groups/');
// Open a group
$I->click('div.item-title');
$I->click('Club Football');
$I->see("What's new in Club Football, Demo?");
$I->click('#whats-new');
$I->fillfield('#whats-new','Upload in group');
$I->attachFile('input[type="file"]','hello.png');
$I->wait(5);
$I->click('Post Update');
$I->wait(10);


?>