module.exports = function (grunt) {
  grunt.initConfig({
    // Watch task config
    watch: {
        javascript: {
            files: ["js/*.js", "!js/*.min.js"],
            tasks: ['uglify'],
        },
    },
    uglify: {
        custom: {
            files: {
                'js/wp-youtube-live.min.js': ['js/wp-youtube-live.js'],
            },
        },
    },
    browserSync: {
        dev: {
            bsFiles: {
                src : ['**/*.php', '**/*.js', '!node_modules'],
            },
            options: {
                watchTask: true,
                proxy: "http://dev.abc.dev",
            },
        },
    },
    addtextdomain: {
        options: {
            textdomain: 'wp-youtube-live',
        },
        target: {
            files: {
                src: [ '*.php', '**/*.php', '!node_modules/**', '!php-tests/**', '!bin/**' ]
            }
        }
    },
    wp_readme_to_markdown: {
        custom: {
            files: {
                'README.md': ['readme.txt'],
            }
        },
        options: {
            screenshot_url: 'http://ps.w.org/wp-youtube-live/assets/{screenshot}.png',
        }
    },
    makepot: {
        target: {
            options: {
                domainPath: '/languages',
                mainFile: 'wp-youtube-live.php',
                potFilename: 'wp-youtube-live.pot',
                potHeaders: {
                    poedit: true,
                    'x-poedit-keywordslist': true
                },
                type: 'wp-plugin',
                updateTimestamp: true
            }
        }
    },
  });

    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-browser-sync');
    grunt.registerTask('i18n', ['addtextdomain', 'makepot']);
    grunt.registerTask('readme', ['wp_readme_to_markdown']);
    grunt.registerTask('default', [
        'browserSync',
        'watch',
    ]);
};
