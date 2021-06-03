<?php

function kcw_movies_api_search(WP_REST_Request $request ) {
    echo "HERE";
}
function kcw_movies_api_page(WP_REST_Request $request ) {
    echo "HERE";
}

add_action('rest_api_init', "kcw_movies_init_api");
function kcw_movies_init_api() {
    register_rest_route('kcwmovies/v1', 
                        '/search/?P<search\s+>', 
                        array(
                            'methods'=> 'GET',
                            'callback'=> 'kcw_movies_api_search'
                        )
    );
    register_rest_route('kcwmovies/v1', 
                        '/page/?P<page\d+>', 
                        array(
                            'methods'=> 'GET',
                            'callback'=> 'kcw_movies_api_page'
                        )
    );
}

?>