$(document).ready(function() {
	onLoad();
	//导航事件
	$('.nav').on('click', 'li a', function(e) {
		e.preventDefault();
		var uri = $(this).attr('href');
		ajaxLoad(uri);
		setState(uri);
		$('.nav li').removeClass('nav-active');
		$(this).parent('li').addClass('nav-active');
	});
	//登录按钮绑定事件
	$('.login button').on('click', function() {
		var usr = $('.login input[type=text]').val();
		var pwd = $('.login input[type=password]').val();
		if (checkForm(usr, pwd)) {
			submitLogin(usr, pwd);
		}
	});
	//回车键提交
	$('.login input').on('keydown', function(e) {
		if (e.keyCode == 13) {
			$('.login button').trigger('click');
		}
	});
	//注销按钮
	$('#container').on('click', '.logout button', function() {
		if (!loged()) {
			popWarning('然而并没有什么卵用，因为你根本没有登录');
		} else {
			$.ajax({
				type: "post",
				url: "../action/logout.php",
				async: false,
				success: function() {
					var url = '/';
					window.location = url;
				}
			});
		}
	});
	//搜索
	$('#search').on('keydown', function(e) {
		if (e.keyCode == 13) {
			ajaxLoad('search');
			setState('search');
		}
	});
	//******新增元素事件绑定******
	//修改密码确认按钮
	$('#container').on('click', '.chgPwd', function() {
		var pwdInfo = '<input type="password" class="oldPwd" placeholder="旧密码" /><input type="password" class="newPwd" placeholder="新密码" /><input type="password" class="confirmNewPwd" placeholder="确认新密码" /><button class="confirmChgPwd">确定</button>';
		maskPop(pwdInfo);
		$('#container').find('.confirmChgPwd').on('click', function() {
			var pinfo = {
				'oldPwd': $.md5($('.oldPwd').val()),
				'newPwd': $.md5($('.newPwd').val()),
				'confirmNewPwd': $.md5($('.confirmNewPwd').val())
			};
			if (pinfo.newPwd != pinfo.confirmNewPwd) {
				popWarning('两次新密码输入不一致！');
				return;
			}
			perDogMng(pinfo, 'chgPwd', 'self', '修改密码成功！', '修改失败！');
			ajaxLoad('index');
		});
	});
	//预订车票按钮
	$('#container').on('click', '.book', function() {
		var tno = $(this).attr('data-tno');
		mngTickets(tno, 'book', 'self', '成功预订一张车票！', '预订失败!');
		ajaxLoad('index');
		//		window.location.reload();
	});
	//退订车票
	$('#container').on('click', '.refund', function() {
		var tno = $(this).attr('data-tno');
		mngTickets(tno, 'refund', 'self', '成功退订一张车票！', '退订失败!');
		ajaxLoad('manage');
	});
	//转让车票
	$('#container').on('mouseover', '.transfer', function() {
		var inputTop = $(this).position().top - $('#transfer-target').height() - 5;
		$('#transfer-target').css('top', inputTop);
		$('#transfer-target').fadeIn();
		$('#transfer-target').focus();
	});
	$('#container').on('mouseleave', '.transfer', function() {
		$('#transfer-target').fadeOut();
	});
	$('#container').on('click', '.transfer', function() {
		var tno = $(this).attr('data-tno');
		var targetUsr = $('#transfer-target').val();
		mngTickets(tno, 'transfer', targetUsr, '成功转让一张车票！', '转让失败!用户名不存在或数据出错');
		ajaxLoad('manage');
	});
	//分区管理员
	$('#container').on('click', '.mgrRefund', function() {
		var tno = $(this).attr('data-tno');
		var targetUsr = $(this).attr('data-usr');
		mngTickets(tno, 'refund', targetUsr, '成功退订一张车票！', '退订失败!');
		ajaxLoad('deptMgr');
	});
	$('#container').on('click', '.mgrBook', function() {
		var usrList = mngTickets(null, 'getUsrList', 'self', null, null);
		var usrSelectList = '';
		$.each(usrList, function(key, value) {
			usrSelectList += '<option value="' + value.username + '">' + value.username + '</option>';
		});
		var mgrBook = '<select class="mgrBookUsr"><option value="">用户名</option>' + usrSelectList + '</select><input class="mgrBookTno" type="text" placeholder="车票编号" /><button class="mgrBookTicket">预订</button>';
		maskPop(mgrBook);
		$('#container').find('.mgrBookTicket').on('click', function() {
			var targetUsr = $('.mgrBookUsr option:selected').val();
			var tno = $('.mgrBookTno').val();
			mngTickets(tno, 'book', targetUsr, '成功预订一张车票！', '预订失败!');
			ajaxLoad('deptMgr');
		});
	});
	//权限狗
	$('#container').on('click', '.addTicket', function() {
		var ticketInfo = '<input type="text" class="newFromto" placeholder="始发地-目的地" /><input type="datetime-local" class="newTime" placeholder="时间" /><input type="number" class="newPrice" placeholder="价格" /><input type="number" class="newRest" placeholder="剩余票数" /><input type="datetime-local" class="newDeadline" placeholder="截止日期" /><button class="addNewTicket">发布</button>';
		maskPop(ticketInfo);
		$('#container').find('.addNewTicket').on('click', function() {
			var nti = new Array();
			nti[0] = getTnoTime();
			nti[1] = $('.newFromto').val();
			nti[2] = $('.newTime').val().replace(/\//g, '-').replace('T', ' ') + ':00';
			nti[3] = $('.newPrice').val();
			nti[4] = $('.newRest').val();
			nti[5] = $('.newDeadline').val().replace(/\//g, '-').replace('T', ' ') + ':00';
			var newTicketInfo = {
				'newTno': nti[0].toString(),
				'newFromto': nti[1].toString(),
				'newTime': nti[2].toString(),
				'newPrice': nti[3].toString(),
				'newRest': nti[4].toString(),
				'newDeadline': nti[5].toString()
			};
			perDogMng(newTicketInfo, 'addTicket', 'new', '成功发布一张车票！', '发布失败！');
			ajaxLoad('perDog');
		});
	});
	$('#container').on('click', '.ticketsEdit', function() {
		var oti = {
			'oldTno': $(this).attr('data-tno'),
			'oldFromto': $(this).parents('tr').children().eq(1).html(),
			'oldTime': $(this).parents('tr').children().eq(2).html().replace(/-/g, '/').substring(0, 16),
			'oldPrice': $(this).parents('tr').children().eq(3).html(),
			'oldRest': $(this).parents('tr').children().eq(4).html(),
			'oldDeadline': $(this).parents('tr').children().eq(5).html().replace(/-/g, '/').substring(0, 16)
		};
		var ticketInfo = '<input type="text" class="newFromto" placeholder="始发地-目的地" value="' + oti.oldFromto + '" /><input type="text" class="newTime" placeholder="时间" value="' + oti.oldTime + '" /><input type="number" class="newPrice" placeholder="价格" value="' + oti.oldPrice + '" /><input type="number" class="newRest" placeholder="剩余票数" value="' + oti.oldRest + '" /><input type="text" class="newDeadline" placeholder="截止日期" value="' + oti.oldDeadline + '" /><button class="confirmEditTicket">确定</button>';
		maskPop(ticketInfo);
		$('#container').find('.confirmEditTicket').on('click', function() {
			var nti = new Array();
			nti[0] = oti.oldTno;
			nti[1] = $('.newFromto').val();
			nti[2] = $('.newTime').val().replace(/\//g, '-').replace('T', ' ') + ':00';
			nti[3] = $('.newPrice').val();
			nti[4] = $('.newRest').val();
			nti[5] = $('.newDeadline').val().replace(/\//g, '-').replace('T', ' ') + ':00';
			var newTicketInfo = {
				'newTno': nti[0].toString(),
				'newFromto': nti[1].toString(),
				'newTime': nti[2].toString(),
				'newPrice': nti[3].toString(),
				'newRest': nti[4].toString(),
				'newDeadline': nti[5].toString()
			};
			perDogMng(newTicketInfo, 'editTicket', 'new', '成功修改一张车票！', '修改失败！');
			ajaxLoad('perDog');
		});
	});
	$('#container').on('click', '.ticketsDel', function() {
		var delTno = $(this).attr('data-tno');
		if (confirm('确认删除车票编号为' + delTno + '的车票？')) {
			perDogMng(delTno, 'delTicket', 'new', '成功删除一张车票！', '删除失败！');
			ajaxLoad('perDog');
		}
	});
	$('#container').on('click', '.addMenber', function() {
		var menberInfo = '<input type="text" class="newUsername" placeholder="用户名" /><input type="text" class="newPassword" placeholder="密码" /><input type="text" class="newDepartment" placeholder="学院" /><select class="newManager"><option value="0">非管理员</option><option value="1">管理员</option></select><button class="addNewMenber">新增</button>';
		maskPop(menberInfo);
		$('#container').find('.addNewMenber').on('click', function() {
			var nmi = new Array();
			nmi[0] = $('.newUsername').val();
			nmi[1] = $.md5($('.newPassword').val());
			nmi[2] = $('.newDepartment').val();
			nmi[3] = $('.newManager option:selected').val();
			var newMenberInfo = {
				'newUsername': nmi[0],
				'newPassword': nmi[1],
				'newDepartment': nmi[2],
				'newManager': nmi[3],
			};
			perDogMng(newMenberInfo, 'addMenber', 'new', '成功新增一个人员！', '新增失败！');
			ajaxLoad('perDog');
		});
	});
	$('#container').on('click', '.menbersEdit', function() {
		var omi = {
			'oldUsername': $(this).attr('data-usr'),
			'oldDepartment': $(this).parents('tr').children().eq(1).html(),
			'oldManager': $(this).parents('tr').children().eq(2).html()
		};
		var menberInfo = '<input type="text" class="newUsername" placeholder="用户名" value="' + omi.oldUsername + '" /><input type="text" class="newPassword" placeholder="密码" /><input type="text" class="newDepartment" placeholder="学院" value="' + omi.oldDepartment + '" /><select class="newManager"><option value="0">非管理员</option><option value="1">管理员</option></select><button class="confirmEditMenber">确定</button>';
		maskPop(menberInfo);
		$('#container').find('.confirmEditMenber').on('click', function() {
			var nmi = new Array();
			nmi[0] = $('.newUsername').val();
			nmi[1] = $.md5($('.newPassword').val());
			nmi[2] = $('.newDepartment').val();
			nmi[3] = $('.newManager option:selected').val();
			var newMenberInfo = {
				'newUsername': nmi[0],
				'newPassword': nmi[1],
				'newDepartment': nmi[2],
				'newManager': nmi[3],
			};
			perDogMng(newMenberInfo, 'addMenber', 'new', '成功修改一个人员！', '修改失败！');
			ajaxLoad('perDog');
		});
	});
	$('#container').on('click', '.menbersDel', function() {
		var delUsername = $(this).attr('data-usr');
		if (confirm('确认删除用户名为' + delUsername + '的用户？')) {
			perDogMng(delUsername, 'delMenber', 'new', '成功删除一个用户！', '删除失败！');
			ajaxLoad('perDog');
		}
	});
});

function ajaxLoad(uri) {
	var extraData = $('#search').val();
	$.ajax({
		type: "post",
		url: "../action/ajax.php",
		data: {
			'uri': uri,
			'extData': extraData
		},
		async: false,
		success: function(data) {
			data = $.parseJSON(data);
			if (data.err == '1') {
				popWarning('数据错误!');
				return;
			}
			if (data.additionalNav == '1') {
				$.each(data.navItem, function(key, value) {
					$('.nav').append('<li><a href="' + value.navUri + '">' + value.navName + '</a></li>');
				});
			}
			$('#container').html('<span class="logout"><button>注销</button></span>');
			$.each(data.module, function(key, value) {
				var htmldata = '<div class="module ' + value.moduleClass + '">\
				<div class="module-title">\
					<h2>' + value.moduleTitle + '</h2>\
					<hr />\
				</div>\
				<div class="module-content">\
				' + value.moduleContent + '\
				</div>\
			</div>';
				$('#container').append(htmldata);
			});
		}
	});
	//激活相应事件
	activateEvent(uri);
}

function setState(uri) {
	var stateobj = ({
		url: uri,
		title: uri
	});
	var url = '?' + uri;
	window.history.pushState(stateobj, null, url);
}

function activateEvent(uri) {
	var maskPopContent = '<div class="mask showPop"><div class="optPop showPop"><span class="closePop">+</span></div></div>';
	$('#container').append(maskPopContent);
	switch (uri) {
		case 'index':
		case 'index-login':
			var chgPwd = '<button class="chgPwd">修改密码</button>';
			$('#container').append(chgPwd);
			break;
		case 'manage':
			var transTarget = '<input id="transfer-target" type="text" placeholder="要转让的用户名" />';
			$('#container').append(transTarget);
			break;
		case 'deptMgr':
			var mgrBook = '<button class="mgrBook">预订车票</button>';
			$('#container').append(mgrBook);
			break;
		case 'perDog':
			$('.perDog-ticket-statistics').rowspan(0).rowspan(3);
			var addTicket = '<button class="addTicket">发布车票</button>';
			var addMenber = '<button class="addMenber">新增人员</button>';
			$('#container').append(addTicket).append(addMenber);
			break;
		default:
			break;
	}
}

function mngTickets(tno, action, targetUsr, popStr1, popStr2) {
	var returnData = '';
	$.ajax({
		type: "post",
		url: "../action/bookAndManage.php",
		data: {
			'tno': tno,
			'action': action,
			'targetUsr': targetUsr
		},
		async: false,
		success: function(data) {
			if (action == 'getUsrList') {
				returnData = JSON.parse(data);
				return;
			}
			if (data == '1') {
				popWarning(popStr1);
			} else {
				popWarning(popStr2);
			}
		}
	});
	return returnData;
}

function perDogMng(newStuff, action, targetUsr, popStr1, popStr2) {
	$.ajax({
		type: "post",
		url: "../action/bookAndManage.php",
		data: {
			newInfo: newStuff,
			action: action,
			targetUsr: targetUsr
		},
		async: false,
		success: function(data) {
			if (data == '1') {
				popWarning(popStr1);
			} else {
				popWarning(popStr2);
			}
		}
	});
}

function submitLogin(usr, pwd) {
	pwd = $.md5(pwd);
	$.ajax({
		type: "post",
		url: "../action/login.php",
		data: {
			'username': usr,
			'password': pwd
		},
		async: true,
		success: function(data) {
			if (data) {
				ajaxLoad('index-login');
				setState('index');
				return;
			}
			popWarning('账号或密码错误！');
		}
	});
}

function loged() {
	var logstate;
	$.ajax({
		type: "post",
		url: "../action/logedOrNot.php",
		async: false,
		success: function(data) {
			logstate = data;
			return data;
		}
	});
	return logstate;
}

function checkForm(usr, pwd) {
	if (usr == '') {
		popWarning('账号不能为空！')
		$('.login input[type=text]').focus();
		return false;
	}
	if (pwd == '') {
		popWarning('密码不能为空！')
		$('.login input[type=password]').focus();
		return false;
	}
	return true;
}

function popWarning(msg) {
	$('body').append('<div class="popWarning"></div>');
	$('.popWarning').html(msg).fadeIn(500, function() {
		$(this).delay(2000).fadeOut(500, function() {
			$(this).remove();
		});
	});
}

function maskPop(popContent) {
	var popC = '<span class="closePop">+</span>' + popContent;
	$('.optPop').html(popC);
	$('.showPop').show('fast');
	$('.closePop').on('click', function() {
		$('.showPop').hide('fast');
	});
}

function onLoad() {
	var url = '/';
	if (loged()) {
		url = window.location.href.split('?')[1];
		if (url == null) {
			url = '?index-login';
			ajaxLoad('index-login');
			setState('index');
		} else {
			ajaxLoad('index-login');
			ajaxLoad(url);
			setState(url);
			$('.nav li').removeClass('nav-active');
			$('.nav li a[href=' + url + ']').parent('li').addClass('nav-active');
			url = '?' + url;
		}
	} else {
		$('#usr').focus();
	}
}

window.addEventListener('popstate', function(e) {
	var state = e.state;
	if (loged()) {
		ajaxLoad(state.url);
		$('.nav li').removeClass('nav-active');
		$('.nav li a[href=' + state.url + ']').parent('li').addClass('nav-active');
	}
});

jQuery.fn.rowspan = function(colIdx) { //封装的一个JQuery小插件 ，合并相同行
	return this.each(function() {
		var that;
		$('tr', this).each(function(row) {
			$('td:eq(' + colIdx + ')', this).filter(':visible').each(function(col) {
				if (that != null && $(this).html() == $(that).html() && $(this).text() != "") {
					rowspan = $(that).attr("rowSpan");
					if (rowspan == undefined) {
						$(that).attr("rowSpan", 1);
						rowspan = $(that).attr("rowSpan");
					}
					rowspan = Number(rowspan) + 1;
					$(that).attr("rowSpan", rowspan);
					$(this).hide();
				} else {
					that = this;
				}
			});
		});
	});
}

function getTnoTime() {
	var currTime = new Date();
	var timeInfo = {
		y: currTime.getFullYear(),
		m: currTime.getMonth() + 1,
		d: currTime.getDate(),
		h: currTime.getHours(),
		mi: currTime.getMinutes(),
		s: currTime.getSeconds()
	};
	var tno = '';
	for (x in timeInfo) {
		tno += timeInfo[x] > 9 ? timeInfo[x].toString() : '0' + timeInfo[x].toString();
	}
	return tno;
}