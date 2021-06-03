<?php

//Return the path to the cache folder
function kcw_movies_GetCacheFolder() {
    $cachedir = __DIR__ . DIRECTORY_SEPARATOR . "cache";
    return $cachedir;
}
//Return the path to a cache file
function kcw_movies_GetCacheFile($type) {
    $cache = kcw_movies_GetCacheFolder() . DIRECTORY_SEPARATOR . $type . ".json";
    return $cache;
}
//Return cache data given the filename
function kcw_movies_GetCacheData($cachefilename) {
    return file_get_contents($cachefilename);
}
//Return cache data as json given the filename
function kcw_movies_GetCacheDataJSON($cachefilename) {
    return  json_decode(kcw_movies_GetCacheData($cachefilename), true);
}
//Cache the given data to the specified cache type
function kcw_movies_Cache($file, $data) {
    //Ensure the cache directory exists
    $cachedir = kcw_movies_GetCacheFolder();
    if (!file_exists($cachedir) || !is_dir($cachedir)) {
        mkdir($cachedir);
    }

    //Write the cache file
    $data = json_encode($data);
    file_put_contents($file, $data);
    return $data;
}
//Delete a cache file
function kcw_movies_DeleteCache($type) {
    $file = kcw_movies_GetCacheFile($type);
    if (file_exists($file)) unlink($file);
}
//Validate that the movies caches are not out of date
function kcw_movies_ValidateCache() {
    $status = array();
    //Compute time for tommorrow (now + (24hrs * 60mins * 60secs))
    $now = time();
    $tommorrow = $now + (24 * 60 * 60);
    $nextweek = $now + (7 * 24 * 60 * 60);
    $file = kcw_movies_GetCacheFile("status");

    if (!file_exists($file)) {
        //Vimeo cache is never invalidated or checked, 0 is the sentinel value describing that
        $status["vimeo"] = 0;
        $status["uploads"] = $nextweek;
        $status["youtube"] = $tommorrow;
        kcw_movies_Cache($file, $status);
    } else {
        $status = json_decode(kcw_movies_GetCacheData($file), true);
    }

    //Delete out of date caches, and update the cache time
    $caches = array("youtube"=> $tommorrow, "uploads"=> $nextweek);
    $changed = false;
    foreach ($caches as $str => $newtime) {
        if ($status[$str] < $now) {
            kcw_movies_DeleteCache($str);
            $status[$str] = $newtime;
            $changed = true;
        }
    }

    //Only update the cache if any of the cache was invalidated
    if ($changed) kcw_movies_Cache($file, $status);
}

?>