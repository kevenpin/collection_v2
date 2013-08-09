<?php
class CollectionPage{
/*
	$this->Parameter['listDOM'];//采集的列表区域
	$this->Parameter['listNextDOM'];采集的列表分页区域下一页
	$this->Parameter['otherTag'];采集的列表区域其余标签
	$this->Parameter['chedren'];采集一条单数据区域
	$this->Parameter['chedren']['listDOM'];//采集一条单数据区域
	$this->Parameter['chedren']['listNextDOM'];//采集一条单数据区域的分页
	$this->Parameter['chedren']['otherTag'];//采集一条单数据区域其余标签
	$this->Parameter['savaHtml'];//设置页面保存 false 是开启，ture是关闭， 如果数据量比较大，建议开启，如果数据比较小，建议关闭。开启后数据库会占用过多的资源
	$this->tables;//设置保存数表
	$this->Parameter['confTable'];//保存数据的时候直接操作表 。若为false则不直接操作表（序列化数据后保存到表），为true则直接操作表
*/
	var $Parameter=array(
						'listDOM'=>'',
						'listNextDOM'=>'',
						'listNextListDOM'=>'',
						'listClassDOM'=>'',
						'otherTag'=>'',
						'HtmlHook'=>'',
						'chedren'=>array(
							'listDOM'=>'',
							'getFunction'=>'',
							'HtmlHook'=>'',
							'listNextDOM'=>'',
							'otherTag'=>''
						),
						'savaHtml'=>false,
						'confTable'=>false
		);
	var $debugConfig;// 从1到9
	var $site;
	var $startPoint=array(1=>false,2=>false);
	var $db;
	var $tables='demo';
	var $classPerant;
	var $classPerantUrl;
	function CollectionPage($url,$config,$db,$site,$debugConfig=''){
		$this->debugConfig=$debugConfig;
		$this->db=$db;
		$this->site=$site;
		$this->tables=$config['tables'];
		$config['chedren']=array_merge($this->Parameter['chedren'],$config['chedren']);
		$this->Parameter=array_merge($this->Parameter,$config);
	//	$this->Parameter['listNextListDOM']=$config['listNextListDOM'];
		$this->setStartPoint();
		$this->claPageList($url);
	}
	function findDOM($parm){
		//$parm=trim($parm);
		$Apaginglist=false;
		if(!empty($parm)){
			if(is_array($parm)){
				foreach ($parm as $k=>$v){
					$finddom=$v;
					$Hdom=$k;
				}
			}else{
				$finddom=$parm;
			}
			$finddom=trim($finddom);
			$Apaginglist=array();
			$i=0;
			foreach($this->html->find($finddom) as $ele){
				$Apaginglist[$i]['title']=$ele->title;
				$Apaginglist[$i]['href']=$ele->href;
				$Apaginglist[$i]['innertext']=$ele->innertext;
				if(is_array($this->Parameter['otherTag'])){
					foreach($this->Parameter['otherTag'] as $k=>$v){
						$Apaginglist[$i][$v]=$ele->$v;
					}
				}
				$i++;
			}
			if(function_exists($Hdom.'_function')){
				$Hfunction=$Hdom.'_function';
				$Apaginglist=$Hfunction($Apaginglist);
			}
		}
		return $Apaginglist;
	}
	function claPageList($url){
		$this->Parameter['listClassDOM']=trim($this->Parameter['listClassDOM']);
		if(empty($this->Parameter['listClassDOM'])){
			$this->debug(1,'列表有配置');
			$this->colPageList($url);
		}else{
			
			$html=file_get_html($url);
			$classList='';
			$k=0;
			$rr=$html->find($this->Parameter['listClassDOM']);
			
			foreach($html->find($this->Parameter['listClassDOM']) as $ele){
				$classList[$k]['url']=$ele->firstChild()->href;
				$classList[$k]['name']=$ele->text();
				$k++;
			}
			$html->clear();
			unset($html);
			$this->debug(1,$classList);
			foreach($classList as $k=>$v){
				$this->classPerant=$v['name'];
				$this->classPerantUrl=$v['url'];
				if($this->startPoint[1]!=false){
					if($this->startPoint[1]['classname']==$v['name']){
						$this->colPageList($v['url'],$v['name']);
					}
				}else{
					$this->colPageList($v['url'],$v['name']);
				}
			}
		}
	}
	function colPageList($url,$classname='',$listname=''){
		$bool=true;
		$url=$this->handleUrl($url);
		if($this->startPoint[1]!=false){
			$pid=$this->checkUrl($url,1,true,$classname,$listname);
			if(!$pid){
				return false;
			}
			$bool=false;
		}else{
			$pid=$this->checkUrl($url,1,true,$classname,$listname);
		}
		$this->startPoint[1]=false;
		$this->html=file_get_html($this->site.$url);
		$html=$this->html->innertext;
		if($this->Parameter['savaHtml']==false){
			$this->saveCol($pid,array('html'=>$html));
		}
		$this->html->clear();
		if(!empty($this->Parameter['HtmlHook'])){
			if(function_exists($this->Parameter['HtmlHook'])){
				$html=$this->Parameter['HtmlHook']($html);
			}
		}
		$this->html=str_get_html($html);
		unset($html);		
		$listNext='';
		$listNextListDOM='';
		$listDOM=$this->Parameter['listDOM'];
		if(!is_array($listDOM)){
			$listDOM=array($listDOM);
		}
		$listContents=$this->findDOM($listDOM);
		$listDOM=$this->Parameter['listNextDOM'];
		if(!is_array($listDOM)){
			$listDOM=array($listDOM);
		}
		if(!empty($this->Parameter['listNextListDOM'])){
					$jk=0;
			if(is_array($this->Parameter['listNextListDOM'])){
				$start=array_keys($this->Parameter['listNextListDOM']);
				$start=array_pop($start);
			}else{
				$start=0;
			}
			foreach($this->html->find($this->Parameter['listNextListDOM']) as $el){
				if($jk>=$start){
					$listNextListDOM[$jk]['href']=$el->firstChild()->href;
					$listNextListDOM[$jk]['name']=$el->text();
					if($jk==$start&&$bool==true){
						$this->checkUrlCo($pid,$listNextListDOM[$jk]['name']);
					}
				}
				$jk++;
			}
			unset($jk);
		}
		$listNext=$this->findDOM($listDOM);
		unset($listDOM);
		$this->html->clear();
		$this->html='';
		
		$this->debug(2,$listContents);
		if($listContents!=false){
			foreach($listContents as $k=>$v){
				//var_dump($this->startPoint[2]['url']!=$v['href']);
				//var_dump($this->startPoint[2]['url'],$v['href']);
				$url=str_replace($this->site,'',$this->startPoint[2]['url']);
				if($this->startPoint[2]!=false&&$url!=$v['href']){
					continue ;
				}
				$this->startPoint[2]=false;
				$this->saveMessage($this->onePage($this->site.$v['href']));	
			}
			$this->startPoint[2]=false;
		}
		$this->closeCol($pid);
		
		if(!empty($listNextListDOM)){
			foreach($listNextListDOM as $k=>$v){
				$this->colPageList($this->site.$v['href'],$classname,$v['name']);
			}
		}else{
			if($listNext!=false){
				foreach($listNext as $k=>$v){
						$this->colPageList($this->site.$v['href'],$classname,$v['name']);
				}
			}
		}
	}
	function onePage($url,$grade=2){
		sleep(8);
		$url=$this->handleUrl($url);
		$pid=$this->checkUrl($url,$grade);
		if(!$pid){ return ;}
		$contents=false;
		if(!empty($this->Parameter['chedren']['listDOM'])){
			$onepage=file_get_html($url);
			$html=$onepage->innertext;
			if(!empty($this->Parameter['chedren']['HtmlHook'])){
				
				if(function_exists($this->Parameter['chedren']['HtmlHook'])){
					$onepage->clear();
					$html=$this->Parameter['chedren']['HtmlHook']($html);
				}
				$onepage=str_get_html($html);
			}
			
				
			$contents=array();
			if($this->Parameter['savaHtml']==false){		
				$contents['html']=$html;
			}
			unset($html);
			/*
			$i=0;
			foreach($onepage->find($this->Parameter['chedren']['listDOM']) as $ele){
				$contents['contents'][$i]=$ele->innertext;
				$i++;
			}
			*/
			if(!empty($this->Parameter['chedren']['getFunction'])){
				if(function_exists($this->Parameter['chedren']['getFunction'])){
					$contentsT=$this->Parameter['chedren']['getFunction']($onepage,$this->db);
					if($grade==2){
						if($this->confTable==false){
							$contents['colvalue']=serialize($contentsT);
						}else{
							$contents=	array_merge($contentsT,$contents);
						}
					}else{
						$contents['colvalue']=$contentsT;
					}
				}
			}else{
				//die('处理采集详细页面的配置不正确！！!');
			}
			$contents['pid']=$pid;
			$contents['url']=$url;
			$contents['purl']=$url;
			$i=0;
			$contentsNext=array();
			
			if(!empty($this->Parameter['chedren']['listNextDOM'])){
				foreach($onepage->find($this->Parameter['chedren']['listNextDOM']) as $ele){
					$contentsNext[$i]['url']=$ele->href;
					$contentsNext[$i]['innertext']=$ele->innertext;
					$i++;
				}
				$contents['nextpage']=$contentsNext;
			}
			
			$onepage->clear();
			unset($i);
			unset($onepage);
			if(!empty($contentsNext)){
				foreach($contentsNext as $k=>$v){
						$contents['children']=$this->onePage($v['url'],3);
				}
				//$contents['children']=serialize($contents['children']);
				if($grade==3){
					return 	$contents;
				}else{
					$contents['children']=serialize($contents['children']);
				}
			}
		//var_dump($contents);
		}
	
		return $contents;
	}
	function handleUrl($url){
		$url=str_replace(' ','%20',$url);
		$url=str_replace('&#x27;',"'",$url);
		if(function_exists('handleUrl_function')){
				$url=handleUrl_function($url);
		}
		return $url;
	}
	/*
		$grade=1 ，2
		1代表为列表
		2代表为单条数据
	*/
	function checkUrl($url,$grade=1,$startBool=false,$classname='',$listname=''){
		if(empty($url)){return ;}
		$res=$this->db->exec_SELECTgetRows('id', $this->tables.'_pages', 'url="'.trim($url).'"','','id DESC','0,1');
		if(empty($res[0])){
			$fields_values['url']=$url;
			$fields_values['updated']='0';
			$fields_values['siteaddress']=$this->site;
			$fields_values['classname']=$classname;
			$fields_values['grade']=$grade;
			if($grade==1){$fields_values['listname']=$listname;}
			$res=$this->db->exec_INSERTquery($this->tables.'_pages', $fields_values);
		//	var_dump($this->db->INSERTquery($this->tables.'_pages', $fields_values));
			//'SELECT LAST_INSERT_ID()'
			$res1 = $this->db->sql_query('SELECT LAST_INSERT_ID()' );
			$indices_output='';
			if (is_resource($res1)) {
				while ($tempRow = $this->db->sql_fetch_assoc($res1)) {
					$indices_output		= array_pop($tempRow);
				}
				$this->db->sql_free_result($res1);
			}
			unset( $fields_values);
			return $indices_output;
		}else{
			if($startBool==false){if($res[0]['updated']==0){return $res[0]['id'];}}else{return $res[0]['id'];}
		}
	}
	function checkUrlCo($pid,$listname){
		if(empty($pid))	return ;
		$inFeild['updated']=1;
		$resa=$this->db->exec_UPDATEquery($this->tables.'_pages', 'id='.$pid,array('listname'=>$listname));
	}
	function setStartPoint(){
		$res1=$this->db->exec_SELECTgetRows('id,updated,url,grade,classname', $this->tables.'_pages','grade=1','','id DESC','0,1');
		$res2=$this->db->exec_SELECTgetRows('id,updated,grade,url', $this->tables.'_pages',"grade='2'",'','id DESC','0,1');
		if(!empty($res1[0]))
		$this->startPoint[1]=$res1[0];
		if(!empty($res2[0]))
		$this->startPoint[2]=$res2[0];
	}
	function saveMessage($insertFeild){
		$this->tables=trim($this->tables);
		$this->db->exec_INSERTquery($this->tables.'_contents', $insertFeild);
		$this->debug(3,$insertFeild);
		if(!empty($this->debugConfig)){	exit();}
	}
	function closeCol($pid){
		if(empty($pid))	return ;
		$inFeild['updated']=1;
		$resa=$this->db->exec_UPDATEquery($this->tables.'_pages', 'id='.$pid,$inFeild);
	}
	function saveCol($pid,$inFeild){
		if(empty($pid))	return ;
		$inFeild['updated']=1;
		$resa=$this->db->exec_UPDATEquery($this->tables.'_pages', 'id='.$pid,$inFeild);
	}
	function debug($i,$value){
		if(is_array($this->debugConfig)){
			if(in_array($i,$this->debugConfig)){
				var_dump($value);
			}
		}else{
			if($i==$this->debugConfig){
				var_dump($value);
			}
		}
	}
}

?>