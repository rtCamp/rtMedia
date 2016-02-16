/* @author: Prabuddha Chakraborty */

exports.command = function(url,username,password) {
var client = this;
var data = client.globals;
var dash = data.URLS.LOGIN + '/wp-admin/admin.php?page=rtmedia-settings'
client
    .pause(3000)
    .url(dash)
    .waitForElementVisible('body', 3000)
    .pause(5000)
    .getTitle(function(title) {
        console.log("We are in rtMedia settings Page :"+title);
      })

return this;
};
