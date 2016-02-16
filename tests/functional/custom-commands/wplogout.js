

exports.command = function() {
var client = this;
var data = client.globals;
var logouturl = data.URLS.LOGIN + "/wp-login.php?action=logout" ;

client
    .pause(1000)
    .url(logouturl)
    .pause(2000)
    .click('#error-page > p:nth-child(2) > a')
    .pause(2000)
    .getTitle(function(title) {
        console.log("Logged out....");
      })

return this;
};
