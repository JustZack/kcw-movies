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

function kcw_movies_register_dependencies() {
    wp_register_style("kcw-movies", plugins_url("kcw-movies.css", __FILE__), null, "1.4.2");
    wp_register_script("kcw-movies", plugins_url("kcw-movies.js", __FILE__), array('jquery'), "1.4.2");
}
add_action("wp_enqueue_scripts", "kcw_movies_register_dependencies");

function kcw_movies_enqueue_dependencies() {
    wp_enqueue_style("kcw-movies");
    wp_enqueue_script("kcw-movies");
}

function kcw_movies_BuildListItem() {

}

//Build up the video display
function kcw_movies_BuildVideoDisplay() {
    
}
//Build up the list display
function kcw_movies_BuildListDisplay() {

}

//The head of the movies wrapper
function kcw_movies_StartBlock() {
    return "<div class='kcw-movies-container'>";
}
//The tail of the movies wrapper
function kcw_movies_EndBlock() {
    return "</div>";
}
//Return the formatting required for the movies page
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
function kcw_movies_GetJSData() {
    return "<script>var kcw_movies = " . kcw_movies_GetData() . "</script>";
}
//Init KCW movies
function kcw_movies_Init() {
    //Validate the movies cache
    kcw_movies_ValidateCache();

    //Enqueue the script and stylesheets
    kcw_movies_enqueue_dependencies();

    //Start the vimeo container
    $html = kcw_movies_StartBlock();
    //Generate the nessesary javascript data 
    $html .= kcw_movies_GetJSData();
    //Add the nessesary html container elements
    $html .= kcw_movies_GetHTML();
    //End the vimeo container
    $html .= kcw_movies_EndBlock();
    //Output the vimeo block on the page
    echo $html;
}
add_shortcode("kcw-movies", 'kcw_movies_Init');

?>