<?php

$path = dirname(__FILE__).'/'.trim($_GET['d'], '/');

if (is_file($path)) {
	require $path;
} else if (is_file($path.'/index.html')) {
	require $path.'/index.html';
} else if (is_file($path.'/index.php')) {
	require $path.'/index.php';
} else {
	require dirname(__FILE__).'/index.php';
}
