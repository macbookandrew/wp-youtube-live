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
                'js/youtube-live-player.min.js': ['js/youtube-live-player.js'],
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
  });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-browser-sync');
    grunt.registerTask('default', [
        'browserSync',
        'watch',
    ]);
};
