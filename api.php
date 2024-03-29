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
    $total = count($fulldata);
    if ($page < 1) $page = 1;

    $data = array();
    $data["total"] = $total; $data["page"] = $page; $data["per_page"] = $per_page; 
    $data[$data_key] = array();

    $start = ($page - 1) * $per_page; $end = 0;
    if ($start >= $total) {
        $start = (ceil($total/$per_page)-1) * $per_page;
        $end = $total - 1;
    } else {
        $end = $start + $per_page - 1;
        if ($end > $total) $end = $total - 1;
    }

    if ($total > 0)
        for ($i = $start;$i <= $end;$i++) 
            $data[$data_key][] = $fulldata[$i];

    $data["start"] = $start; $data["end"] = $end;
    return $data;
}
//Return the first page of the video list
function kcw_movies_api_GetList() {
    $data = array();
    $data["vpage"] = 1;
    return kcw_movies_api_GetListPage($data);
}
//Return the given page of the video list
function kcw_movies_api_GetListPage($data) {
    $list = kcw_movies_GetVideoCacheData();
    $vpage = (int)$data["vpage"];
    $list_page = kcw_movies_api_Page($list["data"], $vpage, 40, "items");
    $list_page["links"] = $list["links"];
    return kcw_movies_api_Success($list_page);
}
//Filter bad meaningless characters out of a search string
function kcw_movies_api_FilterString($search) {
    $search = preg_replace("/(%20|\+|\s)/", " ", $search);
    $search = preg_replace("/[^A-Za-z0-9\s]/", "", $search);
    $search = strtolower($search);
    return $search;
}
//Check if two strings contain eachother
function kcw_movies_api_StringsMatch($a, $b) {
    //Both zero length = match
    if (!strlen($a) && !strlen($b)) return true;
    //Exactly one zero length = no match
    else if (!strlen($a) xor !strlen($b)) return false;
    //Either one contains the other = match
    return strpos($a, $b) > -1 || strpos($b, $a) > -1;
}
//Compute how similar the search array is to the possible match array
function kcw_movies_api_ComputeLikeness($search, $possible_match) {
    //Convert string to array of strings
    $search = explode(" ", $search); $possible_match = explode(" ", $possible_match);
    //Keep track of matches
    $total_matches = 0;
    //Iterate over all parts and track likeness
    foreach ($search as $spart)
        foreach ($possible_match as $pmpart)
            if (kcw_movies_api_StringsMatch($spart, $pmpart)) 
                { $total_matches++; break; }
    //Return # of matches / # of search words
    return $total_matches / (1.0*count($search));
}
//Check if either the search or possible match are similar
function kcw_movies_api_SearchMatches($search, $possible_match) {
    //Search contains video title OR Video title contains search 
    if (kcw_movies_api_StringsMatch($search, $possible_match)) return true;
    //compare likeness of the two arrays and ensure it is >= 33.3%
    else return kcw_movies_api_ComputeLikeness($search, $possible_match) >= .333;
}
//Return any galleries matching the given search string
function kcw_movies_api_Search($string) {
    $list = kcw_movies_GetVideoCacheData();
    if (isset($string) && strlen($string) > 0) {
        $filtered = kcw_movies_api_FilterString($string);
        $search_list = array();
        foreach ($list["data"] as $item)
            if (kcw_movies_api_SearchMatches($filtered, kcw_movies_api_FilterString($item["name"])))
                $search_list[] = $item;
        $list["data"] = $search_list;
    }
    return $list;
}
//Return any galleries matching the given search string
function kcw_movies_api_GetSearch($data) {
    $data["vpage"] = 1;
    return kcw_movies_api_GetSearchPage($data);
}
//Return any galleries matching the given search string
function kcw_movies_api_GetSearchPage($data) {
    $vpage = (int)$data["vpage"];
    $list = kcw_movies_api_Search($data["vsearch"]);
    $list_page = kcw_movies_api_Page($list["data"], $vpage, 40, "items");
    $list_page["links"] = $list["links"];
    $list_page["search"] = $data["vsearch"];
    return kcw_movies_api_Success($list_page);
}
//Register API routes
function kcw_movies_api_RegisterRestRoutes() {
    global $kcw_movies_api_namespace;
    
    //Route for /list
    register_rest_route( "$kcw_movies_api_namespace/v1", '/list', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetList',
    ));
    //Route for /list/page
    register_rest_route( "$kcw_movies_api_namespace/v1", '/list/(?P<vpage>\d+)', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetListPage',
    ));

    //Route for /search/NULL
    register_rest_route( "$kcw_movies_api_namespace/v1", '/search', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetList',
    ));

    //Route for /search/search-string
    register_rest_route( "$kcw_movies_api_namespace/v1", '/search/(?P<vsearch>[^/]+)', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetSearch',
    ));
    //Route for /search/search-string/page
    register_rest_route( "$kcw_movies_api_namespace/v1", '/search/(?P<vsearch>[^/]+)/(?P<vpage>\d+)', array(
        'methods' => 'GET',
        'callback' => 'kcw_movies_api_GetSearchPage',
    ));
}

add_action( 'rest_api_init', "kcw_movies_api_RegisterRestRoutes");

?>