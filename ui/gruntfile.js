module.exports = function(grunt) {
   // Load the plugins that provide tasks.
   grunt.loadNpmTasks('grunt-contrib-uglify');
   grunt.loadNpmTasks('grunt-contrib-jshint');
   grunt.loadNpmTasks('grunt-contrib-copy');

   // Project configuration.
   grunt.initConfig({
      pkg : grunt.file.readJSON('package.json'),
      src : {
         jsFiles : [ 'src/admin/**/*.js', 'src/common/**/*.js', 'src/workspace/**/*.js' ]
      },
      uglify : {
         options : {
            banner : '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd hh:MM:ss") %> */\n'
         },
         build : {
            src : [ '<%= src.jsFiles %>' ],
            dest : 'build/<%= pkg.name %>.min.js'
         }
      },
      copy : {
         vendor : {
            files : [ 
              { src : 'vendor/angular/angular.min.js', dest : 'dist/'},
              { src : 'vendor/angular/angular.min.js.map', dest : 'dist/'},
              { src : 'vendor/angular-route/angular-route.min.js', dest : 'dist/'},
              { src : 'vendor/angular-route/angular-route.min.js.map', dest : 'dist/'},
              { src : 'vendor/angular-messages/angular-messages.min.js', dest : 'dist/'},
              { src : 'vendor/angular-messages/angular-messages.min.js.map', dest : 'dist/'},
              { src : 'vendor/angular-sanitize/angular-sanitize.min.js', dest : 'dist/'},
              { src : 'vendor/angular-sanitize/angular-sanitize.min.js.map', dest : 'dist/'},
              { src : 'vendor/angular-bootstrap/ui-bootstrap-tpls.min.js', dest : 'dist/'},
              { src : 'vendor/bootstrap/dist/css/bootstrap.min.css', dest : 'dist/'},
              { src : 'vendor/bootstrap/dist/css/bootstrap-theme.min.css', dest : 'dist/'},
              { src : 'vendor/bootstrap/dist/fonts/*', dest : 'dist/'},
              { src : 'vendor/angular-dialog-service/dist/dialogs.min.css', dest : 'dist/'},
              { src : 'vendor/angular-dialog-service/dist/dialogs.min.js', dest : 'dist/'},
              { src : 'vendor/ng-table/ng-table.min.js', dest : 'dist/'},
              { src : 'vendor/ng-table/ng-table.map', dest : 'dist/'},
              { src : 'vendor/ng-table/ng-table.min.css', dest : 'dist/'},
              { src : 'vendor/ng-file-upload/angular-file-upload-shim.min.js', dest : 'dist/'},
              { src : 'vendor/ng-file-upload/angular-file-upload.min.js', dest : 'dist/'},
              { src : 'vendor/components-font-awesome/css/font-awesome.min.css', dest : 'dist/'},
              { src : 'vendor/leaflet/dist/leaflet.css', dest : 'dist/'},
              { src : 'vendor/leaflet/dist/leaflet.js', dest : 'dist/'},
              { src : 'vendor/leaflet/dist/images/*', dest : 'dist/'},
              { src : 'vendor/leaflet-plugins/layer/tile/Google.js', dest : 'dist/'},
              { src : 'vendor/angular-leaflet-directive/dist/angular-leaflet-directive.min.js', dest : 'dist/'},
              { src : 'vendor/textAngular/dist/textAngular-sanitize.min.js', dest : 'dist/'},
              { src : 'vendor/textAngular/dist/textAngular.min.js', dest : 'dist/'}
           ]
         },
         common : {
            expand: true, cwd: 'src/', src: 'common/**', dest : 'dist/'
         },
         admin : {
            expand: true, cwd: 'src/', src: 'admin/**', dest : 'dist/'
         },
         workspace : {
            expand: true, cwd: 'src/', src: 'workspace/**', dest : 'dist/'
       },
         main : {
            src : 'src/index.html', dest : 'dist/index.html'
        }
      },
      jshint : {
         files : [ 'gruntFile.js', '<%= src.jsFiles %>' ],
         options : {
            curly : true,
            eqeqeq : true,
            immed : true,
            latedef : true,
            newcap : true,
            noarg : true,
            sub : true,
            boss : true,
            eqnull : true,
            smarttabs : true,
            globals : {}
         }
      }
   });

   // Default task(s).
   grunt.registerTask('default', [ 'jshint', 'uglify' ]);

};