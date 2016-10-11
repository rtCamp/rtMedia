# README #



## What is this repository for?

This repository will contain automated test cases for rtmedia using codeception. Currently using http://codeception.com/for/wordpress wpcept package.

## Current Set up

Tests are in codeception branch.  rtMedia > tests > codeception

Under codeception directory there's the composer.json file.


## How do I get set up in Mac?

### Install composer globally


### Composer update

Pull this repo 

Change branch to codeception

Navigate to codeception directory and do `composer update`

This will install all dependencies in your local setup.


#### Clone this repo

`git clone git@git.rtcamp.com:jondo/snapbox-automated-testing.git`


## How to run tests



### Run the tests on separate tab
Navigate to the cloned directory snapbox-automated-testing:

`cd snapbox-automated-testing`


**To run test suite :** `nightwatch`

**To run individual cases :** `nightwatch --test tests/testOrderPunchBySelectingProductFromMyComputer.js --env chrome`

*Note: You may refer http://nightwatchjs.org/guide#running-tests in case of issues.*
