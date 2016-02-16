


exports.command = function(url,username,password) {
var client = this;
var loginurl = url + "/wp-admin" ;

client
   .url(loginurl)
   .pause(2000)
   .waitForElementVisible('body', 2000)
   .setValue('input[id="user_login"]', username)
   .setValue('input[id="user_pass"]', password)
   .click('input[type=submit]')

return this;
};
