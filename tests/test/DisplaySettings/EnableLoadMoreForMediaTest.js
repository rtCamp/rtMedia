/*
 @author: Prabuddha Chakraborty
 TestCase: To Check Load More On Media Page
*/

module.exports = {

  'Step One : Enable LoadMore from rtmedia settings ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.display.DISPLAY)
          .pause(1000)

					.click(data.selectors.display.MEDIA_PER_PAGE)
					.clearValue(data.selectors.display.MEDIA_PER_PAGE)
					.setValue(data.selectors.display.MEDIA_PER_PAGE,"2")
					.click(data.selectors.display.SELECT_LOADMORE) //load more clicked
					.click(data.selectors.SUBMIT)

          .pause(1000)

          },


    'step two: Check on Frontend ' : function (browser) {

        browser
            .goToMedia()
            .pause(2000)
            .assert.elementPresent("#rtMedia-galary-next")
					  /*
						code for elements count using JQUERY */

            .url(function(result){
              var count,count2;
              var flag ;
              'use strict';
              var html = result.value;
              var env = require('jsdom').env;


              // first argument is url of the site
              env(html, function (errors, window) {
              console.log(errors);

               var $ = require('jquery')(window);
               count = $(".rtmedia-list-media").children().length;
               console.log("Total number of media on page: ")
               console.log(count);

               if(count<3){
                 flag = true;
                 console.log(flag);

               }else{
                 flag= false;
                 }



               browser.assert.equal(flag, true);

               });

            })

            .wplogout()
            .end();

        }


      };
