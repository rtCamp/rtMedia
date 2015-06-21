/*
@author : Prabuddha Chakraborty

*/



module.exports ={

  USERNAME:  'prabuddha',
   PASSWORD:  'QlNC0qIT4RHphf6',

   TESTAUTHOR: 'TestAuthor',
   TESTAUTHORPASSWORD: 'testa',
   TESTEDITOR: 'TestEditor',
   TESTEDITORPASSWORD: 'teste',
   TESTCONTRIBUTOR: 'TestContributor',
   TESTCONTRIBUTORPASSWORD: 'testc',
   TESTSUBSCRIBER: 'TestSubscriber',
   TESTSUBSCRIBERPASSWORD: 'tests',
   TESTADMIN: 'TestAdmin',
   TESTADMINPASSWORD: 'TESTA',

   urls: {
    // LOGIN: 'http://prabuddha.rtcamp.net'
      LOGIN: 'http://rtmediatest.com'         //'http://akashqatester.rtcamp.net'

   },


  path: {

    TEST_IMAGE: './resources/test_data/images/test.jpg',
    TEST_MUSIC: './resources/test_data/music/mpthreetest.mp3',
    TEST_VIDEO: './resources/test_data/videos/testmpfour.mp4',


  },

/*selectors of rtMedia settings here .. */

  selectors: {

    SUBMIT: '#bp_media_settings_form .bottom .rtmedia-settings-submit',           //data.selectors.SUBMIT

    buddypress: {

      BUDDYPRESS: '#tab-rtmedia-bp',
      ENABLE_MEDIA_PROFILE: 'input[name="rtmedia-options[buddypress_enableOnProfile]"]',
      ENABLE_MEDIA_GROUP:    'input[name="rtmedia-options[buddypress_enableOnGroup]"]',
      ENABLE_UPLOAD_ACTIVITY: 'input[name="rtmedia-options[buddypress_enableOnActivity]"]',
      ENABLE_MEDIA_ALBUM: 'input[name="rtmedia-options[general_enableAlbums]"]'

    },

    display: {

      DISPLAY: '#tab-rtmedia-display',
      ENABLE_COMMENT: 'input[name="rtmedia-options[general_enableComments]"]',
      ENABLE_LIGHTBOX: 'input[name="rtmedia-options[general_enableLightbox]"]',
      MEDIA_PER_PAGE: 'input[name="rtmedia-options[general_perPageMedia]"]',
      SELECT_LOADMORE: 'input[value="load_more"]',
      SELECT_PAGINATION: 'input[value="pagination"]'


    },

    mediasizes: {
      MEDIASIZES: '#tab-rtmedia-sizes',
      MUSIC_ACTIVITY_PLAYER_WIDTH: 'input[name="rtmedia-options[defaultSizes_music_activityPlayer_width]"]',
      MUSIC_SINGLEPLAYER_WIDTH: 'input[name="rtmedia-options[defaultSizes_music_singlePlayer_width]"]',
      PHOTO_LARGE_WIDTH: 'input[name="rtmedia-options[defaultSizes_photo_large_width]"]',
      PHOTO_LARGE_HEIGHT: 'input[name="rtmedia-options[defaultSizes_photo_large_height]"]',
      PHOTO_MEDIUM_WIDTH: 'input[name="rtmedia-options[defaultSizes_photo_medium_width]"]',
      PHOTO_MEDIUM_HEIGHT: 'input[name="rtmedia-options[defaultSizes_photo_medium_height]"]',
      PHOTO_THUMBNAIL_HEIGHT: 'input[name="rtmedia-options[defaultSizes_photo_thumbnail_height]"]' ,
      PHOTO_THUMBNAIL_WIDTH: 'input[name="rtmedia-options[defaultSizes_photo_thumbnail_width]"]',
      VIDEO_ACTIVITY_PLAYER_WIDTH: 'input[name="rtmedia-options[defaultSizes_video_activityPlayer_width]"]',
      VIDEO_ACTIVITY_PLAYER_HEIGHT: 'input[name="rtmedia-options[defaultSizes_video_activityPlayer_height]"]',
      VIDEO_SINGLE_PLAYER_WIDTH: 'input[name="rtmedia-options[defaultSizes_video_singlePlayer_width]"]',
      VIDEO_SINGLE_PLAYER_HEIGHT: 'input[name="rtmedia-options[defaultSizes_video_singlePlayer_height]"]'

    },

    mediatypes: {
      MEDIATYPES: '#tab-rtmedia-types',
      ENABLE_PHOTO: 'input[name="rtmedia-options[allowedTypes_photo_enabled]"]',
      ENABLE_MUSIC: 'input[name="rtmedia-options[allowedTypes_music_enabled]"]',
      ENABLE_VIDEO: 'input[name="rtmedia-options[allowedTypes_video_enabled]"]'
    },

    privacy: {
      PRIVACY: "#tab-rtmedia-privacy",
      ENABLE_PRIVACY: 'input[name="rtmedia-options[privacy_enabled]"]',
      PRIVATE: 'input[value="60"]',
      LOGGEDIN: 'input[value="20"]',
      PUBLIC: 'input[value="0"]',
      PRIVACY_OVERRIDE: 'input[name="rtmedia-options[privacy_userOverride]"]'
      },

    customcss: {
      CUSTOM_CSS: "#tab-rtmedia-custom-css-settings",        //'input[name="rtmedia-options[styles_custom]"]'
      DEFAULT_ENABLE: 'input[name="rtmedia-options[styles_enabled]"]',
      CUSTOM_CSS_TEXTAREA: "#rtmedia-custom-css"             //rtmedia-options[styles_custom]
    },

    othersettings: {
      OTHERSETTINGS: '#tab-rtmedia-general',
      SHOW_ADMIN_MENU: 'input[name="rtmedia-options[general_showAdminMenu]"]',
      ADD_FOOTER_LINK: 'input[name="rtmedia-options[rtmedia_add_linkback]"]'

    }


  }

};
