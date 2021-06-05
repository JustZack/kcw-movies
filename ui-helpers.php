    <?php

//include_once "api.php";



//Build up the video display (using video data in $_GET)
function kcw_movies_ui_GetVideoHTML() {

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
        </div>";
}

//Build html for setting js data
function kcw_movies_ui_GetJSData($data) {
    return "<script>var kcw_movies = " . $data . "</script>";
}
//Build a list item
function kcw_movies_ui_BuildListItem($video) {
    //var_dump($video);
    $id = $video["id"];
    $type = $video["type"];
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
    $page = $_GET["vpage"]; $search = $_GET["vsearch"];
    if (!isset($page)) $page = 1;

    //Get the data we want via the API
    $data = array(); 
    $data["vpage"] = $page;
    $data["vsearch"] = $search;
    
    if (isset($search)) $vlist = kcw_movies_api_GetSearch($data);
    else                $vlist = kcw_movies_api_GetListPage($data);

    $list_html = kcw_movies_ui_GetJSData(json_encode($vlist));
    $list_html .= kcw_movies_ui_GetListStartHTML();
    foreach ($vlist["items"] as $video)
        $list_html .= kcw_movies_ui_BuildListItem($video);

    $list_html .= kcw_movies_ui_GetListEndHTML();
    return $list_html;

}

?>