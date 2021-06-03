<?php

include_once "data-helpers.php";

$kcw_movies_api_namespace = "kcwmovies";
$kcw_movies_api_url = home_url('wp-json/' . $kcw_movies_api_namespace . '/v1/');

//Api request ran into error
function kcw_movies_api_Error($msg) {
    $data = array();
    $data["message"] = $msg;
    $data["status"] = "Error";
    return $data;
}
//Api request succeeded!
function kcw_movies_api_Success($data) {
    $data["status"] = "Success";
    $data["time"] = time();
    return $data;
}

//Return a page of the given data
function kcw_movies_api_Page($fulldata, $page, $per_page, $data_key) {
    $data = array();

    $total = count($fulldata);
    $data["total"] = $total;
    $data["page"] = $page;
    $data["per_page"] = $per_page;

    $data[$data_key] = array();

    $data["start"] = 0;
    $data["end"] = 0;
    if ($page < 1) return $data;

    $start = ($page - 1) * $per_page; $end = 0;
    if ($start >= $total) {
        $start = $total;
        $end = $start;
    } else {
        $end = $start + $per_page;
        if ($end > $total)
            $end = $total;
        $end--;

        for ($i = $start;$i <= $end;$i++) $data[$data_key][] = $fulldata[$i];
    }

    $data["start"] = $start;
    $data["end"] = $end;

    return $data;
}

function kcw_movies_api_GetList() {
    $data = array();
    $data["lpage"] = 1;
    return kcw_movies_api_GetListPage($data);
}
function kcw_movies_api_GetListPage($data) {
    $list = kcw_movies_GetListData();
    $lpage = (int)$data["lpage"];
    $list_page = kcw_movies_api_Page($list["data"], $lpage, 40, "items");
    return kcw_movies_api_Success($list_page);
}

//Filter bad meaningless characters out of a search string
function kcw_movies_api_FilterString($search) {
    $search = preg_replace("/\%20/", '', $search);
    $search = preg_replace("/[^A-Za-z0-9]+/", '', $search);
    $search = strtolower($search);
    return $search;
}
//Check if two strings contain eachother
function kcw_movies_api_SearchMatches($search, $possible_match) {
    return strpos($search, $possible_match) > -1 || strpos($possible_match, $search) > -1;
}
//Return any galleries matching the given search string
function kcw_movies_Search($string) {
    $list = kcw_movies_GetListData();
    $filtered = kcw_movies_api_FilterString($string);
    $search_list = array();
    foreach ($list["data"] as $item) {
        $name = kcw_movies_api_FilterString($item["name"]);
        if (kcw_movies_api_SearchMatches($filtered, $name)) {
            $search_list[] = $item;
            continue;
        }
        //Break up the current gallery name based on its spaces
        //And check if the search string matches any of those
        /*$name = explode(' ', $name);
        $search_arr = explode(' ', $string);
        //foreach ($search_arr as $search_part) {
            foreach ($name as $part) {
                if (kcw_gallery_api_SearchMatches($string, $part)) {
                    $search_list[] = $item;
                    $fullbreak = true;
                    break 1;
                }
            }*/
        //}
    }
    return $search_list;
}
//Return any galleries matching the given search string
function kcw_movies_api_GetSearch($data) {
    $data["lpage"] = 1;
    return kcw_movies_api_GetSearchPage($data);
}
//Return any galleries matching the given search string
function kcw_movies_api_GetSearchPage($data) {
    $lpage = (int)$data["lpage"];
    $list = kcw_movies_Search($data["lsearch"]);
    $list_page = kcw_movies_api_Page($list, $lpage, 40, "items");
    $list_page["search"] = ($data["lsearch"]);
    return kcw_movies_api_Success($list_page);
}

//Register all the API routes
function kcw_movies_api_RegisterRestRoutes() {
    global $kcw_movies_api_namespace;
    
    //Route for /list
    register_rest_route( "$kcw_movies_api_namespace/v1", '/list', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetList',
    ));
    //Route for /list/page
    register_rest_route( "$kcw_movies_api_namespace/v1", '/list/(?P<lpage>\d+)', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetListPage',
    ));

    //Route for /search/search-string
    register_rest_route( "$kcw_movies_api_namespace/v1", '/search/(?P<lsearch>[^/]+)', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetSearch',
    ));
    //Route for /search/search-string/page
    register_rest_route( "$kcw_movies_api_namespace/v1", '/search/(?P<lsearch>[^/]+)/(?P<lpage>\d+)', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetSearchPage',
    ));
}

add_action( 'rest_api_init', "kcw_movies_api_RegisterRestRoutes");

?>