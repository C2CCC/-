<?php
session_start();
if (isset($_SESSION['username'])) {
	$_COOKIE['username'] = $_SESSION['username'];
	echo TRUE;
} else if (isset($_COOKIE['username'])) {
	$_SESSION['username'] = $_COOKIE['username'];
	echo TRUE;
} else {
	echo FALSE;
}
?>