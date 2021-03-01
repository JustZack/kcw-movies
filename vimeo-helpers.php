<?php

//Methods to simplify api requests
include_once "api-helpers.php";

//Return a video page associated with the given user folder
function kcw_movies_get_vimeo_videos($token, $user, $folderid, $page = 1, $per_page = 24) {
    $url = "https://api.vimeo.com/users/".$user."/folders/".$folderid."/videos?page=".$page."&per_page=".$per_page;
    //$url .= "&fields=total,page,per_page,paging,data[]";
    $header = kcw_movies_get_header($token, kcw_movies_get_vimeo_accept());
    $res = kcw_movies_do_get($url, $header, null);
    return kcw_movies_parse_vimeo_array($res);
}
//Return all the videos in a folder
function kcw_movies_get_all_vimeo_videos($token, $user, $folderid) {
    $page = 1; $per_page = 100;
    $videos = kcw_movies_get_vimeo_videos($token, $user, $folderid, $page, $per_page);

    $page++;
    $total = $videos["paging"]["total"];
    $videos = $videos["videos"];

    for ($current = $per_page;$current < $total;$page++) {
        $vpage = kcw_movies_get_vimeo_videos($token, $user, $folderid, $page, $per_page);
        $videos = array_merge($videos, $vpage["videos"]);
        $current += $per_page;
    }
    return $videos;
}
//Parse the returned video array from vimeo into its usefull parts
function kcw_movies_parse_vimeo_array($videos) {
    $parsed_videos = array();
    //Grab the paging data from the api response
    $parsed_videos["paging"] = $videos["paging"];
    $parsed_videos["paging"]["page"] = $videos["page"];
    $parsed_videos["paging"]["per_page"] = $videos["per_page"];
    $parsed_videos["paging"]["total"] = $videos["total"];
    //Create separate array for the video data
    $parsed_videos["videos"] = array();

    //var_dump($videos["data"][0]);

    for ($i = 0;$i < count($videos["data"]);$i++) {
        $video = array();
        $uri = $videos["data"][$i]["uri"];
        $video['id'] = substr($uri, strpos($uri, '/', 1) + 1);
        $video['link'] = $videos["data"][$i]["link"];
        $video['name'] = $videos["data"][$i]["name"];
        $video['thumb'] = $videos["data"][$i]["pictures"]['sizes'][1];
        $video['stats'] = $videos["data"][$i]['stats'];
        $video['embed'] = $videos["data"][$i]['embed']['html'];
        $video['created'] = kcw_movies_date_time_to_time_string($videos["data"][$i]["created_time"]);
        $video['length'] = kcw_movies_seconds_to_time_string((int)$videos["data"][$i]['duration']);

        $parsed_videos["videos"][] = $video;
    }
    return $parsed_videos;
}
//Return the vimeo accept header
function kcw_movies_get_vimeo_accept() {
    return "application/vnd.vimeo.*+json;version=3.4";
}

?>