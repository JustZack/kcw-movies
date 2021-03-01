<?php
    //Methods to simplify api requests
    include_once "api-helpers.php";

function kcw_movies_get_youtube_uploads($token, $channel) {
    $url = "https://www.googleapis.com/youtube/v3/channels?id=" . $channel . "&key=" . $token . "&part=contentDetails";
    $res = kcw_movies_do_get($url, null, null);
    return $res["items"][0]["contentDetails"]["relatedPlaylists"]["uploads"];
}

function kcw_movies_get_youtube_playlist_videos($token, $playlist, $perpage = 24) {
    $url = "https://www.googleapis.com/youtube/v3/playlistItems?playlistId=" . $playlist . "&key=" . $token . "&part=snippet&maxResults=" . $perpage;
    $res = kcw_movies_do_get($url, null, null);
    return kcw_movies_parse_youtube_playlist_array($res);
}

function kcw_movies_get_video_details($token, $videoids) {
    $ids = implode(",", $videoids);
    $url = "https://youtube.googleapis.com/youtube/v3/videos?key=" . $token . "&part=snippet,contentDetails,statistics&id=" . $ids;
    $res = kcw_movies_do_get($url, null, null);
    return kcw_movies_parse_youtube_video_array($res);
}

function kcw_movies_get_all_youtube_videos($token, $channel) {
    $playlist = kcw_movies_get_youtube_uploads($token, $channel);
    $videos = kcw_movies_get_youtube_playlist_videos($token, $playlist, 50);
    $videodetails = kcw_movies_get_video_details($token, $videos["videos"]);
    return $videodetails;
}

function kcw_movies_parse_youtube_playlist_array($videos) {
    $parsed_videos = array();

    $parsed_videos["paging"]["total"] = $videos["pageInfo"]["totalResults"];
    $parsed_videos["paging"]["per_page"] = $videos["pageInfo"]["resultsPerPage"];

    $parsed_videos["videos"] = array();
    
    //var_dump($videos["items"][0]);

    for ($i = 0;$i < count($videos["items"]);$i++) {
        $snippet = $videos["items"][$i]["snippet"];
        $parsed_videos["videos"][] = $snippet["resourceId"]["videoId"];
    }

    return $parsed_videos;
}

function kcw_movies_parse_youtube_video_array($videos) {
    $parsed_videos = array();

    //var_dump($videos["items"][0]);

    for ($i = 0;$i < count($videos["items"]);$i++) {
        $video = array();

        $snippet = $videos["items"][$i]["snippet"];

        $video["id"] = $videos["items"][$i]["id"];
        $video["name"] = $snippet["title"];
        $video["description"] = $snippet["description"];
        $video["thumb"]["link"] = $snippet["thumbnails"]["medium"]["url"];
        $video["thumb"]["width"] = $snippet["thumbnails"]["medium"]["width"];
        $video["thumb"]["height"] = $snippet["thumbnails"]["medium"]["height"];
        $video["published"] =  kcw_movies_date_time_to_time_string($snippet["publishedAt"]);
        $video["stats"] = $videos["items"][$i]["statistics"];
        $video["length"] = kcw_movies_convert_youtube_time_to_time_string($videos["items"][$i]["contentDetails"]["duration"]);

        $parsed_videos[] = $video;
    }

    return $parsed_videos;
}

function kcw_movies_convert_youtube_time_to_time_string($yttimestr) {
    $time = substr($yttimestr, 2);
    $minindex = strpos($time, "M");
    if (strlen($minindex) == 0) {
        $mins = "0";
    } else {
        $mins = substr($time, 0, $minindex);
        $time = substr($time, $minindex + 1);
    }

    if (strlen($time) == 0) {
        $secs = "00";
    } else {
        $secindex = strpos($time, "S");
        $secs = substr($time, 0, $secindex);
        if ((int)$secs < 10)
        $secs = "0" . $secs;
    }

    $timestr =  $mins . ":" . $secs;
    return $timestr;    
}

?>