<?php


function kcw_movies_OrderArrayByKeyAsc($array, $key) {
    //Selection sort
    for ($i = 0;$i < count($array) - 1;$i++) {
        $minkey = strtotime($array[$i][$key]);
        $minj = $i;
        for ($j = $i + 1;$j < count($array);$j++) {
            $curkey = strtotime($array[$j][$key]);
            if ($curkey > $minkey) {
                $minj = $j;
                $minkey = $curkey;
            }

        }
        //Swap
        if ($minj != $i) {
            $tmp = $array[$i];
            $array[$i] = $array[$minj];
            $array[$minj] = $tmp;
        }
    }
    return $array;
}

//Build the html for a thumbnail element
function kcw_movies_BuildThumbnailElement($type, $video) {
    $id = $video["id"];
    $name = $video["name"];
    $thumb_src = $video["thumb"]["link"];
    $thumb_width = $video["thumb"]["width"];
    $thumb_height = $video["thumb"]["height"];
    $duration = $video["length"];

    $alt = str_replace('\'', '', $name);
    $alt = str_replace('\"', '', $alt);
    $html = "<li><a class='kcw-movies-thumb-wrapper' data-src='$type' data-id='$id' title='$alt'><img class='kcw-movies-thumb' src='$thumb_src' alt='$alt' width='$thumb_width' height='$thumb_height'><p class='kcw-movies-title'>$name</p><div class='kcw-movies-length'><pre class='kcw-movies-length'>$duration</pre></div></a></li>";
    return $html;
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
        $element["views"] = $videos[$i]["stats"]["plays"];
        $element["created"] = $videos[$i]["created"];
        $element["html"] = kcw_movies_BuildThumbnailElement($type, $videos[$i]);

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
        $element["views"] = $videos[$i]["stats"]["viewCount"];
        $element["created"] = $videos[$i]["published"];
        $element["html"] = kcw_movies_BuildThumbnailElement("youtube", $videos[$i]);

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
        
        $videos = array_merge($videos_franz, $videos_kcw);
        
        $cachedata = kcw_movies_BuildYoutubeCacheData($videos);
        
        $json = $cachedata;
        kcw_movies_Cache($file, $cachedata);
    } else {
        $json = kcw_movies_GetCacheDataJSON($file);
    }
    //Return the data
    return $json;
}

//Return all movie data as array
function kcw_movies_GetListData() {
    $vimeo = kcw_movies_GetVimeoData();
    $uploads = kcw_movies_GetVimeoUploadsData();
    $youtube = kcw_movies_GetYoutubeData();

    $links = array();
    //$links["vimeo"] = [$vimeo["link_prepend"], $vimeo["embed_prepend"]];
    //$links["uploads"] = [$uploads["link_prepend"], $uploads["embed_prepend"]];
    //$links["youtube"] = [$youtube["link_prepend"], $youtube["embed_prepend"]];

    $videos = array_merge($uploads["data"], $youtube["data"]);
    $videos = kcw_movies_OrderArrayByKeyAsc($videos, "created");

    $videos = array_merge($videos, $vimeo["data"]);
    $movies["links"] = $links;
    $movies["data"] = $videos;

    return $movies;
}

//Return all movie data as JSON
function kcw_movies_GetData() {
    $json = "{ 'vimeo': %s, 'uploads': %s, 'youtube': %s }";
    $vimeo = json_encode(kcw_movies_GetVimeoData());
    $uploads = json_encode( kcw_movies_GetVimeoUploadsData());
    
    $ytd = kcw_movies_GetYoutubeData();
    $ytd["data"] = kcw_movies_OrderArrayByKeyAsc($ytd["data"], "created");
    $youtube = json_encode($ytd);
    //$youtube["data"] = kcw_movies_OrderArrayByKeyAsc($youtube["data"], "created");        

    return sprintf($json, $vimeo, $uploads, $youtube);
}

?>