<?php 
//$domain = 'http://bhnlokpk.preview.infomaniak.website/';
$file = 'zbfu73gt2gfewz6r78/Arsenic-Web.woff';



// session_start();
$referer_ok = empty($domain) || (isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], $domain) !== false) || $_SERVER['HTTP_HOST'] === 'localhost:8888';
// $ticket_ok = isset($_SESSION["font-ticket"]) && $_SESSION["font-ticket"] && $_SESSION["font-ticket"] === $_GET['ticket'];
if (file_exists($file) && $referer_ok) {
	header('Content-Type: application/font-woff');
	readfile($file);
	$_SESSION["font-ticket"] = '';
	exit;
} else {
	header('HTTP/1.0 403 Forbidden');
	echo 'Acces forbidden!';
	exit;
}
?>