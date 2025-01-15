<?php
require_once "Classes/router.php";
require_once "Classes/Plugin.php";
require_once "Classes/User.php";

const SM = "statusMessage";

#region Plugins
Router::GET("/plugins/$", "./info.php");
Router::GET("/plugins/$/download", function ($args) {
    function _download($uid): void
    {
        $file = isset($_GET['version']) ?
            Plugin::GetFromVersion($uid, $_GET['version']) ?? Plugin::GetLatest($uid) ?? [] :
            Plugin::GetLatest($uid);

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

    _download($selectedFile);
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
Router::GET("/plugins/$/$/delete", function ($args) {
    if (!User::IsLoggedIn() || !User::IsAdmin() || User::AdminRank() < 5) {
        Router::toJSON(403);
        exit;
    }
    http_response_code(200);

    $uid = $args[0];
    $version = $args[1];
    $plugin = Plugin::Create();

    $plugin->Delete($uid, $version);
});

#endregion

Router::GET("/attach", "attach.html");
Router::GET("/admin", "admin.php");

#region Login
Router::GET("/login", "login.php");
Router::POST("/login", "login.php");
#endregion

#region Collections
Router::GET("/collections/$/delete", function ($args) {
    if (!User::IsLoggedIn() || !User::IsAdmin() || User::AdminRank() < 5) {
        Router::toJSON(400, [SM => "Insufficient Permissions"]);
        exit;
    }
    http_response_code(200);

    $uid = $args[0];
    $plugin = Collection::Create();

    $plugin->Delete($uid);
});

Router::POST("/collections/add", function ($args) {
    if ($_SERVER["CONTENT_TYPE"] !== 'application/json') {
        Router::toJSON(400, [SM => "Invalid Content Type"]);
        exit;
    }

    if (!User::IsLoggedIn() || !USer::IsAdmin() || User::AdminRank() < 5) {
        Router::toJSON(403, [SM => "Insufficient Permissions"]);
        exit;
    }

    $data = Router::getJSON();

    if (empty($data)) {
        Router::toJSON(400, [SM => "Data cannot be empty"]);
        exit;
    }

    if (empty($data['Name']) || empty($data['Author'])) {
        Router::toJSON(400, [SM => "Name and Author fields are required"]);
        exit;
    }

    // Validate fields
    if (!is_string($data['Name']) || strlen($data['Name']) < 2) {
        Router::toJSON(400, [SM => "Name must be a string with a minimum length of 2 characters"]);
        exit;
    }

    if (!is_string($data['Author']) || strlen($data['Author']) < 2) {
        Router::toJSON(400, [SM => "Author must be a string with a minimum length of 2 characters"]);
        exit;
    }

    $collection = Collection::Create();
    $success = $collection->CreateCollection($data['Name'], $data['Author'], $data['Description']);
    $success ?
        Router::toJSON(200, [SM => "Collection $data[Name] was successfully added"]) :
        Router::toJSON(400, [SM => "Collection $data[Name] could not be added"]);

});
#endregion

Router::POST("/resolve", function () {
});

Router::GET("/404", "home.php");