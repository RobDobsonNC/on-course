var gulp         = require('gulp'),
	sass         = require('gulp-sass'),
	plumber      = require('gulp-plumber'),
	rename       = require('gulp-rename'),
	autoPrefixer = require('gulp-autoprefixer'),
	cssComb      = require('gulp-csscomb'),
	uglify 		= require('gulp-uglify'),
	babel 		= require('gulp-babel'),
	cleanCss     = require('gulp-clean-css');

//if node version is lower than v.0.1.2
require('es6-promise').polyfill();

gulp.task('scss', function(){
    gulp.src(['public/scss/**/**/*.scss'])
		.pipe(plumber())
		.pipe( sass() )
        .pipe(autoPrefixer())
        .pipe(cssComb())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(cleanCss())
        .pipe(gulp.dest('public/css'));
});

gulp.task('serve',function(){
	gulp.watch(['public/scss/**/**/*.scss'],['scss']);
	gulp.watch(['js/**/*.js', '!js/vendor/**/*', '!js/min/**/*'], ['minify-js']);
});

gulp.task('minify-js', function(){
	gulp.src(['public/js/**/*.js', '!public/js/min/**/', '!public/js/**/*.min.js'])
	.pipe(plumber())
	 .pipe(babel({
	 	presets: ['es2015']
	 }))
	.pipe(uglify())
	.pipe(rename({
		suffix: '.min'
	}))
	.pipe(gulp.dest('public/js/min/'));

	gulp.src(['!public/js/min/**/', 'public/js/**/*.min.js'])
	.pipe(plumber())
	.pipe(gulp.dest('public/js/min/'));
});

gulp.task('build', ['scss', 'minify-js']);

gulp.task('package', ['build', 'move']);

gulp.task('default',['build', 'serve']);
