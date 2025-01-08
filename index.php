<?php
//  ╱╲
//  ╳╳
// ╱╳╳╲
// ╲╳╳╱
//  ╳╳
//  ╲╱ Re-route every requests towards this file


require_once("./router.php");
require_once "./Plugin.php";

// Html
Router::get("/plugins/$", "plugin.php");
// Json
Router::get("/plugins/$/list", "listing.php");
Router::get("/plugins/search/$", function($args){
    $collection = new Collection();
    echo json_encode($collection->Search($args[0]));
});
// Html
Router::get("/upload","upload.html");
// Json
Router::post("/upload", "uploadhandler.php");
// Html
Router::get("/404", "main-page.php");