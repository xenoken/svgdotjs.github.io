<?php

/**
 * Instructions:
 *
 * 1. Put this into the document root of your Kirby site
 * 2. Make sure to setup the base url for your site correctly
 * 3. Run this script with `php statify.php` or open it in your browser
 * 4. Upload all files and folders from static to your server
 * 5. Test your site
 */

// Setup the base url for your site here
$url = 'http://svgjs.com';

// Don't touch below here
define('DS', DIRECTORY_SEPARATOR);

// load the cms bootstrapper
include(__DIR__ . DS . 'kirby' . DS . 'bootstrap.php');

$kirby = kirby();
$kirby->urls->index = $url;

$site = $kirby->site();

if($site->multilang()) {
  die('Multilanguage sites are not supported');
}

// root dir
$root = __DIR__ . DS . 'static' . DS;

dir::copy(__DIR__ . DS . 'assets',  $root . 'assets');
dir::copy(__DIR__ . DS . 'content', $root . 'content');

// set the timezone for all date functions
date_default_timezone_set($kirby->options['timezone']);

// load all extensions
$kirby->extensions();

// load all plugins
$kirby->plugins();

// load all models
$kirby->models();

// load all language variables
$kirby->localize();

foreach($site->index() as $page) {
  // render page
  $site->visit( $page->uri() );
  $html = $kirby->render( $page );

  // convert h2-6 tags
  $html = preg_replace_callback( "#<(h[1-6])>(.*?)</\\1>#", 'retitle', $html );

  // set root base
  $name = $page->isHomePage() ? 'index.html' : $page->uri() . DS . 'index.html';

  // write static file
  f::write($root . $name, $html);
}

// write CNAME file
file_put_contents( $root . 'CNAME', 'svgjs.com' );

// move 404 file
rename( $root . '404/index.html', $root . '404.html' );
rmdir( $root . '404' );

// helpers
function retitle( $match ) {
  list( $_unused, $hx, $title ) = $match;

  // clean id
  $id = strtolower( preg_replace( '/[^A-Za-z0-9-]+/', '-', strip_tags( $title ) ) );
  $id = preg_replace( '/^\-/', '', preg_replace( '/-$/', '', $id ) );

  return "<$hx id='$id'>$title</$hx>";
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Statification</title>
</head>
<body>

Your site has been exported to the <b>static</b> folder.<br />
Copy all sites and folders from there and upload them to your server.<br />
Make sure the main URL is correct: <b><?php echo $url ?></b>

</body>
</html>
