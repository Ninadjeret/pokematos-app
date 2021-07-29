const mix = require('laravel-mix');
const env = require('dotenv').config();
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

 var fs = require('fs')
 fs.readFile('resources/js/serviceworker.js', 'utf8', function (err,data) {
   if (err) {
     return console.log(err);
   }
   var result = data.replace('__APP_VERSION__', env.parsed.APP_VERSION);
   fs.writeFile('public/serviceworker.js', result, 'utf8', function (err) {
      if (err) return console.log(err);
      console.log('serviceworker.js copie');
   });
 });


mix.js('resources/js/app.js', 'public/js').vue()
   .sass('resources/sass/app.scss', 'public/css');
