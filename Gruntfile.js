module.exports = function (grunt) {
    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        watch: {
			sass: {
				files: ['app/assets/admin/css/sass/**/*.{scss,sass}', 'app/assets/css/sass/**/*.{scss,sass}'],
				tasks: ['sass']
			},
			postcss: {
				files: ['app/assets/admin/css/*.css', 'app/assets/css/*.css'],
				tasks: ['shell:postcss']
			},
			js: {
				files: ['app/assets/js/rtMedia.js', 'app/assets/admin/js/scripts.js'],
				tasks: ['terser']
			}
		},


        sass: {
            dist: {
                options: {
                    style: 'expanded',
                    sourceMap: false
                },
                files: {
                    'app/assets/admin/css/admin.css': 'app/assets/admin/css/sass/admin.scss',
                    'app/assets/css/rtmedia.css': 'app/assets/css/sass/rtmedia.scss',
                }
            },
            minify: {
                options: {
                    style: 'compressed',
                    sourceMap: false
                },
                files: {
                    'app/assets/admin/css/admin.min.css': 'app/assets/admin/css/sass/admin.scss',
                    'app/assets/css/rtmedia.min.css': 'app/assets/css/sass/rtmedia.scss',
                }
            }
        },

        shell: {
            postcss: {
                command: 'npx postcss app/assets/admin/css/*.css app/assets/css/*.css --config postcss.config.js --replace'
            }
        },

        terser: {
            options: {
                format: {
                    comments: false // Removes comments from minified files
                }
            },
            frontend: {
                files: {
                    'app/assets/js/rtmedia.min.js': ['app/assets/js/rtMedia.js']
                }
            },
            backend: {
                files: {
                    'app/assets/admin/js/admin.min.js': ['app/assets/admin/js/scripts.js']
                }
            },
            godam: {
                files: {
                    'app/assets/js/godam-integration.min.js': ['app/assets/js/godam-integration.js']
                }
            },
            godam_ajax_refresh: {
                files: {
                    'app/assets/js/godam-ajax-refresh.min.js': ['app/assets/js/godam-ajax-refresh.js']
                }
            },
            rtmedia_admin: {
                files: {
                    'app/assets/admin/js/rtmedia-admin.min.js': ['app/assets/admin/js/rtmedia-admin.js']
                }
            },
            importer: {
                files: {
                    'app/assets/admin/js/importer.min.js': ['app/assets/admin/js/importer.js']
                }
            },
            wp67_media_element: {
                files: {
                    'app/assets/js/wp67-mediaelement-init.min.js': ['app/assets/js/wp67-mediaelement-init.js']
                }
            },
            migration: {
                files: {
                    'app/assets/admin/js/migration.min.js': ['app/assets/admin/js/migration.js']
                }
            },
            rtm_upload_terms: {
                files: {
                    'app/assets/js/rtm-upload-terms.min.js': ['app/assets/js/rtm-upload-terms.js']
                }
            },
            admin_upload_terms: {
                files: {
                    'app/assets/js/admin-upload-terms.min.js': ['app/assets/js/admin-upload-terms.js']
                }
            }
        }
    });

    // Register tasks
    grunt.registerTask('default', ['sass', 'shell:postcss', 'terser', 'watch']);
    grunt.registerTask('build', ['sass:minify', 'shell:postcss', 'terser']);
};
