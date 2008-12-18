<?PHP
/*******************************************************************
 | 						Raha TinyFileManager Ver 1.9.3
 | 			------------------------------------------------
 |	@copyright: 		(C) 2008-2009 Raha Group, All Rights Reserved
 |	@license:		CC-BY-SA-4.0 <https://creativecommons.org/licenses/by-sa/4.0>
 |	@author: 		Mahdi NezaratiZadeh <HTTPS://Raha.Group>
 |	@since:			2008-12-18 12:21:10 GMT+0330 - 2009-01-09 15:30:03 GMT+0330
********************************************************************/
/*create table revisions(
id int unsigned not null auto_increment primary key,
time int unsigned not null,
path varchar(500) not null,
content longtext not null
)*/
header('Content-Type: Text/HTML; Charset=UTF-8');
ob_start('ob_gzhandler');
ini_set('error_reporting', 0);
session_start();
get_magic_quotes_gpc() && ($_REQUEST = killmq($_REQUEST)) && ($_COOKIE = killmq($_COOKIE)) && (($_GET = killmq($_GET)) || 1) && (($_POST = killmq($_POST)) || 1);
define('TS_PerPage', 20);
class Request {
	public function item() {
		for ($h=opendir($p = preg_replace(array('#//#', '#/$#'), array('/', ''), $_REQUEST['p']).'/'), $l=TS_PerPage*((int)$_REQUEST['l']-1); $i=readdir($h); $I[]=$i);
		closedir($h);
		for ($n=count($I), $j=$l, $c=$l+TS_PerPage; $j<$c && $j<$n; $i=$I[$j], $d['name'][]=$i, $d['size'][]=filesize($p.$i), $d['type'][]=filetype($p.$i), $d['modified'][]=filemtime($p.$i), $d['permission'][]=decoct(fileperms($p.$i)&0777), $d['owner'][]=fileowner($p.$i), $d['group'][]=filegroup($p.$i), $d['selected'][]=0, $j++);
		return json_encode($d+array('total' => $n));
	}
	public function modify() {
		isset($_POST['source']) ? file_put_contents($_POST['file'], $_POST['source']) : die(htmlspecialchars(file_get_contents($_POST['file'])));
		return 'The File was saved.: '.$_POST['file'];
	}
	public function upload() {
		copy($_FILES['item']['tmp_name'], $_POST['path'].$_FILES['item']['name']);
		return '<script>window.top.$("result").innerHTML = "The File was uploaded.: '.$_FILES['item']['name'].'"</script>';
	}
	public function rename() {
		return rename($_REQUEST['o'], $_REQUEST['n']) ? 'The directory/file '.$_REQUEST['o'].' was successfully renamed to '.$_REQUEST['n'].' .' : 'Could not rename...';
	}
	public function delete() {
		return rm_recurse($_REQUEST['i']) ? 'Item successfully deleted.' : '';
	}
	public function DL() {
		header('Content-Disposition: attachment; filename='.urlencode($_GET['i']));
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Description: File Transfer');
		header('Content-Length: '.filesize($_GET['i']));
		readfile($_GET['i']);
		die;
	}
	public function chmod() {
		return chmod($_POST['i'], eval('return "0".('.$_POST['or'].'+'.$_POST['ow'].'+'.$_POST['oe'].'+'.$_POST['gr'].'+'.$_POST['gw'].'+'.$_POST['ge'].'+'.$_POST['wr'].'+'.$_POST['ww'].'+'.$_POST['we'].');')) ? 'CHMOD Success' : 'CHMOD Error';
	}
	public function create() {
		return $_POST['t'] == 'f' ? touch($_POST['p'].$_POST['n']) : ($_POST['t'] == 'd' ? mkdir($_POST['p'].$_POST['n']) : symlink($_POST['l'], $_POST['p'].$_POST['n']));
	}
	public function C3() {
		for ($b=array(), $h=opendir($_REQUEST['p']); $i=readdir($h); is_dir($_REQUEST['p'].'/'.$i) && $i != '.' && ($b['name'][]=$i) && ($b['path'][]=$_REQUEST['p'].$i));
		closedir($h);
		return json_encode($b);
	}
	public function login() {
		
	}
}
class FTP {
	private $handle;
	public function __construct($h, $u, $p, $P = 21, $t = 90) {
		(($this->handle = ftp_connect($h, $P, $t)) || die("Couldn't connect to $h")) && (ftp_login($this->handle, $u, $p) || die('Couldn\'t connect as '.$u));
	}
	public function __destruct() {
		@ftp_close($this->handle);
	}
	public function __call($f, $a) {
		array_unshift($a, $this->handle);
		return call_user_func_array('ftp_'.$f, $a);
	}
}
function ftp_mode($f) {
	return ($i = pathinfo($f)) && preg_match('/am|asp|bat|c|cfm|cgi|conf|cpp|css|dhtml|diz|h|hpp|htm|html|in|inc|js|m4|mak|nfs|nsi|pas|patch|php|php3|php4|php5|phtml|pl|po|py|qmail|sh|shtml|sql|tcl|tpl|txt|vbs|xml|xrc/i', $i['extension']) ? FTP_ASCII : FTP_BINARY;
}
function rm_recurse($i) {
	if (is_dir($i) && !is_link($i)) {
		foreach(glob($i.'/*') as $s)
			if (!rm_recurse($s))
				return false;
		return rmdir($i);
	} else
		return unlink($i);
}
function killmq($v) {
	return is_array($v) ? array_map('killmq', $v) : stripslashes($v);
}
#$FTP = new FTP('ftp.example.com', 'usr', 'passwd', 21);
/*$buff = ftp_rawlist($cid,"/");
foreach ($buff as $file)
{
      if(ereg("([-d][rwxst-]+).* ([0-9]) ([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9]) ([0-9]{2}:[0-9]{2}) (.+)",$file,$regs))
    { 
        if(substr($regs[1],0,1)=="d") $isdir=1; else $isdir=0;
        $tmp_array['line']=$regs[0];
        $tmp_array['isdir']=$isdir;
        $tmp_array['rights']=$regs[1];
        $tmp_array['number']=$regs[2];
        $tmp_array['user']=$regs[3];
        $tmp_array['group']=$regs[4];
        $tmp_array['size']=$regs[5];
        $tmp_array['date']=date("m-d",strtotime($regs[6]));
        $tmp_array['time']=$regs[7];
        $tmp_array['name']=$regs[8];
     }
    $dir_list[]=$tmp_array;
}*/
@$_REQUEST['act'] && die(Request::$_REQUEST['act']($FTP));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
|	This Program has written By MAHDI NEZARATIZADEH
|	WEB : HTTPS://Raha.Group
-->
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>.::TinyFileManager::.</title>
		<style type="text/css">
			body {
				font:8pt tahoma; scrollbar-face-color: #dee3e7; scrollbar-light-color: #ffffff; scrollbar-shadow-color: #dee3e7; scrollbar-3dlight-color: #d1d7dc; scrollbar-arrow-color: #006699; scrollbar-track-color: #efefef; scrollbar-darkshadow-color: #98aab1
			}
			code {
				display: block; background-color: #f9f9f9; direction: ltr; padding: 5px; margin-top: 2px; margin-bottom: 2px; border: 1px solid #CCCCCC; font-family: verdana; font-size: 10pt
			}
			legend {
				color: #0046D5
			}
			p {
				font-family: tahoma; font-size: 10pt
			}
			fieldset {
				background: #ffffff; color: #000000; border: #D0D0BF 1px solid; font: 11px Tahoma, Verdana, sans-serif; -moz-border-radius: 5px
			}
			.tbl {
				font-family: tahoma; font-size: 8pt; padding: 5px; width: 100%; border-spacing: 0px 0px; border-collapse: collapse; border-bottom: solid 1px #b6c0c3; border-right: solid 1px #b6c0c3; background-color: #ffffff
			}
			.tbl th, .tbl td {
				border-top: solid 1px #b6c0c3; border-left: solid 1px #b6c0c3
			}
			.tbl th {
				background-color: #d6dbdf;
				background: transparent url(bars.png) repeat scroll 0 -192px;
				height: 23px;
				border: #d5d5d5 1px solid
			}
			.tbl th:hover, .tbl .active {
				background-position: 0 -215px;
				border-color: #96d9f9;
				cursor: default;
				-moz-user-select: none
			}
			.disable {
				cursor: default; filter:progid:DXImageTransform.Microsoft.Alpha(opacity=30); opacity: 0.3
			}
			#result {
				color: green
			}
			a:link, a:visited {
				color: #0066cc; text-decoration: none
			}
			a:hover {
				color: #99cc00
			}
			input {
				border: 1px solid #b5b8c8; background: #f5f7fa; background: url(bars.png) repeat scroll 0 -271px;
			}
			input:focus {
				border: 1px solid #7eadd9
			}
			.IMG {
				background: transparent url(fm.png) no-repeat scroll 0 0; display: inline-block; width: 14px; height: 14px
			}
			.add {
				background-position: 0 0
			}
			.edit {
				background-position: -14px 0
			}
			.delete {
				background-position: -28px 0
			}
			.new {
				background-position: -42px 0
			}
			.preference {
				background-position: -56px 0
			}
			.save {
				background-position: -70px 0
			}
			.compress {
				background-position: -84px 0
			}
			.reload {
				background-position: -98px 0
			}
			.close {
				background-position: -112px 0;
				width: 6px;
				height: 9px;
				cursor: pointer
			}
			.close:hover {
				background-position: -118px 0
			}
			.open {
				background-position: -124px 0;
				width: 6px;
				height: 6px
			}
			.open:hover {
				background-position: -130px 0
			}
			.bln {
				background-position: -124px -7px;
				cursor: default
			}
			.logo {
				background-position: -112px -9px;
				width: 7px;
				height: 7px
			}
			.computer {
				background-position: -137px 0;
				width: 16px;
				margin-right: 5px
			}
			.folder {
				background-position: -153px 0;
				width: 13px;
				height: 16px;
				margin: 0 5px 0 0;
			}
			.exit {
				display: inline;
				background-position: -166px 0;
				margin: 3px;
				float: right;
				width: 17px;
				height: 17px;
				cursor: pointer
			}
			.exit:hover {
				background-position: -183px 0
			}
			.exit:active {
				background-position: -200px 0
			}
			#NavigatorPreviousPage {
				background-position: -217px 0;
				width: 6px;
				height: 11px;
			}
			#NavigatorNextPage {
				background-position: -223px 0;
				width: 6px;
				height: 11px;
			}
			#NavigatorFirstPage {
				background-position: -229px 0;
				width: 10px;
				height: 12px;
			}
			#NavigatorLastPage {
				background-position: -239px 0;
				width: 10px;
				height: 12px;
			}
			.ASC {
				background-position: -217px -12px
			}
			.DESC {
				background-position: -224px -12px
			}
			.ASC, .DESC {
				float: right;
				width: 7px;
				height: 4px
			}
			.explore {
				background-position: -249px 0;
				width: 13px;
				height: 16px;
			}
			.rename {
				background-position: -262px 0;
				width: 16px;
				height: 12px;
			}
			.permission {
				background-position: -278px 0;
				width: 16px;
				height: 15px;
			}
			.copy {
				background-position: -294px 0;
				width: 15px;
				height: 14px;
			}
			.move {
				background-position: -309px 0;
				width: 11px;
				height: 11px;
			}
			.trash {
				background-position: -320px 0;
				width: 13px;
				height: 15px;
			}
			.properties {
				background-position: -333px 0;
				width: 19px;
				height: 16px;
			}
			.info {
				background-position: -352px 0;
				width: 16px;
				height: 16px;
			}
			.resize {
				background-position: -368px 0;
				width: 6px;
				height: 6px;
			}
			.tik {
				background-position: -368px -6px;
				width: 8px;
				height: 10px;
			}
			.down_disable {
				background-position: -376px 0;
				width: 9px;
				height: 6px;
			}
			.down_enable {
				background-position: -376px -6px;
				width: 9px;
				height: 6px;
			}
			.back {
				background-position: -385px 0;
				width: 11px;
				height: 11px;
			}
			.Bar {
				background: transparent url(bar.png) repeat scroll 0 0
			}
			.winbar {
				display: inline-block;
				background-position: 0 -51px;
				height: 28px;
				border: #a8a8a8 1px solid;
				-moz-border-radius: 0px
			}
			.MenuSeparatorVertical {
				border-left: 1px solid #e2e3e3; border-right: 1px inset #ffffff; width: 0; height: 100%; margin: 0 0 0 30px; display: block; position: absolute
			}
			.MenuSeparatorHorizontal {
				border-top: 1px solid #e2e3e3; border-bottom: 1px inset #ffffff; width: 170px; height: 0; margin: 4px 0 4px 30px; display: block
			}
			#Menu {
				width: 200px;
				background-color: #f1f1f1;
				border: #979797 1px solid;
				position: absolute;
				display: none;
				z-index: 7;
				-moz-user-select: none
			}
			.list-item {
				padding: 3px 0 0 5px;
				height: 17px;
				margin: 2px;
				-moz-border-radius: 3px;
				cursor: default;
				border: #f1f1f1 1px solid
			}
			.list-item .disable-text {
				color: #808080
			}
			.list-item .disable-icon {
				opacity: .3
			}
			.list-item .text {
				padding-left: 35px
			}
			.list-item .icon {
				position: absolute
			}
			.list-item:hover, .RowOver, .RowFocus, .RowActive, .RowSelect, .RowDisable {
				background: transparent url(bars.png) repeat scroll 0 0
			}
			.RowOver, .RowFocus {
				background-position: 0 0;
				border: #d8f0fa 1px solid
			}
			.list-item:hover, .RowActive {
				background-position: 0 -96px;
				border: #b6e6fb 1px solid
			}
			.RowSelect {
				background-position: 0 -48px;
				border: #99defd 1px solid
			}
			.RowDisable {
				background-position: 0 -144px;
				border: #d9d9d9 1px solid
			}
			.BTN {
				width: 18px;
				height: 18px;
				margin: 3px;
				padding: 2px;
				cursor: pointer;
				display: inline;
				-moz-user-select: none
			}
			.BTN:hover {
				background: transparent url(bars.png) repeat scroll 0 0;
				background-position: 0 -238px;
				outline: #abe61e 1px solid;
				opacity: 1
			}
			.BTN:active {
				background-position: 0 -238px;
				border: #b8ee90 1px solid
			}
			#D3 {
				width: 150px;
				overflow: scroll;
				vertical-align: top
			}
			#D3 .node {
				display: block;
				padding: 0 0 0 10px;
				margin: 0
			}
			#D3 .item {
				height: 20px;
				cursor: pointer
			}
			#D3 .link {
				padding: 0 5px 0 5px;
			}
			#D3 .link:hover, #D3 .link:active, #D3 .link:focus {
				background: transparent url(bar.png) repeat scroll 0 0;
				background-position: 0 -80px;
				border: #a9c8f5 1px solid;
				-moz-border-radius: 3px;
			}
			#D3 .active {
				background: transparent url(bar.png) repeat scroll 0 0;
				background-position: 0 -116px;
				border: #b7e7fc 1px solid;
				-moz-border-radius: 3px;
			}
			#Bln {
				position : absolute;
				z-index : 8;
				max-width : 200px;
			}
			#Bln div {
				float : left;
				background : #FFC;
				border : 1px solid #D4D5AA;
				padding : 5px;
				min-height : 25px;
			}
			#Bln h4 {
				width : 13px;
				height : 10px;
				padding : 0;
				margin : 0;
				font-size : 95%;
				font-weight : bold;
				margin-left : 10px;
				margin-top : -1px;
				clear : both;
				float : left;
			}
			#Bln p {
				font-size : 9pt;
				margin : 0;
				direction : lrt;
			}
			.Button {
				background: transparent url(btn.png) repeat scroll 0 0;
				height: 19px;
				border: #707070 1px solid;
				font: normal 11px tahoma, verdana, helvetica;
				cursor: pointer;
				-moz-border-radius: 3px
			}
			.Button:hover {
				background-position: 0 -19px;
				border-color: #3c7fb1;
				color: #30358c
			}
			.Button:active, .Button:focus {
				background-position: 0 -38px;
				border-color: #2c628b
			}
			.Tab {
				-moz-user-select: none;
				width: 100%;
				background-color: #00000;
				cursor: default;
				display: inline-block;
				height: 19px;
			}
			.Tab .Normal {
				background: transparent url(btn.png) repeat scroll 0 0;
				height: 16px;
				border: 1px solid #898c95;
				padding: 1px 5px 1px 5px;
				display: inline-block;
			}
			.Tab .Normal:hover {
				background-position: 0 -19px
			}
			.Tab .Select {
				height: 17px;
				border: 1px solid #898c95;
				border-bottom: none;
				padding: 5px 7px 1px 7px;
				margin-bottom: 0px;
				background-color: #ffffff;
				display: inline-block
			}
			#TabLayer {
				border: 1px solid #898c95;
				padding: 5px;
				background-color: #ffffff;
			}
			.Separator {
				border-left: 1px solid #9ac6ff; border-right: 1px inset #ffffff; width: 0; height: 13px; margin: 0 3px 0 3px
			}
			#Location {
				background-color: #e3eaf4;
				width: 500px;
				height: 20px;
				border: 1px solid;
				border-top-color: #454a4e;
				border-right-color: #696a6b;
				border-bottom-color: #a7b2bd;
				border-left-color: #83898e;
				padding-top: 5px;
				display: inline-block;
				margin-left: 100px
			}
			#Location:hover {
				background-color: #f7f9fc;
			}
			.NAV {
				background-color: #dbebfb
			}
			.NAV #LocationBack, .NAV #LocationForward {
				background: transparent url(nav.png) repeat scroll 0 0;
				display: inline-block;
				width: 25px;
				height: 25px
			}
			.NAV .LocationBack_Disabled {
				background-position: 0 0;
			}
			.NAV .LocationBack_Hover {
				background-position: 0 -25px;
			}
			.NAV .LocationBack_Active {
				background-position: 0 -50px;
			}
			.NAV .LocationForward_Disabled {
				background-position: -25px 0;
			}
			.NAV .LocationForward_Hover {
				background-position: -25px -25px;
			}
			.NAV .LocationForward_Active {
				background-position: -25px -50px;
			}
		</style>
		<script type="text/javascript">
window.XMLHttpRequest || (window.XMLHttpRequest = function() {
	try {
		return new ActiveXObject('Msxml2.XMLHTTP');
	} catch(e) {
		try {
			return new ActiveXObject('Microsoft.XMLHTTP');
		} catch(e) {
			alert('Error initializing XMLHttpRequest!');
		}
	}
});
$().addEventListener || ($().addEventListener = function(e, f) {
	$().attachEvent('on'+e, f);
});
$().removeEventListener || ($().removeEventListener = function(e, f) {
	$().detachEvent('on'+e, f);
});
Math.between = function(m, n, M) {
	return m<n && n<M;
}
document.documentElement ? (window.innerWidth = document.documentElement.clientWidth, window.innerHeight = document.documentElement.clientHeight) : document.body ? (window.innerWidth = document.body.clientWidth, window.innerHeight = document.body.clientHeight) : 0;
function $(m, o, v, _) {
	return typeof m == 'string'
		?
			typeof o == 'object'
			?
				$.itr(m, o)
			:
				m.indexOf('=') == -1 && m.indexOf('/') == -1
				?
					o
					?
						function(t, c, i) {
							return Wnd.create(t, c, i);
						}(m, o, v)
					:
						$().getElementById(m)
				:
					function(q, m, n, p) {
						var R = new XMLHttpRequest();
						q += '&AJAX='+new Date().getTime();
						if (m) {
							R.open('POST', p, true);
							R.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
						} else
							R.open('GET', p+(p.indexOf('?')>-1?'':'?')+q, true);
						R.onreadystatechange = function() {
							s = R.readyState;
							$('Loader').style.display = s<4 ? '' : 'none';
							GenerateWait(s<4);
							s>3 && typeof n == 'function' ? n(R.responseText, R.status) : typeof n != 'function' && ($(n).innerHTML = s<1 ? 'Initializing ...' : s<2 ? 'Sending ...' : s<3 ? 'Processing ...' : s<4 ? 'Getting ...' : R.status != 200 ? 'Error: '+R.statusText : R.responseText);
						};
						R.send(m ? q : null);
						delete R;
						return false;
					}(/^http:\/\/|^\//.test(m) ? '' : m, typeof v != undefined && !/^http:\/\/|^\//.test(m) ? v : 1, o||'result', /^http:\/\/|^\//.test(m) ? m : _||'./fm.php')
			:
				typeof m == 'object'
				?
					m.tagName == 'FORM'
					?
						function(o) {
							for (var i=0, b=0; T=o[i]; i++)
								if (!T.disabled && T.getAttribute('regular') && !RegExt[T.getAttribute('regular')][0].test(T.type=='checkbox'?T.checked:T.value)) {
									Bl(T, RegExt[T.getAttribute('regular')][1]);
									b = 1;
									break;
								}
							if (b)
								return false;
							for (var i=0, T, q=''; T=o[i]; i++) {
								var n = T.name||T.id, t = T.type, v = t == 'checkbox' ? T.checked ? T.value ? T.value : 1 : 0 : t == 'radio' ? T.checked ? T.value : null : T.value;
								n != undefined && n != '' && v != null && T.getAttribute('default') != v && !T.disabled && (q += '&'+n+'='+encodeURIComponent(v));
							};
							return $(q.slice(1), _eval, o.getAttribute('method').toLowerCase() != 'get', o.getAttribute('action'));
						}(m)
					:
					m.tagName == 'A'
					?
						function(o) {
							return $(o.href, _eval);
						}(m)
					:
					m.tagName == 'INPUT'
					?
						function(o) {
							if (!o.getAttribute('default'))
								o.setAttribute('default', o.value);
							if (!o.getAttribute('ntype'))
								o.setAttribute('ntype', o.type);
							if (o.value == o.getAttribute('default')) {
								o.type = o.getAttribute('ntype');
								o.value = '';
								o.focus();
							} else if (o.value == '') {
								o.type = 'text';
								o.value = o.getAttribute('default');
							}
						}(m)
					:
						0
				:
					window.document;
};
function _eval(t, s) {
	s != 200 ||
	(t.charCodeAt(0) == 35 ? eval(t.substring(1)) : t.charCodeAt(0) == 36 ? $('result').innerHTML = eval(t.substring(1)) : t.charCodeAt(0) == 45 ? alert(t.substring(1)) : $('result').innerHTML = t);
};
var RegExt = {file: [/.+/, 'Select File!'], name: [/^[A-z_\-.0-9]+$/, 'Invalid Name'], path: [/^[A-z_\-.0-9\/]+$/, 'Invalid Path'], SymbolicLink: [/^[A-z_\-.0-9\/]+$/, 'Invalid target of the symbolic link'], 'server': [/^[A-z_\-\/.0-9]+$/, 'Please enter valid server to connect'], 'username': [/^[A-z_\-.0-9]+$/, 'This username is invalid'], 'password': [/.+/, 'Invalid password'], 'directory': [/^[A-z_\-\/.0-9]+$/, 'Please enter valid directory'], 'port': [/^[0-9]{1,5}$/, 'Please enter valid port number']};
function Bl(E, T) {
	try {
		E.focus();
		E.select();
	} catch(e) {
	};
	var B = $('Bln'), x = 0, y = 0, o = E;
	while (o) {
		x += o.offsetLeft;
		y += o.offsetTop;
		o = o.offsetParent;
	}
	$('Blt').innerHTML = T;
	with (B.style) {
		opacity = 1;
		display = '';
		top = y-B.offsetHeight+'px';
		right = $().body.clientWidth-x-E.offsetWidth/1.2+'px';
	}
	E.onblur = E.onkeypress = B.onclick = function() {
		new Style('Bln').opacity(0);
		E.onblur = E.onkeypress = B.onclick = '';
	}
}
function ContextMenu(M) {
	var R = '<span class="MenuSeparatorVertical"></span>';
	for (var i=0; i<M.length; i++) {
		i && (R += '<span class="MenuSeparatorHorizontal"></span>');
		for (var j in M[i])
			R += '<div class="list-item" onclick="'+(M[i][j][2]?"$('Menu').setAttribute('hidden', 1);"+M[i][j][0]:"$('Menu').setAttribute('hidden', 0)")+'"><span class="IMG '+M[i][j][1]+' icon'+(M[i][j][2]?'':' disable-icon')+'"></span><span class="text '+(M[i][j][2]?'':' disable-text')+'">'+j+'</span></div>';
	}
	$('Menu').innerHTML = R;
}
function LF() {
	var L = $().createElement('div'), B = $().createElement('div');
	B.style.display = 'none';
	B.id = 'Bln';
	B.innerHTML = '<div><p id="Blt"></p></div><h4 class="IMG bln"></h4>';
	L.innerHTML = '<span style="color: #c0c0c0; font-size: 8pt">Programmed By <a href="//raha.group" style="color: #c0c0c0; font-size: 8pt" target="_blank">Raha.Group</a></span>';
	L.innerHTML += '<div style="top: 0; left: 0; background: #2277dd; position: absolute; z-index: 9; color: white; padding: 2px 5px 2px 10px; display: none" id="Loader">Loading...</div>';
	L.innerHTML += '<iframe name="Loader" style="display: none"></iframe>';
	L.innerHTML += '<div id="PageOverLayer"></div>';
	L.innerHTML += '<div id="Menu" hidden="1"></div>';
	L.innerHTML += '<div id="DD" style="display: none; width: 100px; height: 24px; border: #bdbdbd 1px solid; border-left-color: #e2e2e2; border-top-color: #e2e2e2; color: #1e1e1e; background-color: #f7f7f7; padding: 2px; position: absolute"><img src="drop-no.gif" />1 selected row</div>';
	L.appendChild(B);
	$().getElementsByTagName('body').item(0).appendChild(L);
}
function perms(m) {
	for (var b=m+' (', i=0; i<3; b+=(m[i]&04 ? 'r' : '-')+(m[i]&02 ? 'w' : '-')+(m[i]&01 ? 'x' : '-'), i++);
	return b+')';
}
function Style(e) {
	this.element = null;
	this.tmp = 0;
	this.value = function(k) {
		return parseInt(this.element.style[k]);
	}
	this.apply = function(a) {
		for (var k in a)
			this.element.style[k] = a[k];
		return true;
	};
	this.class = function(m, c) {
		for (var a=(this.element.className+' '+c).split(' '), i=0, v, t=''; v=a[i]; i++)
			if (m || v != c)
				t += v+' ';
		this.element.className = t.slice(0, -1);
		return true;
	}
	this.move = function(m, n) {
		var self = this;
		if (m) {
			this.apply({top: this.value('top')-(this.value('top')-n)*.2+'px'});
			this.value('top')-n>0 && setTimeout(function(){self.move(m, n)}, 50);
		} else {
			this.tmp || (this.tmp = this.value('top'))
			this.apply({top: this.value('top')+(this.value('top')+this.tmp)*.2+'px'});
			this.value('top')<n && setTimeout(function(){self.move(m, n)}, 50) || this.apply({display: 'none'});
		}
	};
	this.opacity = function(m, a) {
		var self = this;
		a == undefined && (a = m ? 0 : 100);
		this.apply({opacity: a/100, filter: 'Alpha(Opacity='+a+')'});
		!m && a == 0 || m && a == 100 || setTimeout(function(){self.opacity(m, m ? a+25 : a-25)}, 100);
		return true;
	}
	this.init = function(e) {
		this.element = typeof e == 'object' ? e : $(e);
	}
	this.init(e);
}
var Wnd = {
	left: 0,
	right: 0,
	object: null,
	tmp: null,
	create: function(t, c, i) {
		var l = $().createElement('div');
		l.innerHTML = '<div style="min-width: 300px; min-height: 100px; z-index: 6; position: absolute; opacity: 0; left: 400px; top: '+document.body.clientHeight+'px"><div class="Bar winbar" onmousedown="Wnd.move(this)" style="width: 100%; cursor: move"><span style="display: inline; float: left; margin: 7px 0px 0px 7px; cursor: default" class="'+(i?i:'IMG logo')+'"></span><span style="float: left; padding: 7px 0 0 5px; font-weight: bold">'+t+'</span><div class="IMG exit" style="margin-right: 10px" onclick="Wnd.close(this)"></div></div><div style="border: #a8a8a8 1px solid; padding: 10; direction: ltr; background-color: #ffffff">'+c+'<div style="width: 100%; text-align: right"><span class="IMG resize" style="cursor: nw-resize" onmousedown="Wnd.resize(this)"></span></div></div></div>';
		$().getElementsByTagName('body').item(0).appendChild(l);
		new Style(l.childNodes[0]).move(1, document.body.scrollTop+200);
		new Style(l.childNodes[0]).opacity(1);
	},
	move: function(o) {
		Wnd.tmp = Wnd.object;
		Wnd.object = o.parentNode;
		$().onmousemove = function() {
			var l, t;
			with (Wnd.object) {
				Math.between(0, l=Wnd.left+MX, window.innerWidth-offsetWidth-1) && (style.left = l+'px');
				Math.between(0, t=Wnd.top+MY, window.innerHeight-offsetHeight-1) && (style.top = t+'px');
			}
			return false;
		};
		$().onmousedown = function() {
			Wnd.tmp && (Wnd.tmp.style.zIndex = 5);
			with (Wnd.object)
				(Wnd.left = parseInt(style.left, 10)-MX, Wnd.top = parseInt(style.top, 10)-MY, style.zIndex = 6);
			return false;
		};
		$().onmouseup = function(){
			$().onmousemove = $().onmouseup = $().onmousedown = null
		};
	},
	resize: function(o) {
		Wnd.object = o.parentNode.parentNode.parentNode;
		$().onmousemove = function() {
			with (Wnd.object)
				(style.width = offsetWidth+MX-Wnd.left+'px', Wnd.left = MX, style.height = offsetHeight+MY-Wnd.top+'px', Wnd.top = MY);
			return false;
		};
		$().onmousedown = function() {
			(Wnd.left = MX, Wnd.top = MY);
			return false;
		};
		$().onmouseup = function() {
			$().onmousemove = $().onmouseup = $().onmousedown = null
		};
	},
	close: function(o) {
		new Style(o.parentNode.parentNode).move(0, document.body.clientHeight);
		new Style(o.parentNode.parentNode).opacity(0);
	}
}
			function GenerateWait(m) {
				new Style('PageOverLayer').apply(m ? {width: window.innerWidth+'px', height: window.innerHeight+'px', background: '#4FA3DA', position: 'absolute', left: 0, top: 0, opacity: 5/10, filter: 'alpha(opacity='+5*10+')', display: 'block'} : {display: 'none'});
			}
			var C3 = {
				object: null,
				number: 0,
				HighLight: function(o) {
					C3.object && new Style(C3.object).class(0, 'active');
					new Style(C3.object = o).class(1, 'active');
				},
				show: function(D, n) {
					var E = $(n != undefined ? 'C3N'+n : 'D3');
					if (n != undefined) {
						new Style('C3T'+n).class(0, 'close');
						new Style('C3T'+n).class(1, 'open');
						$('C3N'+n).style.display = '';
					} else {
						C3.number = 0;
						E.innerHTML = '<span class="BTN" style="margin-right: 0px"><span class="IMG computer" onclick="C3.Generate(\'./\')"></span></span><span class="BTN" style="margin-left: 0px" onclick="Explore(\'.\')">Root</span>';
					}
					for (var i=0; i<D.name.length; i++)
						E.innerHTML += '<span class="node"><span class="item"><span id="C3T'+C3.number+'" class="IMG close" style="opacity: 1" onclick="C3.Generate(\''+D.path[i]+'/\', '+C3.number+')"></span><span class="link" onclick="Explore(\''+D.path[i]+'\');C3.HighLight(this)"><span class="IMG folder"></span>'+D.name[i]+'</span></span><span id="C3N'+C3.number+++'" style="display: none"></span></span>';
					setTimeout(C3.HideToggle, 1000);
				},
				Generate: function(p, n) {
					if (n != undefined && $('C3N'+n).innerHTML != '')
						$('C3N'+n).style.display == '' ? ($('C3T'+n).className = $('C3T'+n).className.replace(' open', ' close'), $('C3N'+n).style.display = 'none') : ($('C3T'+n).className = $('C3T'+n).className.replace(' close', ' open'), $('C3N'+n).style.display = '');
					else
						DirectoryCache[p]?C3.show(DirectoryCache[p], n):$('act=C3&p='+p+'/', function(t, s) {
							if (s != 200)
								return false;
							C3.show(eval('DirectoryCache[p]='+t), n);
						});
					return false;
				},
				ShowToggle: function() {
					for (var i=0; i<C3.number; $('C3T'+i).style.opacity<1 && new Style('C3T'+i).opacity(1), i++);
				},
				HideToggle: function() {
					if (MX>$('D3').offsetWidth+5)
						for (var i=0; i<C3.number; new Style('C3T'+i).opacity(0), i++);
				}
			};
			var MX, MY;
			function MT(e) {
				document.all == undefined ? (MX = e.pageX, MY = e.pageY) : (MX = $().body.scrollLeft+event.clientX, MY = $().body.scrollTop+event.clientY);
			}
			$().addEventListener('mousemove', MT, true);
			function CheckForm(o) {
				for (var i=0, T, b=true; T=o[i]; i++)
					if (!T.disabled && T.getAttribute('regular') && !RegExt[T.getAttribute('regular')][0].test(T.type=='checkbox'?T.checked:T.value)) {
						Bl(T, RegExt[T.getAttribute('regular')][1]);
						b = false;
						break;
					}
				return b;
			}
			var TPL = {
				edit: function(f, r) {
					return '<form method="post" onsubmit="return $(this)"><input type="hidden" name="act" value="modify" /><input type="hidden" name="file" value="'+f+'" /><table style="width: 100%"><tr><td><textarea name="source" cols="45" rows="10" dir="ltr">'+r.replace('<', '&'+'gt;').replace('>', '&'+'lt;')+'</textarea></td></tr><tr><td align="center"><input type="submit" class="Button" name="submit" value="Modify" /></td></tr></table></form>';
				},
				upload: function() {
					return '<form method="post" enctype="multipart/form-data" target="Loader" onsubmit="return CheckForm(this)"><input type="hidden" name="act" value="upload" /><table style="width: 100%; direction: ltr"><tr><td style="width: 50%">File:</td><td><input type="file" name="item" regular="file" /></td></tr><tr><td>Path:</td><td><input name="path" value="'+$PATH+'" dir="ltr" regular="path" /></td></tr><tr><td colspan="2" align="center"><input type="submit" class="Button" name="submit" value="Upload" /></td></tr></table></form>';
				},
				permission: function(i, p) {
					p = p.slice(p.indexOf('(')+1, p.indexOf(')'));
					return '<form method="post" onsubmit="return $(this)"><input type="hidden" name="act" value="chmod" /><input type="hidden" name="i" value="'+i+'" /><table class="tbl" style="width: 100%; direction: ltr"><thead><tr><th>Attribute</th><th>Owner</th><th>Group</th><th>World</th></tr></thead><tbody><tr><td>Read</td><td><input type="checkbox" name="or" value="400" '+(p[0] == 'r' ? 'checked ' : '')+'/></td><td><input type="checkbox" name="gr" value="40" '+(p[3] == 'r' ? 'checked ' : '')+'/></td><td><input type="checkbox" name="wr" value="4" '+(p[6] == 'r' ? 'checked ' : '')+'/></td></tr><tr><td>Write</td><td><input type="checkbox" name="ow" value="200" '+(p[1] == 'w' ? 'checked ' : '')+'/></td><td><input type="checkbox" name="gw" value="20" '+(p[4] == 'w' ? 'checked ' : '')+'/></td><td><input type="checkbox" name="ww" value="2" '+(p[6] == 'w' ? 'checked ' : '')+'/></td></tr><tr><td>Execute</td><td><input type="checkbox" name="oe" value="100" '+(p[2] == 'x' ? 'checked ' : '')+'/></td><td><input type="checkbox" name="ge" value="10" '+(p[5] == 'x' ? 'checked ' : '')+'/></td><td><input type="checkbox" name="we" value="1" '+(p[8] == 'x' ? 'checked ' : '')+'/></td></tr></tbody><tfoot><tr><td colspan="4" align="center"><input type="submit" class="Button" name="submit" value="Apply" /></td></tr></tfoot></table></form>';
				},
				new: function() {
					return '<form method="post" onsubmit="return $(this)" name="NewFileForm"><input type="hidden" name="act" value="create" /><table style="width: 100%; direction: ltr"><tbody><tr><td style="width: 50%">Name:</td><td><input name="n" dir="ltr" regular="name" /></td></tr><tr><td>Type:</td><td><select name="t" onchange="$(\'symbolic_link\').style.display=this.value==\'l\'?\'\':\'none\';$().NewFileForm.l.disabled=this.value==\'l\'?false:true"><option value="f">File</option><option value="d">Directory</option><option value="l">Symbolic Link</option></select></tr><tr style="display: none" id="symbolic_link"><td>Target of the symbolic link:</td><td><input name="l" dir="ltr" value="./" regular="SymbolicLink" disabled /></td></tr><tr><td>Path:</td><td><input name="p" dir="ltr" value="'+$PATH+'" regular="path" /></td></tr></tbody><tfoot><tr><td colspan="2" align="center"><input type="submit" class="Button" name="submit" value="Create" /></td></tr></tfoot></table></form>';
				},
				about: function() {
					return '<div style="background: url(avatar.png) no-repeat; height: 100px; padding: 50px 10px 0 140px">This Program has written By Mahdi NezaratiZadeh<br />Web: <a href="//raha.group" target="_blank">Raha.Group</a></div><div style="margin-left: 20px; background: url(logo.png) no-repeat; height: 20px; padding: 65px 0 0 25px">Copyright © 2005-2009 <b>RahaGroup™</b>.All Right Reserved</div>';
				}
			};
			var Navigator = {
				CurrentPage: 1,
				TotalPage: 1,
				Initializing: function(t) {
					Navigator.TotalPage = Math.ceil(t/<?=TS_PerPage?>);
					$('NavigatorInputPage').value = Navigator.CurrentPage;
					$('NavigatorTotalPage').innerHTML = Navigator.TotalPage;
					for (var e=['PreviousPage', 'FirstPage', 'NextPage', 'LastPage'], i=0; i<e.length; i++)
						with ($('Navigator'+e[i]))
							i<2 && Navigator.CurrentPage == 1 || i>1 && Navigator.TotalPage == Navigator.CurrentPage ? (style.opacity = .3, style.cursor = 'default', onclick = '') : (style.opacity = 1, style.cursor = 'pointer', onclick = new Function('Navigator.'+e[i]+'()'));
				},
				PreviousPage: function() {
					Explore($PATH, --Navigator.CurrentPage);
				},
				FirstPage: function() {
					Explore($PATH, 1);
				},
				NextPage: function() {
					Explore($PATH, ++Navigator.CurrentPage);
				},
				LastPage: function() {
					Explore($PATH, Navigator.TotalPage);
				}
			}
			var Sort = {
				key: 'name',
				order: 1,
				object: null,
				sort: function() {
					do
						for (var i=0, w=0, t; i<Data.name.length-1; i++)
							if ((Sort.order && Data[Sort.key][i]>Data[Sort.key][i+1] || !Sort.order && Data[Sort.key][i]<Data[Sort.key][i+1]) && (w=1))
								for (var j=0, k=['name', 'size', 'type', 'modified', 'permission', 'owner', 'group', 'selected']; j<k.length; t=Data[k[j]][i], Data[k[j]][i]=Data[k[j]][i+1], Data[k[j]][i+1]=t, j++);
					while (w);
				},
				set: function(k, o) {
					Sort.key = k;
					Sort.object && new Style(Sort.object).class(0, 'active') && (Sort.object.childNodes[1].className = '');
					o.childNodes[1] || o.appendChild($().createElement('span'));
					o.className = 'active';
					o.childNodes[1].className = 'IMG '+((Sort.order = Sort.object == o ? !Sort.order : Sort.order) ? 'DESC' : 'ASC');
					Sort.object = o;
				}
			}
			function size(w) {
				for (var s=['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'], i=0; w/1024>1; w/=1024, i++);
				return Math.round(w, 3)+' '+s[i];
			}
			var Data, $PATH='.';
			function Explore(p, l) {
				$PATH = p+'/';
				Location.navigator($PATH);
				var e = $PATH.split('/'), b = '', L = '';
				for (var i=0; i<e.length; L+=e[i]+'/', e[i] && (b += '<span class="BTN" style="margin-right: 0" onclick="Explore(\''+L+'\')">'+e[i]+'</span><span class="BTN" style="margin-right: 0" onclick="Location.list(\''+L+'\')"><span class="IMG close"></span></span>'), i++);
				$('Location').innerHTML = b;
				$('act=item&p='+($PATH)+'&l='+(Navigator.CurrentPage = l||1), new Function('arguments[1]==200||$().location.reload();eval("Data="+arguments[0]);ItemList()'));
			}
			var Tab = {
				data: {},
				switch: function(t) {
					for (var k in Tab.data)
						$('Tab'+k).className = k == t ? 'Select' : 'Normal';
					$('TabLayer').innerHTML = Tab.data[t];
				},
				generator: function(d, s) {
					var k, t='';
					Tab.data = d;
					for (k in d)
						t += '<span class="'+(k==s?'Select':'Normal')+'" id="Tab'+k+'" onclick="Tab.switch(\''+k+'\')">'+k+'</span>';
					return '<div style="padding: 5px; background-color: #f0f0f0"><div class="Tab">'+t+'</div><div id="TabLayer">'+d[s]+'</div></div>';
				}
			};
			function Properties(i, t) {
				$(Data.name[i]+' Properties', Tab.generator({'General': '<fieldset><legend>Detail</legend><table style="width: 100%"><tr><td></td><td><input type="text" value="'+Data.name[i]+'" style="width: 100%" readonly /></td></tr><tr><td colspan="2"><hr color="#808080" size="1" /></td></tr><tr><td>Type</td><td>'+Data.type[i]+'</td></tr><tr><td>Location</td><td>'+$PATH+Data.name[i]+'</td></tr><tr><td>Size</td><td>'+size(Data.size[i])+'</td></tr><tr><td>Date Modified</td><td>'+new Date(Data.modified[i]*1000).toGMTString()+'</td></tr><tr><td></td><td></td></tr></table></fieldset>', 'Detail': 'Soon...', 'Permission': '<fieldset><legend>Permission</legend>'+TPL.permission(Data.name[i], Data.permission[i])+'</fieldset>'}, t||'General'), 'IMG properties');
			}
			function ItemList() {
				Data.ContextMenu = {};
				Sort.sort();
				for (var TB=$('item').getElementsByTagName('tbody')[0], TR=TB.getElementsByTagName('tr'), i=3; i<TR.length; TB.removeChild(TR[i]), $('D3').rowSpan--);
				for (var i=0; i<Data.name.length; i++) {
					var TB = $('item').getElementsByTagName('tbody')[0], TR = TB.getElementsByTagName('tr'), NR = document.createElement('tr'), d = [Data.name[i], '<span class="BTN"><span class="IMG edit'+(Data.type[i] != 'file' ? ' disable" style="cursor: default"' : '" onclick="modify(Data.name['+i+'])"')+' title="Edit"></span></span><span class="BTN"><span class="IMG compress'+(Data.type[i] != 'file' ? ' disable" style="cursor: default"' : '" onclick="extract(Data.name['+i+'])"')+' title="Uncompress"></span></span><span class="BTN"><span class="IMG save" title="Download" onclick="DL(Data.name['+i+'])"></span></span><span class="BTN"><span class="IMG delete" title="Delete" onclick="RM('+i+')"></span></span></span>', size(Data.size[i]), Data.type[i], new Date(Data.modified[i]*1000).toGMTString(), perms(Data.permission[i]), Data.owner[i], Data.group[i]];
					NR.style.backgroundColor = '#'+(i%2?'ffffff':'f8f8f8');
					NR.setAttribute('index', i);
					NR.onmouseover = function() {
						this.className = Data.selected[this.getAttribute('index')] != 1 ? 'RowOver' : 'RowActive';
					}
					Data.selected[i] && (NR.className = 'RowSelect');
					NR.onmouseout = Data.selected[i] ? function() {
						Data.ContextMenu[this.getAttribute('index')] || (this.className = Data.selected[this.getAttribute('index')] ? 'RowSelect' : 'RowOut');
					} : function() {
						Data.ContextMenu[this.getAttribute('index')] || (this.className = 'RowOut');
					}
					NR.onclick = function() {
						var b;
						Data.selected[this.getAttribute('index')] = b = Data.selected[this.getAttribute('index')]==1?0:1;
						this.onmouseout = function() {
							Data.ContextMenu[this.getAttribute('index')] || (this.className = b != 1 ? 'RowOut' : 'RowSelect');
						}
						this.className = b ? 'RowActive' : 'RowOver';
					}
					NR.oncontextmenu = function(e) {
						var i=this.getAttribute('index');
						Data.ContextMenu[i] = 1;
						ContextMenu([{Explore: ["Explore($PATH+'"+Data.name[i]+"')", 'explore', Data.type[i] == 'dir'], Rename: ["$('ItemName"+i+"').onclick()", 'rename', 1], Permision: ["$('ItemPermission"+i+"').onclick()", 'permission', 1]}, {Copy: ['', 'copy', 0], Move: ['', 'move', 0], Delete: ["RM("+i+")", 'trash', 1]}, {Properties: ['Properties('+i+')', 'properties', 1]}, {About: ["$('About', TPL.about(), 'IMG info')", 'info', 1]}]);
						e = e||event;
						with ($('Menu').style) {
							display = 'block';
							top = (e.clientY+$('Menu').offsetHeight<window.innerHeight?e.clientY:e.clientY-$('Menu').offsetHeight)+'px';
							left = (e.clientX+$('Menu').offsetWidth<window.innerWidth?e.clientX:e.clientX-$('Menu').offsetWidth)+'px';
						}
						Data.ContextMenu.object = this;
						document.onclick = new Function('Data.ContextMenu['+i+'] = 0;Data.ContextMenu.object.onmouseout();$("Menu").getAttribute("hidden") == 1 && ($("Menu").style.display = "none")');
						return false;
					};
					NR.onmousedown = function() {
						$('DD').style.display = '';
						// this.className = 'RowDisable';
						return false;
					}
					NR.onmousemove = function() {
						$('DD').style.top = MY+20+'px';
						$('DD').style.left = MX+10+'px';
						return false;
					}
					NR.onmouseup = function() {
						$('DD').style.display = 'none';
						NR.onmousemove = NR.onmouseup = NR.onmousedown = null;
					}
					for (var j=0; j<d.length; j++) {
						var e = $().createElement('div');
						e.innerHTML = d[j];
						if (j == 0) {
							e.style.paddingLeft = '10px';
							e.id = 'ItemName'+i;
							e.onclick = function() {
								var T = this.onclick;
								this.onclick = '';
								t = this.innerHTML;
								var e = document.createElement('input');
								e.value = t;
								e.onblur = function() {
									this.parentNode.onclick = T;
									this.parentNode.innerHTML = this.value;
									this.value != t && $('act=rename&o='+$PATH+t+'&n='+$PATH+this.value)
								}
								e.style.width = '100%';
								e.style.fontSize = '11px';
								this.innerHTML = '';
								this.appendChild(e);
								this.childNodes[0].focus();
							}
							e.style.cursor = 'pointer';
						} else if (j == 5) {
							e.id = 'ItemPermission'+i;
							e.onclick = function() {
								Properties(this.parentNode.parentNode.getAttribute('index'), 'Permission');
							}
							e.style.cursor = 'pointer';
						}
						var TD = document.createElement('td');
						TD.appendChild(e);
						NR.appendChild(TD);
						TB.appendChild(NR);
					}
					$('D3').rowSpan++;
				}
				Navigator.Initializing(Data.total);
				$('result').innerHTML = 'This page loaded success.';
			}
			function modify(f) {
				$('act=modify&file='+$PATH+f, function(r, s) {
					$(f, TPL.edit($PATH+f, r), 'IMG edit');
				}, 1, 'fm.php');
			}
			function RM(i) {
				i ? Wnd.create('Delete Item', '<div style="padding: 5px; background-color: #f0f0f0"><div style="background: url(trash.png) no-repeat; height: 38px; padding: 10px 0 0 50px">Are you sure you want to permanently delete this item?</div><div style="background: url(doc.png) no-repeat; margin-left: 45px; height: 121px; padding: 30px 0 0 100px">'+Data.name[i]+'<br />Date modified: '+new Date(Data.modified[i]*1000).toGMTString()+'</div><div style="width: 100%; text-align: right"><input type="button" class="Button" value="Yes" style="margin: 5px; width: 50px" onclick="$(\'act=delete&i=\'+$PATH+Data.name['+i+']);Wnd.close(this.parentNode.parentNode)" /><input type="button" class="Button" value="No" onclick="Wnd.close(this.parentNode.parentNode)" style="margin: 5px; width: 50px" /></div></div>', 'IMG trash') : Wnd.create('Delete Multiple Items', '<div style="padding: 5px; background-color: #f0f0f0"><div style="background: url(warning.png) no-repeat; height: 38px; padding: 10px 0 0 50px">Are you sure you want to permanently delete these '+Data.selected.length+' items?</div><div style="width: 100%; text-align: right"><input type="button" class="Button" value="Yes" style="margin: 5px; width: 50px" onclick="$(\'act=delete&i=\'+$PATH+Data.name['+i+']);Wnd.close(this.parentNode.parentNode)" /><input type="button" class="Button" value="No" onclick="Wnd.close(this.parentNode.parentNode)" style="margin: 5px; width: 50px" /></div></div>', 'IMG trash');
			}
			function DL(i) {
				window.Loader.location.href = '?act=dl&i='+$PATH+i;
			}
			window.onload = function() {
				LF();
				Sort.set('name', $('ItemListName'));
				Explore('.');
				C3.Generate('.');
			}
			var DirectoryCache = {};
			var Location = {
				path: [],
				index: 0,
				navigator: function(p) {
					Location.path[Location.path.length] = p;
					new Style('LocationBack').class(Location.path.length<2, 'LocationBack_disabled');
					$('LocationBack').onclick = Location.path.length<2 ? '' : 'LocationBack_disabled';
					new Style('LocationForward').class(Location.path.length<2, 'LocationForward_disabled');
					$('LocationForward').onclick = Location.path.length<2 ? '' : 'LocationForward_disabled';
					Location.index++;
				},
				back: function() {
					Explore(Location.path[Location.path.length-2]);
					Location.index--;
				},
				forward: function() {
					Explore(Location.path[Location.index]);
					Location.index++;
				},
				generator: function(D) {
					var d={};
					for (var i=0; i<D.name.length; i++)
						d[D.name[i]] = ["Explore('"+D.path[i]+"')", $PATH == D.path[i]+'/' ? 'tik' : 'folder', $PATH != D.path[i]+'/'];
					ContextMenu([d]);
					with ($('Menu').style) {
						display = 'block';
						top = (MY+$('Menu').offsetHeight<window.innerHeight?MY:MY-$('Menu').offsetHeight)+'px';
						left = (MX+$('Menu').offsetWidth<window.innerWidth?MX:MX-$('Menu').offsetWidth)+'px';
					}
					document.onclick = new Function('$("Menu").getAttribute("hidden") == 1 && ($("Menu").style.display = "none")');
				},
				list: function(p) {
					$('act=C3&p='+p+'/',
						function(t, s) {
							if (s != 200)
								return false;
							Location.generator(eval('('+t+')'));
						}
					);
				}
			};
		</script>
	</head>
	<body>
		<table class="tbl" style="width: 100%" cellpadding="3" align="center" id="item">
			<tbody>
				<tr>
					<td class="NAV" colspan="9">
						<div style="display: inline-block; margin-left: 30px">
							<span class="LocationBack_Disabled" id="LocationBack" onmouseover="this.className='LocationBack_Hover'" onmouseout="this.className='LocationBack_Disabled'" onmousedown="this.className='LocationBack_Active'"></span>
							<span class="LocationForward_Disabled" id="LocationForward" onmouseover="this.className='LocationForward_Hover'" onmouseout="this.className='LocationForward_Disabled'" onmousedown="this.className='LocationForward_Active'"></span>
						</div>
						<div id="Location"></div>
					</td>
				</tr>
				<tr>
					<th>Directory Tree</th>
					<th style="width: 200px" id="ItemListName" onclick="Sort.set('name', this);ItemList();">Name</th>
					<th style="color: #003366">Action</th>
					<th onclick="Sort.set('size', this);ItemList();">Size</th>
					<th onclick="Sort.set('type', this);ItemList();">Type</th>
					<th onclick="Sort.set('modified', this);ItemList();">Date Modified</th>
					<th onclick="Sort.set('permission', this);ItemList();">Permission</th>
					<th onclick="Sort.set('owner', this);ItemList();">Owner</th>
					<th onclick="Sort.set('group', this);ItemList();">Group</th>
				</tr>
				<tr>
					<td rowspan="1" id="D3" onmouseover="C3.ShowToggle()" onmouseout="C3.HideToggle()"></td>
				</tr>
			</tbody>
			</tfoot>
				<tr style="background-color: #d0def0">
					<td colspan="3" style="padding-left: 160px">
						<span class="BTN">
							<span class="IMG" id="NavigatorFirstPage" title="First Page"></span>
						</span>
						<span class="BTN">
							<span class="IMG" id="NavigatorPreviousPage" title="Previous Page"></span>
						</span>
						<span class="Separator"></span>
						Page <input size="1" style="font-size: 11px" onfocus="this.select()" onblur="this.value != Navigator.CurrentPage && Explore($PATH, this.value)" id="NavigatorInputPage" /> of <span id="NavigatorTotalPage"></span>
						<span class="Separator"></span>
						<span class="BTN">
							<span class="IMG" id="NavigatorNextPage" title="Next Page"></span>
						</span>
						<span class="BTN">
							<span class="IMG" id="NavigatorLastPage" title="Last Page"></span>
						</span>
					</td>
					<td colspan="4" id="result"></td>
					<td colspan="2" align="center">
						<span class="BTN"><span class="IMG add" onclick="$('Upload', TPL.upload(), 'IMG add')" title="Upload"></span></span>
						<span class="BTN"><span class="IMG new" onclick="$('New', TPL.new(), 'IMG new')" title="New"></span></span>
						<span class="BTN"><span class="IMG delete" onclick="RM()" title="Delete selected file(s)"></span></span>
						<span class="BTN"><span class="IMG reload" onclick="Explore($PATH)" title="Reload"></span></span>
						<span class="BTN"><span class="IMG preference" onclick="$('Preference', $('preference').innerHTML, 'IMG preference')" title="Preference"></span></span>
						<span class="BTN"><span class="IMG info" onclick="$('About', TPL.about(), 'IMG info')" title="About"></span></span>
					</td>
				</tr>
			</tfoot>
		</table>
		<div id="preference" style="display: none">
		<fieldset>
			<legend>Settings</legend>
			<form method="post" onsubmit="return $(this)">
				<input type="hidden" name="act" value="login" />
				<table>
					<tbody>
						<tr>
							<td style="width: 50%">
								<label for="host">Server: </label><input name="host" id="host" value="<?=$_SESSION['fm']['host']?>" regular="server" />
							</td>
							<td>
								<label for="port">Port: </label><input name="port" id="port" size="5" value="<?=$_SESSION['fm']['port']?>" regular="port" />
							</td>
						</tr>
						<tr>
							<td>
								<label for="username">UserName: </label><input name="username" id="username" value="<?=$_SESSION['fm']['username']?>" regular="username" />
							</td>
							<td>
								<label for="">Mode: </label>
								<select id="mode" name="mode">
									<option value="automatic">Automatic</option>
									<option value="binary">Binary</option>
									<option value="ascii">Ascii</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="password">Password: </label><input type="password" name="password" id="password" value="<?=$_SESSION['fm']['password']?>" regular="password" />
							</td>
							<td>
								<label for="remember">Remember in cookies: </label>
								<input type="checkbox" name="remember" id="remember" value="1" />
							</td>
						</tr>
						<tr>
							<td>
								<label for="directory">Directory: </label><input name="directory" id="directory" value="<?=$_SESSION['fm']['directory']?$_SESSION['fm']['directory']:'./'?>" regular="directory" />
							</td>
							<td>
								<input type="submit" name="submit" value="Login" />
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</fieldset>
		</div>
	</body>
</html>
<!-- Powered By WWW.Raha.Group -->
