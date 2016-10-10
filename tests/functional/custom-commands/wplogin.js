


exports.command = function(url,username,password) {
var client = this;
var loginurl = url + "/wp-admin" ;

client
   .url(loginurl)
   .pause(500)
   .waitForElementVisible('body', 2000)
   .setValue('#user_login', username)
   .setValue('#user_pass', password)
   .click('#wp-submit')

return this;
};
