/*
 @author: Prabuddha Chakraborty
 TestCase: Enable Media in Profile Other Users
 */

module.exports = {

  'Step One : Enable media in profile From Admin Menu ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.buddypress.BUDDYPRESS)
          .pause(2000)

          //select checkbox switch
          .getAttribute(data.selectors.buddypress.ENABLE_MEDIA_PROFILE, "checked", function(result) {
            //  console.log(result); //used for debug
                  if(result.value){
                          browser.verify.ok(result.value, 'Checkbox is selected');
                          console.log('check box is already enabled');
                  }else{
                          browser.click(data.selectors.buddypress.ENABLE_MEDIA_PROFILE);
                          browser.click(data.selectors.SUBMIT);
                } })
            .pause(1000)
            .wplogout()

          },


        'step two: Login from TestEditor' : function (browser) {
            var data = browser.globals;
                  browser
                  .wplogin(data.urls.LOGIN,data.TESTEDITOR,data.TESTEDITORPASSWORD)
                  .goToProfile()
                  .click('#user-media')
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





              },

              'step three: Login from Author' : function (browser) {

                var data = browser.globals;

                browser
                .wplogin(data.urls.LOGIN,data.TESTAUTHOR,data.TESTAUTHORPASSWORD)
                .goToProfile()
                .click('#user-media')
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
            },

            'step three: Login from Subscriber' : function (browser) {
              var data = browser.globals;
              browser
              .wplogin(data.urls.LOGIN,data.TESTSUBSCRIBER,data.TESTSUBSCRIBERPASSWORD)
              .goToProfile()
              .click('#user-media')
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
          },

          'step three: Login from Subscriber' : function (browser) {

            var data = browser.globals;
            browser
            .wplogin(data.urls.LOGIN,data.TESTCONTRIBUTOR,data.TESTCONTRIBUTORPASSWORD)
            .goToProfile()
            .click('#user-media')
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
















};
