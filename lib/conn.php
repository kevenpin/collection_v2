<?php
    define("ServerName","localhost:3306");
	define("UserName","root");
	define("PassWord","123456");
	define("DataBaseName","collection");
    require_once("DB.class.php");
	$Conn=mysql_connect(ServerName,UserName,PassWord) or die("连接服务器失败 !!!");
	mysql_query("set names 'utf8'");//UTF-8
	//mysql_query("set names 'GB2312'");
	mysql_select_db(DataBaseName);
	$db=new DB('','','',$Conn);
?>