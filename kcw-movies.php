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
//Return the formatting required for the movies page **OLD FORMATTING** FOR REFRENCE ONLY
function kcw_movies_GetHTML() {
    $html ="<div class='kcw-movies-wrapper'> 
                <div class='kcw-movies-video' style='display: none;opacity: 0;'>
                    <iframe title='' src='' allow='autoplay; picture-in-picture' allowfullscreen='true' frameborder='0'>
                    </iframe>
                    <div class='kcw-movies-video-info'> 
                        <h3 class='kcw-movies-video-title'></h3>
                        <p class='kcw-movies-video-views'></p>
                        <p class='kcw-movies-video-separator'>-</p>
                        <p class='kcw-movies-video-created'></p>
                        <a class='kcw-movies-copy-embed'>
                            <span class='dashicons dashicons-shortcode'></span><h4>Embed</h4>
                            <input type='text' value='' class='kcw-movies-link'>
                        </a>
                        <div class='kcw-movies-copy-embed-message' style='position:absolute; left: -9999px;'>Copied!</div>
                    </div>
                </div>
                <div class='kcw-movies-search'>
                    <input type='text' aria-label='search' name='kcw-movies-search' placeholder='Search'>
                    <span class='dashicons dashicons-search'></span>
                </div>
                <div class='kcw-movies-list-wrapper'>
                    <div class='kcw-movies-pagination-wrapper'>
                        <ul class='kcw-movies-pagination pagination-top'></ul>
                    </div>
                    <center>
                        <h3 class='kcw-movies-list-message'></h3>
                        <ul class='kcw-movies-list'></ul>
                    </center>
                    <div class='kcw-movies-pagination-wrapper'>
                        <ul class='kcw-movies-pagination pagination-bottom'></ul>
                    </div>
                </div>
                <div class='kcw-movies-playbutton'><div class='kcw-movies-triangle'></div>
            </div>";
    return $html;
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
    
    //End the movies container
    $html .= kcw_movies_EndBlock();
    //Output the movies block on the page
    echo $html;
}
add_shortcode("kcw-movies", 'kcw_movies_Init');

?>