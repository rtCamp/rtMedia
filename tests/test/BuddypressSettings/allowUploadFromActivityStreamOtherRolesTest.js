/*
 @author: Prabuddha Chakraborty
 TestCase: Check Allow upload from activity stream for other users
 */

module.exports = {

  'Step One : Enable Allow upload from activity stream from Admin' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.buddypress.BUDDYPRESS)
          .pause(2000)

          //select checkbox switch
          .getAttribute(data.selectors.buddypress.ENABLE_UPLOAD_ACTIVITY, "checked", function(result) {

                  if(result.value){

                    browser.verify.ok(result.value, 'Checkbox is selected');
                    console.log('check box is already enabled');

                  }else{

                    browser.click(data.selectors.buddypress.ENABLE_UPLOAD_ACTIVITY);
                    browser.click(data.selectors.SUBMIT);

                } })
            .pause(1000)
            .wplogout()


          },





        'step two: Login from TestEditor' : function (browser) {
    var data = browser.globals;
          browser
          .wplogin(data.urls.LOGIN,data.TESTEDITOR,data.TESTEDITORPASSWORD)
          .goToActivity()
          .assert.elementPresent("#rtmedia-add-media-button-post-update")
          .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_IMAGE))
          .setValue('#whats-new','Check  Media Type : Photos (jpg, jpeg, png, gif) ')
          .click('#aw-whats-new-submit')
          .assert.containsText("#buddypress", "test")
        .wplogout()





      },

    'step three: Login from Author' : function (browser) {

        var data = browser.globals;

        browser
        .wplogin(data.urls.LOGIN,data.TESTAUTHOR,data.TESTAUTHORPASSWORD)
        .goToActivity()
        .assert.elementPresent("#rtmedia-add-media-button-post-update")
        .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_IMAGE))
        .setValue('#whats-new','Check  Media Type : Photos (jpg, jpeg, png, gif) ')
        .click('#aw-whats-new-submit')
        .assert.containsText("#buddypress", "test")
      .wplogout()
    },

    'step four: Login from Subscriber' : function (browser) {
      var data = browser.globals;
      browser
      .wplogin(data.urls.LOGIN,data.TESTSUBSCRIBER,data.TESTSUBSCRIBERPASSWORD)
      .goToActivity()
      .assert.elementPresent("#rtmedia-add-media-button-post-update")
      .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_IMAGE))
      .setValue('#whats-new','Check  Media Type : Photos (jpg, jpeg, png, gif) ')
      .click('#aw-whats-new-submit')
      .assert.containsText("#buddypress", "test")
    .wplogout()
  },

  'step five: Login from Subscriber' : function (browser) {

    var data = browser.globals;
    browser
    .wplogin(data.urls.LOGIN,data.TESTCONTRIBUTOR,data.TESTCONTRIBUTORPASSWORD)
    .goToActivity()
    .assert.elementPresent("#rtmedia-add-media-button-post-update")
    .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_IMAGE))
    .setValue('#whats-new','Check  Media Type : Photos (jpg, jpeg, png, gif) ')
    .click('#aw-whats-new-submit')
    .assert.containsText("#buddypress", "test")
  .wplogout()
  .end();
}





};
