<?php
session_start();
if (isset($_SESSION['username'])) {
	$_SESSION = array();
}
session_destroy();
if (isset($_COOKIE['username'])) {
	setrawcookie('username', '', time() - 3600);
}
?>