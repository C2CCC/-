<?php
//ini_set("display_errors", "Off");
require_once 'connectVars.php';
session_start();
$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db('onlinebookingsystem', $con);
mysql_query("set names utf8");
if ($_POST['targetUsr'] == 'self') {
	$usr = $_SESSION['username'];
} else {
	$usr = $_POST['targetUsr'];
}

$action = $_POST['action'];
switch ($action) {
	case 'chgPwd' :
		$pwdInfo = $_POST['newInfo'];
		echo chgPwd($usr, $pwdInfo, $con);
		break;
	case 'book' :
		$tno = $_POST['tno'];
		echo bookTickets($usr, $tno, $con);
		break;
	case 'refund' :
		$tno = $_POST['tno'];
		echo refundTickets($usr, $tno, $con);
		break;
	case 'transfer' :
		$tno = $_POST['tno'];
		$targetUsr = $_POST['targetUsr'];
		$usr = $_SESSION['username'];
		if (userExist($targetUsr, $con)) {
			if (refundTickets($usr, $tno, $con) && bookTickets($targetUsr, $tno, $con)) {
				echo TRUE;
			} else {
				echo FALSE;
			}
		} else {
			echo FALSE;
		}
		break;
	case 'getUsrList' :
		echo getUsrList($usr, $con);
		break;
	case 'addTicket' :
	case 'editTicket' :
		$newInfo = $_POST['newInfo'];
		echo perDogMngTickets($newInfo, $con);
		break;
	case 'delTicket' :
		$delTno = $_POST['newInfo'];
		echo perDogDelTickets($delTno, $con);
		break;
	case 'addMenber' :
	case 'editMenber' :
		$newInfo = $_POST['newInfo'];
		echo perDogMngMenbers($newInfo, $con);
		break;
	case 'delMenber' :
		$delUsername = $_POST['newInfo'];
		echo perDogDelMenbers($delUsername, $con);
		break;
	default :
		break;
}

function userExist($usr, $con) {
	$row = mysql_fetch_array(mysql_query("select username from user where username='$usr'", $con));
	if ($row) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function getUsrList($usr, $con) {
	$result = mysql_query("select username from user where department in (select department from user where username='$usr')", $con);
	$usrList = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$usrList[] = $row;
	}
	$usrList = arr_foreach($usrList);
	$usrList = json_encode($usrList);
	$usrList = urldecode($usrList);
	return $usrList;
}

function chgPwd($usr, $pwdInfo, $con) {
	$row = mysql_fetch_array(mysql_query("select username,password from user where username='$usr' and password='" . $pwdInfo['oldPwd'] . "'", $con));
	if ($row) {
		return mysql_query("update user set password='" . $pwdInfo['newPwd'] . "' where username='$usr'", $con);
	} else {
		return FALSE;
	}
}

function bookTickets($usr, $tno, $con) {
	$row = mysql_fetch_array(mysql_query("select * from tickets where tno='$tno' and rest>0 and overdue=0", $con));
	if ($row) {
		$row = mysql_fetch_array(mysql_query("select * from bookedtickets where username='$usr' and tno='$tno'", $con));
		if ($row) {
			$result = mysql_query("update bookedtickets set amount=amount+1 where username='$usr' and tno='$tno'", $con);
		} else {
			$result = mysql_query("insert into bookedtickets (username,tno,amount) values('$usr','$tno',1)", $con);
		}
		if ($result) {
			mysql_query("update tickets set rest=rest-1 where tno='$tno'", $con);
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function refundTickets($usr, $tno, $con) {
	$row = mysql_fetch_array(mysql_query("select * from bookedtickets where username='$usr' and tno='$tno'", $con));
	if ($row) {
		$row = mysql_fetch_array(mysql_query("select amount from bookedtickets where username='$usr' and tno='$tno'", $con));
		if ($row['amount'] == 1) {
			mysql_query("delete from bookedtickets where username='$usr' and tno='$tno'", $con);
		} else {
			mysql_query("update bookedtickets set amount=amount-1 where username='$usr' and tno='$tno'", $con);
		}
		mysql_query("update tickets set rest=rest+1 where tno='$tno'", $con);
		return TRUE;
	} else {
		return FALSE;
	}
}

function perDogMngTickets($newInfo, $con) {
	$row = mysql_fetch_array(mysql_query("select tno from tickets where tno='" . $newInfo['newTno'] . "'", $con));
	if ($row) {
		return mysql_query("update tickets set fromto='" . $newInfo['newFromto'] . "' ,time='" . $newInfo['newTime'] . "' ,price='" . $newInfo['newPrice'] . "' ,rest='" . $newInfo['newRest'] . "' ,deadline='" . $newInfo['newDeadline'] . "' ,changed=1 where tno='" . $newInfo['newTno'] . "'", $con);
	} else {
		return mysql_query("insert into tickets (tno,fromto,time,price,rest,deadline) values('" . $newInfo['newTno'] . "','" . $newInfo['newFromto'] . "','" . $newInfo['newTime'] . "','" . $newInfo['newPrice'] . "','" . $newInfo['newRest'] . "','" . $newInfo['newDeadline'] . "')", $con);
	}
}

function perDogMngMenbers($newInfo, $con) {
	$row = mysql_fetch_array(mysql_query("select username from user where username='" . $newInfo['newUsername'] . "'", $con));
	if ($row) {
		return mysql_query("update user set username='" . $newInfo['newUsername'] . "' ,password='" . $newInfo['newPassword'] . "' ,department='" . $newInfo['newDepartment'] . "' ,manager='" . $newInfo['newManager'] . "' ,chief='0' where username='" . $newInfo['newUsername'] . "'", $con);
	} else {
		return mysql_query("insert into user (username,password,department,manager,chief) values('" . $newInfo['newUsername'] . "','" . $newInfo['newPassword'] . "','" . $newInfo['newDepartment'] . "','" . $newInfo['newManager'] . "','0')", $con);
	}
}

function perDogDelTickets($delTno, $con) {
	return mysql_query("delete from tickets where tno='$delTno'", $con);
}

function perDogDelMenbers($delUsername, $con) {
	return mysql_query("delete from user where username='$delUsername'", $con);
}

function arr_foreach($arr) {
	if (!is_array($arr)) {
		return $arr;
	}
	foreach ($arr as $key => $val) {
		if (is_array($val)) {
			arr_foreach($val);
		} else {
			$arr[$key] = urlencode($val);
		}
	}
	return $arr;
}
?>