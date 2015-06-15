<?php
ini_set("display_errors", "Off");
require_once 'connectVars.php';
session_start();
$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db('onlinebookingsystem', $con);
mysql_query("set names utf8");
$uri = $_POST['uri'];
$usr = $_SESSION['username'];
$err = array('err' => FALSE);
$addNav = array('additionalNav' => FALSE);
$addNavM = array('additionalNav' => TRUE, 'navItem' => array(0 => array('navName' => '分区管理', 'navUri' => 'deptMgr')));
$addNavC = array('additionalNav' => TRUE, 'navItem' => array(0 => array('navName' => '分区管理', 'navUri' => 'deptMgr'), 1 => array('navName' => '权限狗', 'navUri' => 'perDog')));
$data = array_merge($err, $addNav);
switch ($uri) {
	case 'index-login' :
		//获取权限
		$sql = "select manager,chief from user where username='$usr'";
		$result = mysql_query($sql, $con);
		$row = mysql_fetch_array($result);
		if ($row['chief'] == 1) {
			$data = array_merge($err, $addNavC);
		} else if ($row['manager'] == 1) {
			$data = array_merge($err, $addNavM);
		}
		//获取车票
		$data = getTickets($data, $con);
		break;
	case 'index' :
		$data = getTickets($data, $con);
		break;
	case 'manage' :
		$data = getBookedTickets($data, $con, $usr);
		break;
	case 'deptMgr' :
		$data = deptMgr($data, $con, $usr);
		break;
	case 'perDog' :
		$data = perDog($data, $con, $usr);
		break;
	default :
		$data['err'] = TRUE;
		$data = arr_foreach($data);
		$data = json_encode($data);
		break;
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

function getTickets($data, $con) {
	$result = mysql_query("select * from tickets where overdue = 0", $con);
	$tickets = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tickets[] = $row;
	}
	$moduleContent = file_get_contents('html/tickets-available.html');
	$ticketsContent = '';
	$tickets = arr_foreach($tickets);
	foreach ($tickets as $key => $val) {
		$ticketsContent .= '<tr data-changed=\'' . $val['changed'] . '\'><td>' . $val['fromto'] . '</td><td>' . $val['time'] . '</td><td>' . $val['price'] . '</td><td>' . $val['rest'] . '</td><td>' . $val['deadline'] . '</td><td><button data-tno=\'' . $val['tno'] . '\'>预订</button></td></tr>';
	}
	$moduleContent = substr_replace($moduleContent, $ticketsContent, strpos($moduleContent, '</tbody>'), 0);
	$module = array('module' => array(0 => array('moduleClass' => 'bookTickets', 'moduleTitle' => '预订车票', 'moduleContent' => 'toBeReplaced')));
	$data = array_merge($data, $module);
	$data = arr_foreach($data);
	$data = json_encode($data);
	$data = substr_replace($data, $moduleContent, strpos($data, 'toBeReplaced'), 12);
	return $data;
}

function getBookedTickets($data, $con, $usr) {
	$result = mysql_query("select tickets.*,bookedtickets.amount from tickets,bookedtickets where bookedtickets.username='$usr' and bookedtickets.tno=tickets.tno and tickets.overdue=0", $con);
	$tickets = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tickets[] = $row;
	}
	$moduleContent = file_get_contents('html/manage-tickets.html');
	$ticketsContent = '';
	$tickets = arr_foreach($tickets);
	foreach ($tickets as $key => $val) {
		$ticketsContent .= '<tr data-changed=\'' . $val['changed'] . '\'><td>' . $val['fromto'] . '</td><td>' . $val['time'] . '</td><td>' . $val['price'] . '</td><td>' . $val['amount'] . '</td><td><button class=\'refund\' data-tno=\'' . $val['tno'] . '\'>退订</button><button class=\'transfer\' data-tno=\'' . $val['tno'] . '\'>转让</button></td></tr>';
	}
	$moduleContent = substr_replace($moduleContent, $ticketsContent, strpos($moduleContent, '</tbody>'), 0);
	$module = array('module' => array(0 => array('moduleClass' => 'bookTickets', 'moduleTitle' => '管理车票', 'moduleContent' => 'toBeReplaced')));
	$data = array_merge($data, $module);
	$data = arr_foreach($data);
	$data = json_encode($data);
	$data = substr_replace($data, $moduleContent, strpos($data, 'toBeReplaced'), 12);
	return $data;
}

function deptMgr($data, $con, $usr) {
	//检查权限
	$checkRow = mysql_fetch_array(mysql_query("select manager from user where username='$usr'", $con));
	if ($checkRow['manager'] == '0') {
		$data['err'] = TRUE;
		$data = arr_foreach($data);
		$data = json_encode($data);
		return $data;
	}
	$result = mysql_query("select distinct bookedtickets.username,tickets.*,bookedtickets.amount from user,tickets,bookedtickets where user.department in (select user.department from user where user.username='$usr') and bookedtickets.tno=tickets.tno and user.username = bookedtickets.username and bookedtickets.username!='$usr' order by bookedtickets.username,bookedtickets.tno asc", $con);
	$tickets = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tickets[] = $row;
	}
	$moduleContent0 = file_get_contents('html/dept-manage.html');
	$ticketsContent = '';
	$tickets = arr_foreach($tickets);
	foreach ($tickets as $key => $val) {
		$ticketsContent .= '<tr data-changed=\'' . $val['changed'] . '\'><td>' . $val['username'] . '</td><td>' . $val['fromto'] . '</td><td>' . $val['time'] . '</td><td>' . $val['price'] . '</td><td>' . $val['amount'] . '</td><td><button class=\'mgrRefund\' data-tno=\'' . $val['tno'] . '\' data-usr=\'' . $val['username'] . '\'>退订</button></td></tr>';
	}
	$moduleContent0 = substr_replace($moduleContent0, $ticketsContent, strpos($moduleContent0, '</tbody>'), 0);
	$result = mysql_query("select bookedtickets.tno,user.department,sum(bookedtickets.amount) from bookedtickets,user where user.department in (select user.department from user where user.username='$usr') and user.username=bookedtickets.username group by bookedtickets.tno desc,user.department asc", $con);
	$statistics = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$statistics[] = $row;
	}
	$moduleContent1 = file_get_contents('html/statistics.html');
	$statisticsContent = '';
	$statistics = arr_foreach($statistics);
	foreach ($statistics as $key => $val) {
		$statisticsContent .= '<tr><td>' . $val['tno'] . '</td><td>' . $val['department'] . '</td><td>' . $val['sum(bookedtickets.amount)'] . '</td></tr>';
	}
	$moduleContent1 = substr_replace($moduleContent1, $statisticsContent, strpos($moduleContent1, '</tbody>'), 0);
	$module = array('module' => array(0 => array('moduleClass' => 'bookTickets', 'moduleTitle' => '管理车票', 'moduleContent' => 'toBeReplaced0'), 1 => array('moduleClass' => 'ticketStatistics', 'moduleTitle' => '车票统计', 'moduleContent' => 'toBeReplaced1')));
	$data = array_merge($data, $module);
	$data = arr_foreach($data);
	$data = json_encode($data);
	$data = substr_replace($data, $moduleContent0, strpos($data, 'toBeReplaced0'), 13);
	$data = substr_replace($data, $moduleContent1, strpos($data, 'toBeReplaced1'), 13);
	return $data;
}

function perDog($data, $con, $usr) {
	//检查权限
	$checkRow = mysql_fetch_array(mysql_query("select chief from user where username='$usr'", $con));
	if ($checkRow['chief'] == '0') {
		$data['err'] = TRUE;
		$data = arr_foreach($data);
		$data = json_encode($data);
		return $data;
	}
	$result = mysql_query("select * from tickets order by tno asc", $con);
	$tickets = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$tickets[] = $row;
	}
	$moduleContent0 = file_get_contents('html/manage-tickets-perdog.html');
	$ticketsContent = '';
	$tickets = arr_foreach($tickets);
	foreach ($tickets as $key => $val) {
		$ticketsContent .= '<tr data-changed=\'' . $val['changed'] . '\'><td>' . $val['tno'] . '</td><td>' . $val['fromto'] . '</td><td>' . $val['time'] . '</td><td>' . $val['price'] . '</td><td>' . $val['rest'] . '</td><td>' . $val['deadline'] . '</td><td><button class=\'ticketsEdit\' data-tno=\'' . $val['tno'] . '\'>编辑</button><button class=\'ticketsDel\' data-tno=\'' . $val['tno'] . '\'>删除</button></td></tr>';
	}
	$moduleContent0 = substr_replace($moduleContent0, $ticketsContent, strpos($moduleContent0, '</tbody>'), 0);
	$result = mysql_query("select bookedtickets.tno,user.department,sum(bookedtickets.amount) from bookedtickets,user where user.username=bookedtickets.username group by bookedtickets.tno desc,user.department asc", $con);
	$statistics = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$statistics[] = $row;
	}
	$moduleContent1 = file_get_contents('html/statistics.html');
	$statisticsContent = '';
	$statistics = arr_foreach($statistics);
	foreach ($statistics as $key => $val) {
		$statisticsContent .= '<tr><td>' . $val['tno'] . '</td><td>' . $val['department'] . '</td><td>' . $val['sum(bookedtickets.amount)'] . '</td></tr>';
	}
	$moduleContent1 = substr_replace($moduleContent1, $statisticsContent, strpos($moduleContent1, '</tbody>'), 0);
	$result = mysql_query("select * from user where chief=0 order by department asc,manager desc,username asc", $con);
	$menbers = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$menbers[] = $row;
	}
	$moduleContent2 = file_get_contents('html/manage-menbers-perdog.html');
	$menbersContent = '';
	$menbers = arr_foreach($menbers);
	foreach ($menbers as $key => $val) {
		$menbersContent .= '<tr><td>' . $val['username'] . '</td><td>' . $val['department'] . '</td><td>' . $val['manager'] . '</td><td><button class=\'menbersEdit\' data-usr=\'' . $val['username'] . '\'>编辑</button><button class=\'menbersDel\' data-usr=\'' . $val['username'] . '\'>删除</button></td></tr>';
	}
	$moduleContent2 = substr_replace($moduleContent2, $menbersContent, strpos($moduleContent2, '</tbody>'), 0);
	$module = array('module' => array(0 => array('moduleClass' => 'ticketsMng', 'moduleTitle' => '车票管理', 'moduleContent' => 'toBeReplaced0'), 1 => array('moduleClass' => 'ticketStatisticsAll', 'moduleTitle' => '车票统计', 'moduleContent' => 'toBeReplaced1'), 2 => array('moduleClass' => 'menbersMng', 'moduleTitle' => '人员管理', 'moduleContent' => 'toBeReplaced2')));
	$data = array_merge($data, $module);
	$data = arr_foreach($data);
	$data = json_encode($data);
	$data = substr_replace($data, $moduleContent0, strpos($data, 'toBeReplaced0'), 13);
	$data = substr_replace($data, $moduleContent1, strpos($data, 'toBeReplaced1'), 13);
	$data = substr_replace($data, $moduleContent2, strpos($data, 'toBeReplaced2'), 13);
	return $data;
}

echo urldecode($data);
?>