'use strict';
module.exports = function ( grunt ) {

	// load all grunt tasks matching the `grunt-*` pattern
	// Ref. https://npmjs.org/package/load-grunt-tasks
	require( 'load-grunt-tasks' )( grunt );

	grunt.initConfig( {
		// SCSS and Compass
		// Ref. https://npmjs.org/package/grunt-contrib-compass
		compass: {
			frontend: {
				options: {
					config: 'config.rb',
					force: true
				}
			},
			// Admin Panel CSS
			backend: {
				options: {
					sassDir: 'app/assets/admin/css/sass/',
					cssDir: 'app/assets/admin/css/'
				}
			}
		},
		// Uglify
		// Compress and Minify JS files in js/rtp-main-lib.js
		// Ref. https://npmjs.org/package/grunt-contrib-uglify
		uglify: {
			options: { banner: '/*! \n * rtMedia JavaScript Library \n * @package rtMedia \n */'
			},
			build: {
				src: [
					'app/assets/admin/js/vendors/rtp-tabs.js',
					'app/assets/admin/js/scripts.js',
					'app/assets/admin/js/admin.js'
				],
				dest: 'app/assets/admin/js/admin-min.js'
			}
		},
		// Watch for hanges and trigger compass and uglify
		// Ref. https://npmjs.org/package/grunt-contrib-watch
		watch: {
			compass: { files: [ '**/*.{scss,sass}' ],
				tasks: [ 'compass' ]
			},
			uglify: {
				files: '<%= uglify.build.src %>',
				tasks: [ 'uglify' ]
			} },
	} );

	// WordPress Deploy Task
	// grunt.registerTask('default', ['wordpressdeploy']);

	// Register Task
	grunt.registerTask( 'default', [ 'watch' ] );
};