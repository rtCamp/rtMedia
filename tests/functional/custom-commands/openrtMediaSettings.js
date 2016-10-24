/* @author: Prabuddha Chakraborty */

exports.command = function(url,username,password) {
var client = this;
var data = client.globals;
var dash = data.URLS.LOGIN + '/wp-admin'
client
    .pause(1000)
    .url(dash)
    .waitForElementVisible('body', 3000)
    .pause(1005)
    .moveToElement('#toplevel_page_rtmedia-settings',2,2)
    .moveToElement('#toplevel_page_rtmedia-settings .wp-submenu a.wp-first-item',1,1)
    .click('#toplevel_page_rtmedia-settings .wp-submenu a.wp-first-item')
    .waitForElementVisible('body', 2000)
    .pause(1005)
    .getTitle(function(title) {
        console.log("We are in rtMedia settings Page :"+title);
      })

return this;
};
