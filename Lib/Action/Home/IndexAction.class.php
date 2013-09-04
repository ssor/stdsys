<?php

//class IndexAction extends Action{
class IndexAction extends LoginCommonAction
{
	//****************************************************************
	/*
	 *	修改订阅内容部分
	    interface 接口规范
		codeCollection 代码集
		class    数据子集
		rule     编码规则
	 */
	//****************************************************************
	public $UserName = "unknown";
	
	
	public function checkClientIE() {
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if (eregi("MSIE",$agent))
		{
			return true;
		}
		return false;
	}
	public function checkUTF8($str) {
		$cod = mb_check_encoding($str,"UTF-8");
		if("UTF-8" != $cod ||  empty($cod))
		{
			$str = mb_convert_encoding( $str,'utf-8','gb2312'); 
		}
		return $str;
	}
	public function checkWindows() {
		if(eregi('WIN',PHP_OS))
		{
			return true;
		}
		return false;
	}
	public function checkGB2312($str) {
		$cod = mb_check_encoding($str,"GB2312");
		if("GB2312" != $cod ||  empty($cod))
		{
			$str = mb_convert_encoding( $str,'GB2312','UTF-8'); 
		}
		return $str;
	}
	public function welcome()
	{
		$this->display();
	}
	
	public function InterfacesIndex()
	{
		if ($this->checkRole())
		{
			$M = new Model();  
			$sqlSelect="select FILE_NAME,UPLOAD_DATE,FILE_SIZE,AUTHOR from T_INTERFACES_FILE where JLZT='1'";
			$fileSelectList = $M->query($sqlSelect);
			$this->assign('fileList',$fileSelectList);
			$this->display();    
		}
	}
	public function downloadInterface()
	{
		$fileName = $_GET['fn'];// fn filename
		$fileNameSystemBased = $fileName;
		//$agent = $_SERVER['HTTP_USER_AGENT'];
		//如果客户端浏览器使用的是IE，则应该使用的windows系统，此时输出的文件名编码应该是gb2312
		if ($this->checkClientIE())
		{
			
			$fileNameSystemBased=$this->checkGB2312($fileNameSystemBased);
	
		}
		if ($this->checkWindows()) {
			//如果服务器系统为windows，需要查找文件时所用的文件名转成gb2312编码
			$fileName=$this->checkGB2312($fileName);
		}		
		else
		{
			//linux系统转成utf-8
			$fileName=$this->checkUTF8($fileName);
		}
		$path = C('INTERFACES_FILE_PATH').$fileName;
		if(!empty($path) and !is_null($path))
		{
			//$filename = basename($path);
			$file=@fopen($path,"r");
			if($file)
			{
				header("Content-type:application/octet-stream");
				//header("Content-type:application/vnd.ms-excel");
				header("Accept-ranges:bytes");
				header("Accept-length:".filesize($path));
				header("Content-Disposition:attachment;filename=".$fileNameSystemBased);
				echo fread($file,filesize($path));
				fclose($file);
				exit;
			}
			else
			{
				$this->assign('jumpUrl','/Index/InterfacesIndex');
				$this->success($filename."文件不存在,正在跳转 ...！");
			}	    	
		}
		else
		{
			$this->assign('jumpUrl','/Index/InterfacesIndex');
			$this->success($filename." 文件不存在,正在跳转 ...！");
			
		}
	}
	
	public function exportInfoClassData()
	{
		$cid = $_GET['cid'];
		
		if (empty($cid))
			return;
		$cid=$this->checkUTF8($cid);
		//echo $cid;
		//return;		
		$Class = new Model();
		// mysql version
		$sql = "SELECT IC.ICLASSID AS CID,ICN.VCLASSNAME AS CLASSNAME,
				IC.VID AS ID,IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
				IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
				IC.TCOMMENT AS CMT,IC.VREF AS REF
				FROM THINK_TBINFOCLASS IC,THINK_TBINFOCLASSNAME ICN
				WHERE IC.ICLASSID = '$cid' AND IC.ICLASSID = ICN.ICLASSID and IC.JLZT='1'";
		$list = $Class->query($sql);
		Vendor("PHPExcel.PHPExcel");
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '编号')->setCellValue('B1',
				'数据项名')->setCellValue('c1', '中文简称')->setCellValue('d1', '类型')->setCellValue('e1',
				'长度')->setCellValue('f1', '可选')->setCellValue('g1', '取值范围')->setCellValue('h1',
				'说明')->setCellValue('i1', '引用编号');
		
		$codeCollectionName = $list[0]['CLASSNAME'];
		for ($i = 0; $i < count($list); $i++)
		{
			$rowN = $i + 2;
			$id = $list[$i]['ID'];
			$name = $list[$i]['NAME'];
			$cName = $list[$i]['CNAME'];
			$vType = $list[$i]['VTYPE'];
			$len = $list[$i]['LEN'];
			$vSelect = $list[$i]['VSELECT'];
			$scope = $list[$i]['SCOPE'];
			$cmt = $list[$i]['CMT'];
			$ref = $list[$i]['REF'];
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowN", "$id")->
				setCellValue("b$rowN", $name)->setCellValue("c$rowN", $cName)->setCellValue("d$rowN",
					$vType)->setCellValue("e$rowN", $len)->setCellValue("f$rowN", $vSelect)->
				setCellValue("g$rowN", $scope)->setCellValue("h$rowN", $cmt)->setCellValue("i$rowN",
					$ref);
		}
		$objPHPExcel->getActiveSheet()->setTitle($codeCollectionName);
		
		$objPHPExcel->setActiveSheetIndex(0);
		if ($this->checkClientIE()) {
			$codeCollectionName =  mb_convert_encoding( $codeCollectionName,'gb2312','utf-8'); 
		}
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=$codeCollectionName.xls");
		//header('Content-Disposition: attachment;filename="ex.xls"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
		
	}
	public function exportCodeRulesData()
	{
		$Class = new Model();
		$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN,TCOMMENT AS CMT
				FROM THINK_TBCODERULES WHERE JLZT='1'";
		$list = $Class->query($sql);
		Vendor("PHPExcel.PHPExcel");
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '编号')->setCellValue('B1',
				'数据项名')->setCellValue('c1', '中文简称')->setCellValue('d1', '类型')->setCellValue('e1',
				'长度')->setCellValue('f1', '说明');
		$codeCollectionName = "基础数据编号规则";
		for ($i = 0; $i < count($list); $i++)
		{
			$rowN = $i + 2;
			$id = $list[$i]['RULEID'];
			$name = $list[$i]['NAME'];
			$cName = $list[$i]['CNAME'];
			$vType = $list[$i]['VTYPE'];
			$len = $list[$i]['LEN'];
			$cmt = $list[$i]['CMT'];
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowN", "$id")->
				setCellValue("b$rowN", $name)->setCellValue("c$rowN", $cName)->setCellValue("d$rowN",
					$vType)->setCellValue("e$rowN", $len)->setCellValue("f$rowN", $cmt);
			//log::write("exportData id=$id ");
		}
		$objPHPExcel->getActiveSheet()->setTitle($codeCollectionName);
		
		$objPHPExcel->setActiveSheetIndex(0);
		if ($this->checkClientIE()) {
			$codeCollectionName =  mb_convert_encoding( $codeCollectionName,'gb2312','utf-8'); 
		}
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=$codeCollectionName.xls");
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
	public function exportCodeCollectionData()
	{
		$Class = new Model();
		Vendor("PHPExcel.PHPExcel");
		$cid = $_GET['cid'];
		if (empty($cid))
		{
			//echo "1111";
			return;
		}
		$cid=$this->checkUTF8($cid);
		//echo $cid;
		$codeCollectionName = "";
		// mysql version
		$sql = "SELECT CC.COLLECTIONID AS CID
				,CCN.COLLECTIONNAME AS CNAME,
				ID, NAME,CODECOMMENT AS CMT
				FROM THINK_TBCODECOLLECTION CC,
				THINK_TBCODECOLLECTIONNAME CCN
				where CC.COLLECTIONID = '$cid' and
				CC.COLLECTIONID = CCN.COLLECTIONID and CC.JLZT='1'";
		//log::write("exportCodeCollectionData -> sql".$sql);
		$list = $Class->query($sql);
		//var_dump($list);
		//return;
		
		if (count($list) > 0)
		{
			$codeCollectionName = $list[0]['CNAME'];
			
			
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '代码')->setCellValue('B1',
					'名称')->setCellValue('c1', '描述')->setCellValue('d1', '所属代码集');
			for ($i = 0; $i < count($list); $i++)
			{
				$rowN = $i + 2;
				$id = $list[$i]['ID'];
				$name = $list[$i]['NAME'];
				$cmt = $list[$i]['CMT'];
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowN", "$id")->
					setCellValue("b$rowN", $name)->setCellValue("c$rowN", $cmt)->setCellValue("d$rowN",
						$codeCollectionName);
				//log::write("exportData id=$id ");
			}
			$objPHPExcel->getActiveSheet()->setTitle($codeCollectionName);
			if ($this->checkClientIE()) {
				$codeCollectionName =  mb_convert_encoding( $codeCollectionName,'gb2312','utf-8'); 
			}
			$objPHPExcel->setActiveSheetIndex(0);
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Disposition: attachment;filename=$codeCollectionName.xls");
			//header('Content-Disposition: attachment;filename="ex.xls"');
			header('Cache-Control: max-age=0');
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
			exit;
			
		} else
		{
			return;
		}
	}
	public function seachKeyWord()
	{
		if ($this->checkRole())
		{
			$style = $_GET['style'];
			$keyword = $_GET['kw'];
			if (empty($style))
			{
				$style = 1; //代码集搜索
			}
			switch ($style)
			{
				case 1:
					$Class = new Model();
					$sql = "SELECT CC.COLLECTIONID AS CID
							,CCN.COLLECTIONNAME AS CNAME,
							ID, NAME,CODECOMMENT AS CMT
							FROM THINK_TBCODECOLLECTION CC,
							THINK_TBCODECOLLECTIONNAME CCN
							WHERE CC.NAME like '%$keyword%' or
							CC.CODECOMMENT like '%$keyword%'
							group by CC.COLLECTIONID,ID
							,CCN.COLLECTIONNAME,NAME,CODECOMMENT
							";
					$list = $Class->query($sql);
					$this->assign('list', $list);
					$this->display('specialIndex');
					break;
				case 2:
					$Class = new Model();
					
					$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,
							NLENGTH AS LEN,TCOMMENT AS CMT
							FROM THINK_TBCODERULES
							where VID like '%$keyword%'
							or VNAME like '%$keyword%'
							or VNAMECHN like '%$keyword%'
							or TCOMMENT like '%$keyword%'";
					$list = $Class->query($sql);
					$this->assign('ruleList', $list);
					$this->display('ruleIndex');
					break;
				case 3:
					$Class =new Model();
					$sql = "SELECT IC.ICLASSID AS CID,ICN.VCLASSNAME AS CLASSNAME,
							IC.VID AS ID,IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
							IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
							IC.TCOMMENT AS CMT,IC.VREF AS REF
							FROM THINK_TBINFOCLASS IC,THINK_TBINFOCLASSNAME ICN
							WHERE IC.VID LIKE '%$keyword%'
							OR IC.VNAME LIKE '%$keyword%' OR IC.VNAMECHN LIKE '%$keyword%'
							OR IC.TCOMMENT LIKE '%$keyword%'
							GROUP BY IC.ICLASSID,IC.VID,
							ICN.VCLASSNAME,
							IC.VNAME,
							IC.VNAMECHN ,
							IC.VTYPE,
							IC.ILENGTH,
							IC.VSELECT,
							IC.VVALUESCOPE,
							IC.TCOMMENT,
							IC.VREF";
					$list = $Class->query($sql);
					$this->assign('list', $list);
					$this->display('InfoClassIndex');
					break;
				case 4:
					$M = new Model();
					$sql = "SELECT VTIME AS TM,VAUTHOR AS AUTHOR,TCONTENT AS CMT
							FROM THINK_TBEDITRECORD
							WHERE VAUTHOR like '%$keyword%' or  TCONTENT like '%$keyword%'
							order by TM desc";
					$logList = $M->query($sql);
					$this->assign('list', $logList);
					$this->display('ChangeLogIndex');
					break;
				case 5:
					$sql = "SELECT VCLASSNAME AS CLASSNAME, VITEMNAME AS NAME,
							VITEMCONTENT AS CONTENT,TCOMMENT AS CMT,EDITRECORDID 
							FROM THINK_TBCUSTOMITEMS
							where VITEMNAME like '%$keyword%' 
							or VITEMCONTENT like '%$keyword%'
							or TCOMMENT like '%$keyword%'";
					
					$M = new Model();
					$list = $M->query($sql);
					$this->assign('list', $list);
					$this->display('CustomClassItemIndex');
					break;
			}
		}
	}
	public function top()
	{
		$this->display();
	}
	public function main()
	{
		if ($this->checkRole())
		{
			$this->display();
		}
	}
	
	public function menu()
	{
		if ($this->checkRole())
		{
			$Class = new Model();
			$levelList = $Class->query("select distinct COLLECTIONLEVEL as CL from THINK_TBCODECOLLECTIONNAME where JLZT='1'");
			$this->assign('levelList', $levelList);
			$list = $Class->query("SELECT COLLECTIONID AS ID,COLLECTIONNAME AS NAME
						,COLLECTIONLEVEL AS CL FROM THINK_TBCODECOLLECTIONNAME WHERE JLZT='1'");
			$this->assign('codeCollectionlist', $list);
			
			//			$CodeRulesClass = new Model();
			//			$sql = "SELECT VID AS RULEID,VNAME AS NAME,FILE_NAME
			//					,TCOMMENT AS CMT 
			//					FROM THINK_TBCODERULES 
			//					WHERE JLZT='1'";
			//			$codeRulesList = $CodeRulesClass->query("SELECT VID AS ID,VNAME AS NAME,
			//						VNAMECHN AS SHOWNAME,VTYPE AS TP,NLENGTH AS LEN,TCOMMENT AS CMT
			//						FROM THINK_TBCODERULES WHERE JLZT='1'");
			//			$this->assign('rulesList', $codeRulesList);
			
			$InfoClassNameClass = new Model();
			$InfoClassNameLevelList = $InfoClassNameClass->query("SELECT DISTINCT VCLASSLEVEL AS CL
						FROM THINK_TBINFOCLASSNAME where JLZT='1'");
			$this->assign('InfoClassNamelevelList', $InfoClassNameLevelList);
			$InfoClassNameList = $InfoClassNameClass->query("SELECT ICLASSID AS CID,VCLASSNAME AS NAME
						,VCLASSLEVEL AS CL FROM THINK_TBINFOCLASSNAME where JLZT='1'");
			$this->assign('InfoClassNameList', $InfoClassNameList);
			
			$M = new Model();
			$CustomClassNameList = $M->query("SELECT ICLASSID as CID, VNAME AS NAME,VCLASSLEVEL AS CL,TCOMMENT AS CMT
						FROM THINK_TBCUSTOMCLASSES where JLZT='1'");
			$this->assign('CustomClassNamelist', $CustomClassNameList);
			
			
			$this->display();
		}
	}
	public function checkRole()
	{
		if ($_SESSION['logined'] != 1)
		{
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Welcome/welcome'";
			echo "</script>";
			return;
		}
		if ($_SESSION['ROLE_NAME'] != "viewer")
		{
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Welcome/welcome'";
			echo "</script>";
			return;
		}
		if ($this->UserName == "unknown")
		{
			
		}
		return true;
	}
	public function ruleIndex()
	{
		if ($this->checkRole())
		{
			$rid = $_GET['ruleID']; //collection id
			if (!empty($rid))
			{
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,FILE_NAME
						,TCOMMENT AS CMT,EDITRECORDID   
						FROM THINK_TBCODERULES 
						WHERE VID = '$rid'  and JLZT='1'";
			} else
			{
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,FILE_NAME
						,TCOMMENT AS CMT ,EDITRECORDID  
						FROM THINK_TBCODERULES 
						WHERE JLZT='1'";
			}
			$Class = new Model();
			
			$list = $Class->query($sql);
			$this->assign('ruleList', $list);
			$this->display();
		}
	}
	public function ruleDownload()
	{
		if ($this->checkRole())
		{
			$rid = $_GET['ruleID']; // rule id
			if (empty($rid))
			{
				$this->assign('jumpUrl','/Index/ruleIndex');
				$this->success('项目出错,正在跳转 ...！');
			} else
			{
				$CodeRuleClass = new Model();
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,FILE_NAME
						,TCOMMENT AS CMT ,EDITRECORDID  
						FROM THINK_TBCODERULES 
						WHERE VID = '$rid'  and JLZT='1'";
				$list = $CodeRuleClass->query($sql);
				if (count($list) <= 0)
				{
					$url = "/Admin/Index/menu";
					$this->assign('jumpUrl','/Index/ruleIndex');
					$this->success('该项不存在,正在跳转 ...！');
					return;
				}
				$fileName = $list[0]['FILE_NAME'];
				//echo $fileName;
				//return;
				$fileNameSystemBased = $fileName;
				//如果客户端浏览器使用的是IE，则应该使用的windows系统，此时输出的文件名编码应该是gb2312
				if ($this->checkClientIE())
				{
					
					$fileNameSystemBased=$this->checkGB2312($fileNameSystemBased);
					
				}
				if ($this->checkWindows()) {
					//如果服务器系统为windows，需要查找文件时所用的文件名转成gb2312编码
					$fileName=$this->checkGB2312($fileName);
				}		
				else
				{
					//linux系统转成utf-8
					$fileName=$this->checkUTF8($fileName);
				}
				$path = C('INTERFACES_FILE_PATH').$fileName;
				if(!empty($path) and !is_null($path))
				{
					//$filename = basename($path);
					$file=@fopen($path,"r");
					if($file)
					{
						header("Content-type:application/octet-stream");
						//header("Content-type:application/vnd.ms-excel");
						header("Accept-ranges:bytes");
						header("Accept-length:".filesize($path));
						header("Content-Disposition:attachment;filename=".$fileNameSystemBased);
						echo fread($file,filesize($path));
						fclose($file);
						exit;
					}
					else
					{
						$this->assign('jumpUrl','/Index/ruleIndex');
						$this->success($filename."文件不存在,正在跳转 ...！");
					}	    	
				}
				else
				{
					$this->assign('jumpUrl','/Index/ruleIndex');
					$this->success($filename." 文件不存在,正在跳转 ...！");
					
				}
			}
		}		
	}
	
	public function specialIndex()
	{
		if ($this->checkRole())
		{
			$cnt = 0;
			$cid = $_GET['cid']; //collection id
			
			if (!empty($cid))
			{
				$cid = $this->checkUTF8($cid);				
			} else
			{
				$M = new Model();
				$sql = "SELECT COLLECTIONID
						FROM THINK_TBCODECOLLECTIONNAME where JLZT='1'";
				$codeCollectionList = $M->query($sql);
				if(count($codeCollectionList)>0)
				{
					$cid = $codeCollectionList[0]['COLLECTIONID'];
					
				}else
				{
					$this->assign('jumpUrl','/Index/welcome');
					$this->success('尚未建立该代码集,正在跳转 ...！');
					return;
				}
			}
			$Class = new Model();
			$slqHierarchi = "SELECT ID,UPNODEID
					FROM THINK_TBCODECOLLECTION WHERE COLLECTIONID = '$cid'
					AND UPNODEID  IN (SELECT ID FROM THINK_TBCODECOLLECTION) and JLZT='1' ";
			$sql = "SELECT CC.COLLECTIONID AS CID
					,CCN.COLLECTIONNAME AS CNAME,
					ID, NAME,CODECOMMENT AS CMT,CC.EDITRECORDID,CC.UPNODEID  
					FROM THINK_TBCODECOLLECTION CC,
					THINK_TBCODECOLLECTIONNAME CCN
					WHERE CC.COLLECTIONID = '$cid' and
					CC.COLLECTIONID = CCN.COLLECTIONID  and CC.JLZT='1'";
			
			//$sql = "SELECT CC.COLLECTIONID AS CID ,CCN.COLLECTIONNAME AS CNAME, ID, NAME,CODECOMMENT AS CMT,CC.EDITRECORDID,CC.UPNODEID FROM THINK_TBCODECOLLECTION CC, THINK_TBCODECOLLECTIONNAME CCN WHERE CC.COLLECTIONID = '测试代码集1' and CC.COLLECTIONID = CCN.COLLECTIONID";
			//echo $sql;
			//return;
			$list = $Class->query($sql);	
			
			$hieList = $Class->query($slqHierarchi);
			
			if (count($hieList) > 0)
			{
				$this->assign('bHierarchi', 1); //有等级的数据
				
				Vendor("HierarchiIndex.HierarchicalItem");
				Vendor("HierarchiIndex.HierarchicalItemCollection");
				Vendor("HierarchiIndex.CodeCollectionlItem");
				$collection = new HierarchicalItemCollection();
				for ($i = 0; $i < count($list); $i++)
				{
					$vo = $list[$i];
					$item = new CodeCollectionlItem($vo['NAME'], $vo['ID'], $vo['UPNODEID'], $vo['CID'],
						$vo['CNAME'], $vo['CMT'], $vo['EDITRECORDID']);
					$collection->add($item);
				}
				$collection->startIndex();
				$rownums = $collection->getIndexedRowNumbers();
				$this->assign('maps', $rownums);
				$array = $collection->getIndexedArray();
				$this->assign('list', $array);
				$this->assign('cltInfo', $array[0]);
				//                $this->assign('list', $list);
				//                $this->assign('cltInfo', $list[0]);
				$this->display();
				
			} else
			{
				$this->assign('bHierarchi', 0);
				
				//var_dump($list);
				//var_dump($list);
				//return;
				$this->assign('list', $list);
				if (count($list) > 0)
				{               
					$this->assign('cltInfo', $list[0]);
				}
				else
				{
					$M = new Model();
					$sql = "select COLLECTIONNAME as CNAME,COLLECTIONID AS CID
							from THINK_TBCODECOLLECTIONNAME
							WHERE COLLECTIONID = '$cid'  and JLZT='1' ";
					$nameList = $M->query($sql);
					$this->assign('cltInfo', $nameList[0]);
					
				}
				$this->display();
				//                }
				//                 else
				//                {
				//                    $this->assign('list', $list);
				//                    $this->assign('cltInfo', $list[0]);
				//
				//                }
			}
			
			//$this->display();
		}
		
	}
	
	
	function CollectionNameIndex()
	{
		if ($this->checkRole())
		{
			$Class = new Model();
			$sql = "SELECT COLLECTIONID AS CID,COLLECTIONNAME AS NAME,
					COLLECTIONCOMMENT AS CMT,COLLECTIONLEVEL AS CL
					FROM THINK_TBCODECOLLECTIONNAME WHERE JLZT='1'";
			$list = $Class->query($sql);
			$this->assign('codeCollectionNameList', $list);
			$this->display();
		}
		
	}
	
	
	public function InfoClassIndex()
	{
		if ($this->checkRole())
		{
			$cnt = 0;
			$icid = $_GET['icid']; //collection id
			
			if (!empty($icid))
			{
				$icid=$this->checkUTF8($icid);			
			} else
			{
				//如果传进来的icid为为空，则找一个现有icid跳转过去
				$M = new Model();
	
				$sql = "SELECT ICLASSID,VCLASSNAME  
						FROM THINK_TBINFOCLASSNAME where JLZT='1'";
				
				$classCidList = $M->query($sql);
				if(count($classCidList)>0)
				{
					$icid = $classCidList[0]['ICLASSID'];
				}else
				{
					$this->assign('jumpUrl','/Index/welcome');
					$this->success('尚未建立任何子集,正在跳转 ...！');
					return;
				}
			}
			$Class = new Model();
			$sql = "SELECT IC.ICLASSID AS CID,ICN.VCLASSNAME AS CLASSNAME,
					IC.VID AS ID,IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
					IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
					IC.TCOMMENT AS CMT,IC.VREF AS REF,IC.EDITRECORDID 
					FROM THINK_TBINFOCLASS IC,THINK_TBINFOCLASSNAME ICN
					WHERE IC.ICLASSID = '$icid' and IC.ICLASSID = ICN.ICLASSID  and IC.JLZT='1'";
			$list = $Class->query($sql);
			$className = "";
			if (count($list) > 0)
			{
				$className = $list[0]['CLASSNAME'];
				$this->assign('list', $list);
				$this->assign('InfoClass', $list[0]);
				$this->assign('className', $className);
				$this->assign('icid', $icid);
				$this->display();
			} else
			{
				$sql =  "SELECT ICLASSID,VCLASSNAME
						FROM THINK_TBINFOCLASSNAME WHERE ICLASSID = '$icid'  and JLZT='1'";
				$nameList=	$Class->query($sql);
				if (count($nameList)>0) {
					$className=$nameList[0]['VCLASSNAME'];
				}
				$this->assign('list', $list);
				$this->assign('InfoClass', $list[0]);
				$this->assign('icid', $icid);
				$this->assign('className', $className);
				$this->display();
			}
		}
	}
	
	public function InfoClassNameIndex()
	{
		if ($this->checkRole())
		{
			$InfoClassNameClass = new Model();
			$InfoClassNameList = $InfoClassNameClass->query(
					"SELECT ICLASSID AS CID,VCLASSNAME AS NAME,VCLASSLEVEL AS CL,TCOMMENT AS CMT
						FROM THINK_TBINFOCLASSNAME where JLZT='1'");
			$this->assign('InfoClassNameList', $InfoClassNameList);
			$this->display();
		}
	}
	public function ChangeLogIndex()
	{
		//log::write("ChangeLogIndex->");
		if ($this->checkRole())
		{
			$logID = $_GET['id'];
			$M = new Model();
			if (empty($logID))
			{
				//$sql = "SELECT VTIME AS TM,VAUTHOR AS AUTHOR,TCONTENT AS CMT
				//	FROM THINK_TBEDITRECORD WHERE EDITID = 'nothing' ORDER BY TM DESC";
				$sql = "SELECT VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID
						FROM THINK_TBCHANGERECORD WHERE EDITID = 'nothing'  and JLZT='1' ORDER BY VTIME DESC";
			} else
				if ($logID == "all")
				{
					//$sql = "SELECT VTIME AS TM,VAUTHOR AS AUTHOR,TCONTENT AS CMT
					//FROM THINK_TBEDITRECORD ORDER BY TM DESC";
					$sql = "SELECT VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID
							FROM THINK_TBCHANGERECORD WHERE  JLZT='1' ORDER BY VTIME DESC";
				} else
				{
					$sql = "SELECT VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID
							FROM THINK_TBCHANGERECORD WHERE EDITID = '$logID'  and JLZT='1' ORDER BY VTIME DESC";
					//$sql = "SELECT VTIME AS TM,VAUTHOR AS AUTHOR,TCONTENT AS CMT
					//FROM THINK_TBEDITRECORD WHERE EDITID = '" . $logID . "' ORDER by TM desc";
				}
			$logList = $M->query($sql);
			$this->assign('list', $logList);
			$this->display();
		}
	}
	
	public function CustomClassItemIndex()
	{
		if ($this->checkRole())
		{
			$cid = $_GET['cid'];
			if (empty($cid))
			{
				$M = new Model();
				$sql = "select ICLASSID from THINK_TBCUSTOMCLASSES WHERE  JLZT='1'";
				$list = $M->query($sql);
				if (count($list)>0) {
					$cid=$list[0]['ICLASSID'];
				}
				else
				{
				$url = "/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('不存在该类,正在跳转 ...！');
				return;
				}
			}
			$cid=$this->checkUTF8($cid);
			$sql = "SELECT class.ICLASSID as CID,class.VNAME AS CLASSNAME,VITEMID as ID, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
					,items.TCOMMENT AS CMT,EDITRECORDID 
					FROM THINK_TBCUSTOMITEMS items,THINK_TBCUSTOMCLASSES class
					WHERE items.ICLASSID=class.ICLASSID and items.ICLASSID = '$cid'  and items.JLZT='1'";
			
			//log::write("CustomClassItemIndex -> " . $sql);
			$M = new Model();
			$list = $M->query($sql);
			if (count($list)>0) {
				$this->assign('itemName', $list[0]['CLASSNAME']);
				$this->assign('cid', $list[0]['CID']);
			}
			else
			{
				$sql = "select ICLASSID as CID,  VNAME as NAME from THINK_TBCUSTOMCLASSES where ICLASSID = '$cid'  and JLZT='1'";
				$nameList = $M->query($sql);
				$this->assign('itemName', $nameList[0]['NAME']);
				$this->assign('cid', $nameList[0]['CID']);
			}
			$this->assign('list', $list);
			$this->display();
		}
	}
	public function CustomClassIndex()
	{
		if ($this->checkRole())
		{
			//log::write("CustomClassIndex -> ");
			
			$M = new Model();
			$CustomClassNameList = $M->query("SELECT ICLASSID as CID, VNAME AS NAME,VCLASSLEVEL AS CL,TCOMMENT AS CMT
						FROM THINK_TBCUSTOMCLASSES WHERE  JLZT='1'");
			$this->assign('list', $CustomClassNameList);
			$this->display();
		}
	}
	
	
}

?>
