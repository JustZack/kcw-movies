<?php

include_once "cache-helpers.php";
include_once "vimeo-helpers.php";
include_once "youtube-helpers.php";

function kcw_movies_SortingSwap($array, $i, $j) {
    $tmp = $array[$i];
    $array[$i] = $array[$j];
    $array[$j] = $tmp;
    return $array;
}

function kcw_movies_SortingMinj($i, $array, $key, $minkey) {
    $minj = $i;
    for ($j = $i + 1;$j < count($array);$j++) {
        $curkey = strtotime($array[$j][$key]);
        if ($curkey > $minkey) {
            $minj = $j;
            $minkey = $curkey;
        }
    }
    return $minj;
}

function kcw_movies_OrderArrayByKeyAsc($array, $key) {
    //Selection sort
    for ($i = 0;$i < count($array) - 1;$i++) {
        $minkey = strtotime($array[$i][$key]);
        $minj = kcw_movies_SortingMinj($i, $array, $key, $minkey);
        //Swap
        if ($minj != $i) $array = kcw_movies_SortingSwap($array, $i, $minj);
    }
    return $array;
}

//Construct the vimeo cache data
function kcw_movies_BuildVimeoCacheData($videos, $type = "vimeo") {
    $cachedata = array();
    $cachedata["link_prepend"] = "https://vimeo.com/";
    $cachedata["embed_prepend"] = "https://player.vimeo.com/video/";
    $cachedata["data"] = array();

    for ($i = 0;$i < count($videos);$i++) {
        $element = array();
        
        $element["id"] = $videos[$i]["id"];
        $element["name"] = $videos[$i]["name"];
        $element["length"] = $videos[$i]["length"];
        $element["src"] = $type;
        $element["thumb"] = $videos[$i]["thumb"]["link"];
        $element["views"] = $videos[$i]["stats"]["plays"];
        $element["created"] = $videos[$i]["created"];

        $cachedata["data"][] = $element;
    }
    return $cachedata;
}

//Get the nessesary data for vimeo videos
function kcw_movies_GetVimeoData() {
    //Cache / Fetch the Vimeo JSON data
    $json = "";
    $file = kcw_movies_GetCacheFile("vimeo");
    if (!file_exists($file)) { 
        //Personal Token
        $token = 'e53ce9b373f3c78eb5a6712445d2b0fe';
        //Relevent information that isnt worth using the api to query
        $user = '130014326'; $folder = '3531368';

        $videos = kcw_movies_get_all_vimeo_videos($token, $user, $folder);
        $cachedata = kcw_movies_BuildVimeoCacheData($videos);        
        
        $json = $cachedata;
        kcw_movies_Cache($file, $cachedata);
    } else {
        $json = kcw_movies_GetCacheDataJSON($file);
    }

    //Return the data
    return $json;
}
//Get the nessesary data for vimeo uploads
function kcw_movies_GetVimeoUploadsData() {
        //Cache / Fetch the Vimeo JSON data
        $json = "";
        $file = kcw_movies_GetCacheFile("uploads");
        if (!file_exists($file)) { 
            //Personal Token
            $token = 'e53ce9b373f3c78eb5a6712445d2b0fe';
            //Relevent information that isnt worth using the api to query
            $user = '130014326'; $folder = '3598104';
    
            $videos = kcw_movies_get_all_vimeo_videos($token, $user, $folder);
            $cachedata = kcw_movies_BuildVimeoCacheData($videos, "uploads");
            
            $json = $cachedata;
            kcw_movies_Cache($file, $cachedata);
        } else {
            $json = kcw_movies_GetCacheDataJSON($file);
        }
    
        //Return the data
        return $json;
}

//Construct the youtube cache
function kcw_movies_BuildYoutubeCacheData($videos) {
    $cachedata = array();
    $cachedata["link_prepend"] = "https://youtube.com/watch?v=";
    $cachedata["embed_prepend"] = "https://youtube.com/embed/";
    $cachedata["data"] = array();

    for ($i = 0;$i < count($videos);$i++) {
        $element = array();
        
        $element["id"] = $videos[$i]["id"];
        $element["name"] = $videos[$i]["name"];
        $element["length"] = $videos[$i]["length"];
        $element["src"] = "youtube";
        $element["thumb"] = $videos[$i]["thumb"]["link"];
        $element["views"] = $videos[$i]["stats"]["viewCount"];
        $element["created"] = $videos[$i]["published"];
        
        $cachedata["data"][] = $element;
    }
    return $cachedata;
}

//Get the nessesary data for youtube videos
function kcw_movies_GetYoutubeData() {
    $json = "";
    $file = kcw_movies_GetCacheFile("youtube");
    if (!file_exists($file)) {
        //Personal token
        $token = "AIzaSyC5WjpUu_CdUls3RD_OBMv0H4ts-YIFgv8";
        //The KCW youtube channel
        //$channel = "UCApIjgEvgPjXmuhp_ngx88A";
        //$videos = kcw_movies_get_all_youtube_videos($token, $channel);

        $kcw_channel = "UCApIjgEvgPjXmuhp_ngx88A";
        $videos_kcw = kcw_movies_get_all_youtube_videos($token, $kcw_channel);
        
        $franz_channel = "UCeFkmJX8p0h-ZSKflv_rI7A";
        $videos_franz = kcw_movies_get_all_youtube_videos($token, $franz_channel);
        
        $audrey_channel = "UCO6ru5l9ZSGI_kM5HLvvt0w";
        $videos_audrey = kcw_movies_get_all_youtube_videos($token, $audrey_channel);

        $videos = array_merge( $videos_audrey, $videos_franz, $videos_kcw);
        
        $cachedata = kcw_movies_BuildYoutubeCacheData($videos);
        
        $json = $cachedata;
        kcw_movies_Cache($file, $cachedata);
    } else {
        $json = kcw_movies_GetCacheDataJSON($file);
    }
    //Return the data
    return $json;
}

//Return all movie data as JSON **OLD FORMATTING**
function kcw_movies_GetData() {
    $vimeo = json_encode(kcw_movies_GetVimeoData());
    $uploads = json_encode( kcw_movies_GetVimeoUploadsData());
    
    $ytd = kcw_movies_GetYoutubeData();
    $ytd["data"] = kcw_movies_OrderArrayByKeyAsc($ytd["data"], "created");
    $youtube = json_encode($ytd);

    $json = "{ 'vimeo': %s, 'uploads': %s, 'youtube': %s }";
    return sprintf($json, $vimeo, $uploads, $youtube);
}

//Get the full video cache
function kcw_movies_GetVideoCacheData() {
    $movies = array();
    $file = kcw_movies_GetCacheFile("movies");
    if (!file_exists($file)) {
        $vimeo = kcw_movies_GetVimeoData();
        $uploads = kcw_movies_GetVimeoUploadsData();
        $youtube = kcw_movies_GetYoutubeData();
    
        $links = array();
        $links["vimeo"] = array("link" => $vimeo["link_prepend"], "embed" => $vimeo["embed_prepend"]);
        $links["youtube"] = array("link" => $youtube["link_prepend"], "embed" => $youtube["embed_prepend"]);
    
        $videos = array_merge($uploads["data"], $youtube["data"]);
        $videos = kcw_movies_OrderArrayByKeyAsc($videos, "created");
        $videos = array_merge($videos, $vimeo["data"]);

        $movies["links"] = $links;
        $movies["data"] = $videos;

        kcw_movies_Cache($file, $movies);
    } else {
        $movies = kcw_movies_GetCacheDataJSON($file);
    }
    //Return the data
    return $movies;
}

?>