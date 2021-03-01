<?php

require_once __DIR__ . '/Facebook/autoload.php';

function get_facebook_access_token($appid, $appsecret) {
    $fb = new \Facebook\Facebook([
        'app_id' => $appid,
        'app_secret' => $appsecret,
        'default_graph_version' => 'v2.10',
    ]);
    $helper = $fb->getJavaScriptHelper();
    try {
        $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    if (!isset($accessToken)) {
        echo 'No cookie set or no OAuth data could be obtained from cookie.';
        exit;
    }
}

?>