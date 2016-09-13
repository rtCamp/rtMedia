[![Build Status](https://travis-ci.org/rtCamp/rtMedia.svg?branch=master)](https://travis-ci.org/rtCamp/rtMedia)

**Automated testscript for rtMedia product**

Running tests:

* Install node.js

* Install nightwatch

`npm install -g nightwatch`

* `cd {project_root_folder}/tests/functional/`

* $```npm install```


* configure `res/constants.js`

    `change site admin username/password`

			  TESTADMINUSERNAME: 'ADMINUSER'

    	      TESTADMINPASSWORD: 'ADMINPASS'


		change url of site

				URLS: {

        		LOGIN: 'http://wp.localtest.me'
   					},



 Run to test

 $```nightwatch```

Or

 Docker Container for NightwatchJS test suite for rtMedia

 To build Docker Image with Nightwatchjs dependencies:
 
 $```docker build -t test/nightwatch-xvfb .```

 Run Test Suite (run from `\tests` folder):
 
 $```docker run -i --rm -v $(pwd):/test/ --name nightwatch-rtmedia test/nightwatch-xvfb  bash -c "npm install && xvfb-run --server-args='-screen 0, 1624x1068x24' nightwatch --group src/"```
