<?php
//Return the http options array for performing requests
function kcw_movies_get_http_options($header, $method, $content) {
    $options = array(
        'http' => array(
            'header'  => $header,
            'method'  => $method,
            'content' => $content
        )
    );
    return $options;
}
//Return the header string
function kcw_movies_get_header($token, $accept) {
    $header = "Authorization: Bearer " . $token . "\r\n";
    $header .= "Content-Type: application/json\r\n";
    $header .= "Accept: " . $accept . "\r\n";
    return $header;
}
//Perform a get request with the given url, header, and content
function kcw_movies_do_get($url, $header, $content) {
    $options = kcw_movies_get_http_options($header, 'GET', $content);
    return kcw_movies_do_request($url, $options);
}
//Perform a post request with the given url, header, and content
function kcw_movies_do_post($url, $header, $content) {
    $options = kcw_movies_get_http_options($header, 'POST', $content);
    return kcw_movies_do_request($url, $options);
}
//Perform an http request
function kcw_movies_do_request($url, $options) {
    //Keep 404's from throwing warnings to the client
    set_error_handler("kcw_movies_warning_handler", E_WARNING);
    $res = "";
    try {
        $res = json_decode(file_get_contents($url, false, stream_context_create($options)), true) or kcw_movies_toss(new Exception());
    } catch (Exception $e) {
        restore_error_handler();
        die;
    }
    restore_error_handler();
    return $res;

}
//Throw out exceptions
function kcw_movies_toss(Exception $ex) {
    throw $ex;
}
//Throw out warnings
function kcw_movies_warning_handler($errnom, $errstr) {
    return "";
}
//Convert an integer number of seconds to a time string
function kcw_movies_seconds_to_time_string($duration) {
    $minutes = (int)($duration / 60);
    $seconds = (int)($duration % 60);
    $secZero = ($seconds < 10) ? '0' : '';
    $timeStr = $minutes . ":" . $secZero . $seconds;

    return $timeStr;
}
//Convert a date time string to the generic datetime format we use
function kcw_movies_date_time_to_time_string($datetime) {
    $d = new DateTime($datetime);
    return $d->format("M j, Y");
}

?>