/*
 @author: Prabuddha Chakraborty
 TestCase: To Check Organise Media In album Other Users
 */

module.exports = {
  'Step One : Enable media in profile From Admin Acount and logout ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.buddypress.BUDDYPRESS)
          .pause(2000)

        
          .getAttribute(data.selectors.buddypress.ENABLE_MEDIA_ALBUM, "checked", function(result) {
                  if(result.value){
                          browser.verify.ok(result.value, 'Checkbox is selected');
                          console.log('check box is already enabled');
                  }else{
                          browser.click(data.selectors.buddypress.ENABLE_MEDIA_ALBUM);
                          browser.click(data.selectors.SUBMIT);
                } })
            .pause(1000)
            .wplogout()


          },

        'step three: Login from Author' : function (browser) {

          var data = browser.globals;

          browser
          .wplogin(data.urls.LOGIN,data.TESTAUTHOR,data.TESTAUTHORPASSWORD)
          .goToMedia()

          .pause(500)
          .click('#rtmedia-nav-item-albums')
          .pause(1000)
          .click('#rtm-media-options-list .js .rtmedia-action-buttons')
          .click('#rtm-media-options-list .js .rtm-options .rtmedia-reveal-modal')
          .pause(500)
          .click('#rtmedia_album_name')
          .setValue('input[id="rtmedia_album_name"]', 'New_Album')
          .click('#rtmedia_create_new_album')
          .waitForElementVisible('.rtmedia-success', 1500)
          .verify.containsText('.rtmedia-success',"New_Album album created successfully.")
          .pause(500)
          .click('.mfp-close')
          .pause(200)
        .wplogout()
      },

      'step three: Login from Subscriber' : function (browser) {
        var data = browser.globals;
        browser
        .wplogin(data.urls.LOGIN,data.TESTSUBSCRIBER,data.TESTSUBSCRIBERPASSWORD)
        .goToMedia()

        .pause(1000)
        .click('#rtmedia-nav-item-albums')
        .pause(1000)
        .click('#rtm-media-options-list .js .rtmedia-action-buttons')
        .click('#rtm-media-options-list .js .rtm-options .rtmedia-reveal-modal')
        .pause(500)
        .click('#rtmedia_album_name')
        .setValue('input[id="rtmedia_album_name"]', 'New_Album')
        .click('#rtmedia_create_new_album')
        .waitForElementVisible('.rtmedia-success', 1500)
        .verify.containsText('.rtmedia-success',"New_Album album created successfully.")
        .pause(500)
        .click('.mfp-close')
        .pause(200)

      .wplogout()
    },

    'step Four: Login from Contributor' : function (browser) {

      var data = browser.globals;
      browser
      .wplogin(data.urls.LOGIN,data.TESTCONTRIBUTOR,data.TESTCONTRIBUTORPASSWORD)
      .goToMedia()

      .pause(1000)
      .click('#rtmedia-nav-item-albums')
      .pause(1000)
      .click('#rtm-media-options-list .js .rtmedia-action-buttons')
      .click('#rtm-media-options-list .js .rtm-options .rtmedia-reveal-modal')
      .pause(500)
      .click('#rtmedia_album_name')
      .setValue('input[id="rtmedia_album_name"]', 'New_Album')
      .click('#rtmedia_create_new_album')
      .waitForElementVisible('.rtmedia-success', 1500)
      .verify.containsText('.rtmedia-success',"New_Album album created successfully.")
      .pause(500)
      .click('.mfp-close')
      .pause(200)
    .wplogout()
    .end();
  }

  };
