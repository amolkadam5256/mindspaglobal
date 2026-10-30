<?php
/** Bootstrap WordPress from its LocalWP document root when deployed at public_html. */
define( 'WP_USE_THEMES', true );
require __DIR__ . '/app/public/wp-blog-header.php';
