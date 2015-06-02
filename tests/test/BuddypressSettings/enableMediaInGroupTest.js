/*
 @author: Prabuddha Chakraborty
 TestCase: To Check Enable Media in Group 
 */

module.exports = {
  'Step one :Enable media in group from settings' :function(browser){
    var data = browser.globals;
    browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()
          .click(data.selectors.buddypress.BUDDYPRESS)
          .pause(500)
          .getAttribute(data.selectors.buddypress.ENABLE_MEDIA_GROUP, "checked", function(result) {
            //  console.log(result); //used for debug
                  if(result.value){
                          browser.verify.ok(result.value, 'Checkbox is selected');
                          console.log('check box is already enabled');
                  }else{
                          browser.click(data.selectors.buddypress.ENABLE_MEDIA_GROUP);
                          browser.click(data.selectors.SUBMIT);
                } })
            .pause(1000)

          },

          'step two: Check frontend ' : function (browser) {
            var data = browser.globals;
            browser
            .goToGroups()
            .click('#groups-list .is-member .item .item-title a')
            .assert.elementPresent('#rtmedia-media-nav')
            .click('#rtmedia-media-nav')


            .click('#rtm_show_upload_ui')
            .click('.rtm-select-files')
            .setValue('input[type=file]', require('path').resolve(data.path.TEST_IMAGE))
            .click('.start-media-upload')
            .pause(6000)
            .refresh()
            .getText('.rtmedia-list-item a.rtmedia-list-item-a .rtmedia-item-title h4',function(result){

                browser.assert.equal(result.value, 'test', 'image uploaded successfully');


           })

            .wplogout()
            .end();





  }
}
