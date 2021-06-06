<?php
/*
* Plugin Name:       KCW Movies
* Description:       Provide an aggregated home for all KCW video content
* Version:           1.4.0
* Requires at least: 5.2
* Requires PHP:      7.2
* Author:            Zack Jones
*/

defined( 'ABSPATH' ) or die('');

include_once "api.php";
include_once "ui-helpers.php";

function kcw_movies_register_dependencies() {
    wp_register_style("kcw-movies", plugins_url("kcw-movies.css", __FILE__), null, "1.4.2");
    wp_register_script("kcw-movies", plugins_url("kcw-movies.js", __FILE__), array('jquery'), "1.4.2");
}
add_action("wp_enqueue_scripts", "kcw_movies_register_dependencies");

function kcw_movies_enqueue_dependencies() {
    wp_enqueue_style("kcw-movies");
    wp_enqueue_script("kcw-movies");
}

//The head of the movies wrapper
function kcw_movies_StartBlock() {
    return "<div class='kcw-movies-container'>";
}
//The tail of the movies wrapper
function kcw_movies_EndBlock() {
    return "</div>";
}

//Init KCW movies
function kcw_movies_Init() {
    //Validate the movies cache (Delete old cache files)
    kcw_movies_ValidateCache();
    //Enqueue the script and stylesheets
    kcw_movies_enqueue_dependencies();
    //Start the movies container
    $html = kcw_movies_StartBlock();

    //Add the video display (if applicable)
    $html .= kcw_movies_ui_GetvideoHTML();
    //Add the search display
    $html .= kcw_movies_ui_GetSearchHTML();
    //Add the list display
    $html .= kcw_movies_ui_GetListDisplay();
    //Add the loading gif wrapper
    $html .= kcw_movies_ui_GetLoadingBox();
    //End the movies container
    $html .= kcw_movies_EndBlock();
    //Output the movies block on the page
    echo $html;
}
add_shortcode("kcw-movies", 'kcw_movies_Init');

?>