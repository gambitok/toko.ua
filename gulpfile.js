var gulp             = require('gulp'),
    sass             = require('gulp-sass'),
    autoprefixer     = require('gulp-autoprefixer'),
    sourcemaps       = require('gulp-sourcemaps'),
    minifyCss        = require('gulp-clean-css'),
    rename           = require('gulp-rename'),
    browserSync      = require('browser-sync').create(),
    uglify           = require('gulp-uglify'),
    gulpStyleLint    = require('gulp-stylelint');

// minify js
gulp.task('uglify', function(){
    gulp.src('./js/*.js')
        .pipe(uglify())
        .pipe(rename('script.min.js'))
        .pipe(gulp.dest('js/src'))
        .pipe(browserSync.reload({stream: true})),
        gulp.watch('./js/*.js', ['uglify']);
});

// stylelint
gulp.task('stylelint', function(){
    return gulp.src('./sass/*.scss')
        .pipe(gulpStyleLint({
            reporters: [
                {
                    formatter: 'string',
                    console: true
                }
            ]
        }));
});

// to css with sourcemaps
gulp.task('sass', function(){
    return gulp.src('./sass/*scss')
        .pipe(sass())
        .pipe(sass({outputStyle:'compact'}).on('error', sass.logError))
        .pipe(sourcemaps.init())
        .pipe(sourcemaps.write(('./')))
        .pipe(gulp.dest('./css')),
        gulp.watch('./sass/*.scss', ['sass']);
});

// minify css (merge + autoprefix + rename)
gulp.task('minify-css', function(){
    return gulp.src('./css/style.css')
        .pipe(minifyCss())
        .pipe(autoprefixer({
            browsers: ['last 15 versions'],
            cascade: false
        }))
        .pipe(rename('style.min.css'))
        .pipe(gulp.dest('./css'))
        .pipe(browserSync.reload({stream: true})),
        gulp.watch('./css/style.css', ['minify-css']);
});

// browse-sync
gulp.task('browser-sync', function(){
    browserSync.init({
        server:{
            baseDir: './'
        }
    });
    gulp.watch('./*.html').on('change', browserSync.reload);
    gulp.watch('./js/*.js').on('change', browserSync.reload);
});

gulp.task('default', ['sass', 'minify-css', 'uglify', 'browser-sync']);