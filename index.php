<?php
//  ╱╲
//  ╳╳
// ╱╳╳╲
// ╲╳╳╱
//  ╳╳
//  ╲╱ Re-route every requests towards this file

require_once "Classes/router.php";
require_once "Classes/Plugin.php";

// Html
Router::get("/plugins/$", "./info.php");
// Binary
Router::get("/plugins/$/download", function ($args) {
    function Download($uid)
    {
        $file = isset($_GET['version']) ?
            Plugin::GetFromVersion($uid, $_GET['version']) ?? Plugin::GetLatest($uid) ?? [] :
            $file = Plugin::GetLatest($uid);

        if (isset($file['path']) && file_exists($file['path'])) {
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment;filename=\"$file[name].dll\"");
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Pragma: public");
            header("Content-Length: " . filesize($file['path']));

            readfile($file['path']);
        } else {
            http_response_code(404);
        }
    }

    $selectedFile = $args[0];

    Download($selectedFile);
});

$r = Router::getJSON();
// Json
Router::get("/plugins/$/list", function ($args){
    $plugin = new Plugin();
    Router::toJSON(200, $plugin->GetExtraInfo($args[0]));
});
// Json
Router::get("/plugins/search/$", function ($args) {
    $collection = new Collection();
    echo json_encode($collection->Search($args[0]));
});
// Html
Router::get("/upload", "upload.html");
// Json
Router::post("/upload", "uploadhandler.php");
// Html
Router::get("/404", "main-page.php");