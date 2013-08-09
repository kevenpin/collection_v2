<?php
include('../lib/CollectionPage.php');
include('../collection/lib/simple_html_dom.php');
ini_set('memory_limit', '400M');
ini_set('max_execution_time',9000);
define("DataBaseName","collection");
include('../excel_operate/lib/conn.php');
$db=new DB('','','',$Conn);
$CollectionPage=new CollectionPage('http://www.assaybiotech.com/ProductCenter/Antibodies/',
									array(
										'tables'=>'demo',
										'listDOM'=>array('a'=>'.right .listTable .list td a'),
										'listClassDOM'=>'.right ul.L-show li',
										'listNextListDOM'=>array('1'=>'.right .page-show li'),
										'HtmlHook'=>'demoFunction',
										'chedren'=>array(
											'listDOM'=>'.main .right',
											'getFunction'=>'demogetFUNCTION',
										)
									),
									$db,
									'http://www.assaybiotech.com/',4);
//function demoFunction($html){echo $html;exit;}
function demogetFUNCTION($html,$db){
	$vale='';
	foreach ($html->find('.main .right') as $e){
		$vale['a']=$e->text();
	}
	$imageobj=$html->find('.main .right .TestedImage .detail img');
	$imageobj[1];
	$imasusr=$imageobj[1]->scr;
	$newimageurl=md5($imasusr).'.jpg';
	$imasusr='http://www.assaybiotech.com'.$imasusr
	$image=file_get_contents($imasusr);
	var_dump($vale);
	file_put_contents('d:/tmp/'.$newimageurl,$imag);
	exit;
	return $vale;
}
function a_function($list){
	//var_dump($list);
	return $list;
}