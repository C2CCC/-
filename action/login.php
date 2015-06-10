<?php
require_once 'connectVars.php';
session_start();
$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("onlinebookingsystem", $con);
$usr = $_POST['username'];
$pwd = $_POST['password'];
$sql = "SELECT * from user WHERE username='$usr' AND password='$pwd'";
$result = mysql_query($sql, $con);
$row = mysql_fetch_array($result);
if ($row) {
	$_SESSION['username'] = $usr;
	setrawcookie('username', $usr, time() + 3600);
	echo TRUE;
} else
	echo FALSE;
?>