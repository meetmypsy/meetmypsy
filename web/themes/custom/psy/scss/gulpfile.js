'use strict';
let gulp = require('gulp'),
  sass = require('gulp-sass'),
  autoprefixer = require('gulp-autoprefixer'),
  cleanCSS = require('gulp-clean-css'),
  sourceMaps = require('gulp-sourcemaps'),
  plumber = require('gulp-plumber');

// ====================   TACHE SCSS   =================================
function theme_sass() {
  return (
    gulp
      .src('./*.scss')
      .pipe(sourceMaps.init({largeFile: true}))
      .pipe(plumber())
      .pipe(sass())
      .pipe(autoprefixer())
      .pipe(cleanCSS())
      .pipe(sourceMaps.write('../css'))
      .pipe(gulp.dest('../css'))
  );
}

// ====================      WATCHER      ==============================
function watch() {
  gulp.watch('./*.scss', gulp.parallel(theme_sass))
}

// ====================      COMPILER      ==============================
gulp.task('make_sass', gulp.parallel(theme_sass));

// ====================     EXPORT TASKS    ===========================
exports.theme_sass = theme_sass;
exports.watch = watch;
exports.default = watch;
