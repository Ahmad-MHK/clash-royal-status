<?php
/**
 * Functions file voor het simpele Clash Royale thema.
 * Hier registreren we styles en basis theme-support.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Styles en scripts laden.
 */
function cr_simple_theme_enqueue_assets() {
    // Laad style.css van het thema.
    wp_enqueue_style(
        'cr-simple-theme-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'cr_simple_theme_enqueue_assets' );

/**
 * Basis theme support.
 */
function cr_simple_theme_setup() {
    // Laat WordPress de <title> in de head genereren.
    add_theme_support( 'title-tag' );

    // Laat WordPress uitgelichte afbeeldingen gebruiken.
    add_theme_support( 'post-thumbnails' );

    // HTML5 markup support.
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
}
add_action( 'after_setup_theme', 'cr_simple_theme_setup' );
