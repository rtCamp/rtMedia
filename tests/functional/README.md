**Automated testscript for rtMedia product**

Running tests:

Install node.js

Install nightwatch

`npm install -g nightwatch`

cd tests/functional/

1. $`npm install`

2. configure res/constants.js

      change site admin username/password

			`TESTADMINUSERNAME: 'ADMINUSER'`

    	 `TESTADMINPASSWORD: 'ADMINPASS'`


			change url of site
			```
				URLS: {

        		LOGIN: 'http://wp.localtest.me'
   					},
  		```


 Run to test

 $```nightwatch```
