/*
 @author: Prabuddha Chakraborty
 TestCase: Enable Media in Group Negative Case
 */



module.exports = {
  'Step one :Disable media in group from settings' :function(browser){
    var data = browser.globals;
    browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()
          .click(data.selectors.buddypress.BUDDYPRESS)
          .pause(500)
          .getAttribute(data.selectors.buddypress.ENABLE_MEDIA_GROUP, "checked", function(result) {

                  if(result.value){

                    browser.click(data.selectors.buddypress.ENABLE_MEDIA_GROUP);
                    browser.click(data.selectors.SUBMIT);


                  }else{
                    console.log('check box is already disabled');

                } })
            .pause(1000)

          },

          'step two: Check frontend ' : function (browser) {
            var data = browser.globals;
            browser
            .goToGroups()
            .click('#groups-list .is-member .item .item-title a')       //select a group
            .assert.elementNotPresent('#rtmedia-media-nav')
            .wplogout()
            .end();





  }
}
