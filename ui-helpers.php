    <?php

//include_once "api.php";

//Return the html format of the video display
function kcw_moives_ui_VideoDisplayHTMl() {
    $video_html = "
    <div class='kcw-movies-video' style='display: %s;opacity: %s;'>
        <iframe title='%s' src='%s' allow='autoplay; picture-in-picture' allowfullscreen='true' frameborder='0'></iframe>
        <div class='kcw-movies-video-info'> 
            <h3 class='kcw-movies-video-title'>%s</h3>
            <p class='kcw-movies-video-views'>%s</p>
            <p class='kcw-movies-video-separator' style='display: inline;'>-</p>
            <p class='kcw-movies-video-created' style='display: inline;'>%s</p>
            <a class='kcw-movies-copy-embed'>
                <span class='dashicons dashicons-shortcode'></span><h4>Embed</h4>
                <input type='text' value='%s' class='kcw-movies-link' style='width: 0px; opacity: 0; margin-left: 0px; padding: 0px;'>
            </a>
            <div class='kcw-movies-copy-embed-message' style='position:absolute; left: -9999px;'>Copied!</div>
        </div>
    </div>";
    return $video_html;
}
//Build up the video display (using video data in $_GET)
function kcw_movies_ui_GetVideoHTML() {
    $list = kcw_movies_GetVideoCacheData();
    $id = $_GET["v"];
    $src = $_GET["vsrc"];

    $video_html = kcw_moives_ui_VideoDisplayHTMl();

    if (isset($id) && isset($src) && strlen($id) > 0 && strlen($src) > 0) {
        foreach ($list["data"] as $video) {
            if ($video["id"] == $id && $video["src"] == $src) {
                $v_src = $list["links"][$src]["embed"] . $id;
                $v_title = $video["name"];
                $v_views = $video["views"];
                $v_published = $video["created"];

                return sprintf($video_html, "block", 1, $v_title, $v_src, $v_title, $v_views, $v_published, $v_src);
            }
        }
    }

    return sprintf($video_html, "none", 0, "", "", "" , "", "", "", "");
}

//Search html (with search in $_GET)
function kcw_movies_ui_GetSearchHTML() {
    $search_html  = "<div class='kcw-movies-search'>
                        <input type='text' aria-label='search' name='kcw-movies-search' placeholder='Search' %s>
                        <span class='dashicons dashicons-search'></span>
                    </div>";
    $search = $_GET["vsearch"];
    if (isset($search)) return sprintf($search_html, "value='$search'");
    else                return sprintf($search_html, "");
}
//Start of the list html
function kcw_movies_ui_GetListStartHTML() {
    return "
        <div class='kcw-movies-list-wrapper'>
            <div class='kcw-movies-pagination-wrapper'>
                <ul class='kcw-movies-pagination pagination-top'></ul>
            </div>
                <center>
                    <h3 class='kcw-movies-list-message'></h3>
                    <ul class='kcw-movies-list'>";
}
//End of the list html
function kcw_movies_ui_GetListEndHTML() {
    return "        </ul>
                </center>
            <div class='kcw-movies-pagination-wrapper'>
                <ul class='kcw-movies-pagination pagination-bottom'></ul>
            </div>
        </div>
        <div class='kcw-movies-playbutton'><div class='kcw-movies-triangle'></div>";
}

//Build html for setting js data
function kcw_movies_ui_GetJSData($data) {
    return "<script>var kcw_movies = " . $data . "</script>";
}
//Build a list item
function kcw_movies_ui_BuildListItem($video) {
    //var_dump($video);
    $id = $video["id"];
    $type = $video["src"];
    $name = $video["name"];
    $thumb_src = $video["thumb"];
    $thumb_width = 320;
    $thumb_height = 180;
    $duration = $video["length"];

    $alt = str_replace('\'', '', $name);
    $alt = str_replace('\"', '', $alt);
    $html = "<li><a class='kcw-movies-thumb-wrapper' data-src='$type' data-id='$id' title='$alt'><img class='kcw-movies-thumb' src='$thumb_src' alt='$alt' width='$thumb_width' height='$thumb_height'><p class='kcw-movies-title'>$name</p><div class='kcw-movies-length'><pre class='kcw-movies-length'>$duration</pre></div></a></li>";
    return $html;
}
//Build up the list display (using search and video page in $_GET)
function kcw_movies_ui_GetListDisplay() {
    $page = (int)$_GET["vpage"]; $search = $_GET["vsearch"];
    if (!isset($page)) $page = 1;

    //Get the data we want via the API
    $data = array(); 
    $data["vpage"] = $page;
    $data["vsearch"] = $search;
    
    if (isset($search)) $vlist = kcw_movies_api_GetSearch($data);
    else                $vlist = kcw_movies_api_GetListPage($data);

    $js_data = array();
    $js_data["links"] = $vlist["links"];
    $js_data["total"] = $vlist["total"];
    $js_data["per_page"] = $vlist["per_page"];
    $js_data["pages"] = array();    
    for ($i = 0;$i < $page;$i++) $js_data["pages"][$i] = [];

    $js_data["pages"][(int)$page] = $vlist["items"];
    $list_html = kcw_movies_ui_GetJSData(json_encode($js_data));

    $list_html .= kcw_movies_ui_GetListStartHTML();
    foreach ($vlist["items"] as $video)
        $list_html .= kcw_movies_ui_BuildListItem($video);

    $list_html .= kcw_movies_ui_GetListEndHTML();
    return $list_html;

}

?>