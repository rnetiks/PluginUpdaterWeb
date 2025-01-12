<?php
require_once "Classes/router.php";
require_once "Classes/Plugin.php";
require_once "Classes/User.php";

Router::GET("/plugins/$", "./info.php");
Router::GET("/plugins/$/download", function ($args) {
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
Router::GET("/plugins/$/list", function ($args) {
    $plugin = new Plugin();
    Router::toJSON(200, $plugin->GetExtraInfo($args[0]));
});
Router::GET("/plugins/search/$", function ($args) {
    $collection = new Collection();
    $data = $collection->Search($args[0]);
    Router::toJSON(200, $data);
});
Router::GET("/plugins/search/$/fh", function ($args) {
    $collection = new Collection();
    $data = $collection->SearchHash($args[0]);
    Router::toJSON(200, $data);
});
Router::GET("/upload", "upload.html");
Router::POST("/upload", "upload_handler.php");
Router::GET("/admin", "admin.php");
Router::GET("/login", "login.php");
Router::GET("/plugins/$/$/delete", function ($args) {
    http_response_code(204);
    if (!User::IsLoggedIn() || !User::IsAdmin() || User::AdminRank() < 5) {
        Router::toJSON(403);
        exit;
    }

    $uid = $args[0];
    $version = $args[1];
    $plugin = Plugin::Create();

    $plugin->Delete($uid, $version);
});
Router::GET("/404", "main_page.php");
