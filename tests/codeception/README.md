# README #


## What is this repository for?

This repository will contain automated test cases for rtmedia using codeception. Currently using http://codeception.com/for/wordpress wpcept package.

## Current Set up

Tests are in codeception branch under codeception directory. rtMedia > tests > codeception

Under codeception directory there's the composer.json file.


## How to set up?

### Install composer globally

Composer needs to be installed. 

### At codeception directory run composer update

Clone this repo 

Change branch to codeception

Navigate to rtMedia > tests > codeception directory and run `composer update`

This will install all dependencies in your local setup.


### Update .yml files as per requirements

Update acceptance.suite.yml and other yml files as per requirements.

## How to run tests

Navigate to codeception directory and run

`wpcept run acceptance exampleCept.php`

**Note:** If vendor/bin is not added to path, then you need to run

`vendor/bin/wpcept run acceptance exampleCept.php`
