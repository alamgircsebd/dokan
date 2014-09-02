// including plugins
var gulp = require('gulp')
, less = require("gulp-less");

// task
gulp.task('compile-less', function () {
	gulp.src('assets/less/style.less') // path to your file
	.pipe(less())
	.pipe(gulp.dest('assets/css'));
});

gulp.task('watch', function() {
    gulp.watch('assets/less/*.less', ['compile-less']);
});

gulp.task('default', ['compile-less', 'watch']);