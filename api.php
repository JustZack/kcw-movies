<?php

function kcw_movies_api_search($data) {
    return "YEP";
}

function kcw_movies_api_page($data) {
    return "YEP";
}

add_action('rest_api_init', "kcw_movies_init_api");
function kcw_movies_init_api() {
    register_rest_route('kcwmovies/v1', 
                        '/search/(?P<msearch>[\s+])', 
                        array(
                            'methods'=> 'GET',
                            'callback'=> 'kcw_movies_api_search'
                        )
    );
    register_rest_route('kcwmovies/v1', 
                        '/page/(?P<lpage>\d+)', 
                        array(
                            'methods'=> 'GET',
                            'callback'=> 'kcw_movies_api_page'
                        )
    );
}
add_action( 'rest_api_init', "kcw_movies_init_api");
?>