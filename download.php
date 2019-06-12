<?php
include_once "framework/framework.php";

if(isset($_GET["id"])){
    $db = App::getDBo();
	$db->setQuery("select * from files where id = ".$_GET["id"]);
	$result = $db->loadObject();
	$judul = $result->nama;
	$fileext = $result->jenis;
	$cfg = App::getConfig();
	$filepath = $cfg->path."/decrypted/".$judul.".".$fileext;
    
    // Process download
    if(file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        flush(); // Flush system output buffer
        readfile($filepath);
        exit;
    }
}
?>
Downloading File . . .
<script>
window.close();
</script>