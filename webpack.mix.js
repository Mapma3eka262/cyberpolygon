const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// Основной CSS
mix.postCss('resources/css/app.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss'),
    require('autoprefixer'),
]);

// Auth CSS
mix.postCss('resources/css/auth.css', 'public/css');

// Dashboard CSS
mix.postCss('resources/css/dashboard.css', 'public/css');

// Arena CSS
mix.postCss('resources/css/arena.css', 'public/css');

// Admin CSS
mix.postCss('resources/css/admin.css', 'public/css');

// JavaScript
mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/auth.js', 'public/js')
   .js('resources/js/dashboard.js', 'public/js')
   .js('resources/js/arena.js', 'public/js')
   .js('resources/js/admin.js', 'public/js')
   .version();

// Копирование сторонних библиотек
mix.copy('node_modules/htmx.org/dist/htmx.min.js', 'public/js/htmx.min.js');

if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}