<?php
error_reporting(E_ALL);
if($_SERVER['REQUEST_METHOD'] != "POST" || !strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME'])) die;

session_start();

include_once "framework/framework.php";

$cfg = App::getConfig();
$path = __DIR__."/".$cfg->path;

$method = Jinput::post("method", null, "text");
$id = Jinput::post("id", null, "int");

set_time_limit(1800);

switch($method){
	case "upload":
		$judul = Jinput::post("title", null, "text");
		$file = Jinput::files("file", null);
		$keterangan = Jinput::post("desc", null, "text");
		$result = Files::upload($path, $id, $judul, $file, $keterangan);
		echo json_encode($result);
		break;
	case "download":
		echo json_encode(Files::download($path, $id));
		break;
	case "collapsesidebar":
		echo json_encode(User::setCollapsedSidebar($id));
		break;
}

?>