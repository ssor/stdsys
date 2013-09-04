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
	public function executeSql($sql) {
		$state=true;
		if (!empty($sql)) {
			
			//***************************************
			//  将修改记录注释 oracle数据时使用这种sql写法
			if (C('DB_TYPE') == 'oracle') {
				$sql="begin ".$sql." end;";	
				$M = new Model(); 
				if (!$M->execute($sql))
				{
					$state = false;
				}
			}
			
			//TODO oracle
			////////////////////////////////////////	
			
			////////////////////////////////////////
			//******************************************
			// sqlite使用不同的方法执行
			if (C('DB_TYPE') == 'pdo') {
				if (!empty($sql)) {
					$sqlArray = explode(';',$sql);
					for($i=0;$i<count($sqlArray)-1;$i++)
					{
						$ConfigM = new Model();
						if (!$ConfigM->execute($sqlArray[$i])) {
							$state=false;
							break;
						}
					}
				}					
			}
			//******************************************		
		}
		
		return $state;
	}
	/*
	 *	关于文件上传下载的约定：
		在windows系统下，文件名一律转成gb2312编码；
		在linux系统下，文件名一律转成utf-8编码。
		
	 */
	public function InterfaceDelete()
	{
		$fileNames = $_GET['fn'];// fn filename
		if (empty($fileNames)) {
			$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
			$this->success("请选择要删除的文件,正在跳转 ...！");
			return;
		}
		$fileNameA=explode('?',$fileNames);
		$state=true;
		date_default_timezone_set("Asia/Shanghai");
		$vTime = date("Y-m-d H:i:s");
		$author = $_SESSION['nickname'];
		$sqlInsert="";
		for($i=0;$i<count($fileNameA);$i++)
		{
			$fileName=$fileNameA[$i];
			$fileNameSystemBased=$fileName;//用于在本地查找文件
			if($this->checkWindows())
			{
				$fileNameSystemBased=$this->checkGB2312($fileNameSystemBased);
				
			}
			else
			{
				$fileNameSystemBased=$this->checkUTF8($fileNameSystemBased);
			}
			$fileName=$this->checkUTF8($fileName);
			$path = C('INTERFACES_FILE_PATH').$fileNameSystemBased;
			//$sqlInsert="delete from T_INTERFACES_FILE where FILE_NAME='$fileName';";
			if (C('DB_TYPE') == 'oracle') {
				$sqlInsert="update T_INTERFACES_FILE set JLZT='0',OPERATERTIME=sysdate where FILE_NAME='$fileName';";
			}
			else
			{
				$sqlInsert="update T_INTERFACES_FILE set JLZT='0',OPERATERTIME=date('now') where FILE_NAME='$fileName';";
				
			}
			
			$content = "删除了接口规范 $fileName ";
			$sqlInsert .= "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
					values('$vTime','$author','$content','interface'); ";
			
			if ( !unlink($path) || !$this->executeSql($sqlInsert) ) {
				$state=false;
				break;
			}
		}
		
		if ($state) {
			
			//************************************************************
			//$M = new Model();
			
			//$M->execute($sqlInsert);
			//************************************************************
			
			$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
			$this->success("文件删除成功,正在跳转 ...！");
			return;
		}
		else 
		{
			$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
			$this->success("文件删除失败,正在跳转 ...！");
			return;
		}			
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
				$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
				$this->success($filename."文件不存在,正在跳转 ...！");
			}	    	
		}
		else
		{
			$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
			$this->success($filename." 文件不存在,正在跳转 ...！");
			
		}
	}
	public function uploadInterface()
	{
		//echo C('INTERFACES_FILE_PATH');
		//return;
		
		//var_dump($_FILES);return;
		$filename =$_FILES["file"]["name"];
		$filename=$this->checkUTF8($filename);
		$M = new Model();  
		$sqlSelect="select FILE_NAME  from T_INTERFACES_FILE where FILE_NAME='$filename'  and JLZT='1'";
		//echo $sqlSelect;
		//return;
		$fileSelectList = $M->query($sqlSelect);
		//log::write($filename." uploadInterface ".count($fileSelectList) );
		if (count($fileSelectList)>0) {
			$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
			$this->success("文件已经上传，如果需要更新，请先删除之前的文件,正在跳转 ...！");
			return;
		}
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		//$upload->maxSize = 3292200000;
		//设置附件上传目录
		$upload->savePath = C('INTERFACES_FILE_PATH');
		//设置上传文件规则
		//if(eregi(“WIN”,PHP_OS))
		if (!$upload->upload()) {
			//捕获上传异常
			//log::write('_upload error' );
			$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
			$this->success("发生错误：". $upload->getErrorMsg()."。上传 失败,正在跳转 ...！");
			//$this->error($upload->getErrorMsg());
		} else
		{
			//			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			$filename = $uploadList[0]['savename'];
			
			date_default_timezone_set("Asia/Shanghai");
			$vTime = date("Y-m-d H:i:s");
			$author = $_SESSION['nickname'];
			$operaterid= $_SESSION['acount'];
			$size = $uploadList[0]['size']/1000;
			//检查是否有删除记录，如果有删除记录则直接恢复更新之前的记录
			$sqlInsertNewFile ="";
			{
				$MCheck =new Model();
				$sqlCheck = "select FILE_NAME from T_INTERFACES_FILE where FILE_NAME ='$filename'";
				$checkResult=$MCheck->query($sqlCheck);
				if (count($checkResult)>0) {
					if (C('DB_TYPE') == 'oracle') {
						$sqlInsertNewFile=
							"update T_INTERFACES_FILE set JLZT='1',UPLOAD_DATE ='$vTime'
								,FILE_SIZE='$size',AUTHOR='$author',OPERATERID='$operaterid',OPERATERTIME=sysdate  where FILE_NAME='$filename';";						
					}
					else
					{
						$sqlInsertNewFile=
							"update T_INTERFACES_FILE set JLZT='1',UPLOAD_DATE ='$vTime'
								,FILE_SIZE='$size',AUTHOR='$author',OPERATERID='$operaterid',OPERATERTIME=date('now')  where FILE_NAME='$filename';";							
					}

				}
				else {

					if (C('DB_TYPE') == 'oracle') {
						$sqlInsertNewFile =
							"insert into T_INTERFACES_FILE(FILE_NAME,UPLOAD_DATE,FILE_SIZE,AUTHOR,OPERATERID,OPERATERTIME)
								values('$filename','$vTime','$size','$author','$operaterid',sysdate)";						
					}
					else
					{
						$sqlInsertNewFile =
							"insert into T_INTERFACES_FILE(FILE_NAME,UPLOAD_DATE,FILE_SIZE,AUTHOR,OPERATERID,OPERATERTIME)
								values('$filename','$vTime','$size','$author','$operaterid',date('now'))";							
					}
				}
			}
			
			if($M->execute($sqlInsertNewFile))
			{
				//************************************************************
				$M = new Model();
				date_default_timezone_set("Asia/Shanghai");
				$content = "上传了接口规范 $filename ";
				$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
						values('$vTime','$author','$content','interface') ";
				$M->execute($sqlInsert);
				//************************************************************
				
				$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
				$this->success("上传  ".$filename." 大小 ".$size."Kb  成功,正在跳转 ...！");				
			}
			else
			{	
				$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
				$this->success("上传失败,正在跳转 ...！");	
				
			}
			
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
	public function importCodeCollectionData()
	{
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
			
			$CodeRulesClass = new Model();
			$codeRulesList = $CodeRulesClass->query("SELECT VID AS ID,VNAME AS NAME,
						VNAMECHN AS SHOWNAME,VTYPE AS TP,NLENGTH AS LEN,TCOMMENT AS CMT
						FROM THINK_TBCODERULES WHERE JLZT='1'");
			$this->assign('rulesList', $codeRulesList);
			
			$InfoClassNameClass = new Model();
			$InfoClassNameLevelList = $InfoClassNameClass->query("SELECT DISTINCT VCLASSLEVEL AS CL
						FROM THINK_TBINFOCLASSNAME where JLZT='1'");
			$this->assign('InfoClassNamelevelList', $InfoClassNameLevelList);
			$InfoClassNameList = $InfoClassNameClass->query("SELECT ICLASSID AS CID,VCLASSNAME AS NAME
						,VCLASSLEVEL AS CL FROM THINK_TBINFOCLASSNAME where JLZT='1'");
			$this->assign('InfoClassNameList', $InfoClassNameList);
			
			$M = new Model();
			$CustomClassNameList = $M->query("SELECT ICLASSID as CID, VNAME AS NAME,VCLASSLEVEL AS LEVEL,TCOMMENT AS CMT
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
		if ($_SESSION['ROLE_NAME'] != "editor")
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
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT,EDITRECORDID 
						FROM THINK_TBCODERULES WHERE VID = '$rid'  and JLZT='1'";
			} else
			{
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT,EDITRECORDID 
						FROM THINK_TBCODERULES WHERE JLZT='1'";
				//$this->redirect('Admin-Index/menu', null, 1, '不存在该编码规则！');
				//return;
			}
			$Class = new Model();
			
			$list = $Class->query($sql);
			$this->assign('ruleList', $list);
			$this->display();
			// if (count($list) > 0)
			// {
			// $this->assign('ruleList', $list);
			// $this->display();
			// } else
			// {
			// $this->redirect('Admin-Index/menu', null, 1, '不存在该编码规则！');
			// }
		}
	}
	public function ruleEdit()
	{
		if ($this->checkRole())
		{
			$rid = $_GET['ruleID']; // rule id
			if (empty($rid))
			{
				$this->assign('jumpUrl','/Admin/Index/ruleIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
			} else
			{
				$CodeRuleClass = new Model();
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT  FROM THINK_TBCODERULES WHERE VID = '$rid' and JLZT='1'";
				$list = $CodeRuleClass->query($sql);
				if (count($list) <= 0)
				{
					$url = "/Admin/Index/menu";
					$this->assign('jumpUrl','/Admin/Index/ruleIndex');
					$this->success('编辑项不存在,正在跳转 ...！');
					//$this->redirect($url, null, 1, '编辑项不存在！');
					return;
				}
				$this->assign('CodeRuleClass', $list[0]);
				$this->display();
			}
		}
	}
	public function ruleUpdate()
	{
		if ($this->checkRole())
		{
			$rid = trim($_POST['rid']);
			$name = trim($_POST['name']);
			$cName = trim($_POST['cName']);
			$type = trim($_POST['vType']);
			$len = trim($_POST['len']);
			$comment = trim($_POST['comment']);
			if (empty($rid))
			{
				$this->assign('jumpUrl','/Admin/Index/ruleIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
			} else
			{
				if (empty($len)) {
					$len=0;
				}
				$CodeRuleClass = new Model();
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT,EDITRECORDID 
						FROM THINK_TBCODERULES WHERE VID = '$rid' and JLZT='1'";
				$list = $CodeRuleClass->query($sql);
				if (count($list) <= 0)
				{
					$url = '/Admin/Index/menu';
					$this->assign('jumpUrl',"/Admin/Index/ruleEdit/ruleID/".$rid);
					$this->success('编辑项不存在,正在跳转 ...！');
					//$this->redirect($url, null, 1, '编辑项不存在！');
					return;
				}
				/*
				*	产生修改记录
				*/
				//默认值
				$sqlUpdate = "update THINK_TBCODERULES set VNAME='$name',VNAMECHN = '$cName',
						VTYPE = '$type',NLENGTH = $len,TCOMMENT = '$comment'
						where VID = '$rid'";
				$uniqueID = 0;
				$sqlInsert = "";
				
				if (empty($list[0]['editRecordID']))
				{ // empty可以用来测试返回值是否为空
					//log::write("update() -> editRecordID is null");
					// 如果尚未有修改记录的编号
					$uniqueID = $this->GetUniqueID();
					$sqlUpdate = "update THINK_TBCODERULES set VNAME='$name',VNAMECHN = '$cName',
							VTYPE = '$type',NLENGTH = $len,TCOMMENT = '$comment',
							EDITRECORDID = '$uniqueID' 
							where VID = '$rid'";
					
				} else
				{
					$uniqueID = $list[0]['EDITRECORDID'];
				}
				//插入修改记录
				$changeLog = "";
				$bHasChanged = false;
				date_default_timezone_set("Asia/Shanghai");
				$vTime = date("Y-m-d H:i:s");
				$author = $_SESSION['nickname'];
				
				$originalCollection = $list[0];
				if ($name != $originalCollection['NAME'])
				{
					$bHasChanged = true;
					$oName = $originalCollection['NAME'];
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','编码规则','$rid','更新',
							'数据项名','$oName','$name','$uniqueID') ;";
					//$changeLog = $changeLog . " 数据项名 由 " . $oName . "  改为 " . $name;
				}
				
				if ($cName != $originalCollection['CNAME'])
				{
					$bHasChanged = true;
					$ocName = $originalCollection['CNAME'];
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','编码规则','$rid','更新',
							'中文简称','$ocName','$cName','$uniqueID');";
					//$changeLog = $changeLog . " 中文简称 由 " . $ocName . "  改为 " . $cName;
				}
				
				if ($type != $originalCollection['VTYPE'])
				{
					$bHasChanged = true;
					$otype = $originalCollection['VTYPE'];
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','编码规则','$rid','更新',
							'类型','$otype','$type','$uniqueID'); ";
					//$changeLog = $changeLog . " 类型 由 " . $otype . "  改为 " . $type;
				}
				if ($len != $originalCollection['LEN'])
				{
					$bHasChanged = true;
					$olen = $originalCollection['LEN'];
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','编码规则','$rid','更新',
							'长度','$olen','$len','$uniqueID'); ";
					//$changeLog = $changeLog . " 长度 由 " . $olen . "  改为 " . $len;
				}
				if ($comment != $originalCollection['CMT'])
				{
					$bHasChanged = true;
					$oComment = $originalCollection['CMT'];
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','编码规则','$rid','更新',
							'说明','$oComment','$comment','$uniqueID'); ";
					//$changeLog = $changeLog . " 说明 由 " . $oComment . "  改为 " . $comment;
				}
				
				//if ($changeLog != "")
				if($bHasChanged)
				{
					date_default_timezone_set("Asia/Shanghai");
					$vTime = date("Y-m-d H:i:s");
					$state=true;
					
					if (!empty($sqlInsert)) {
						$state =$this->executeSql($sqlInsert);
					}
					if(!$state)
					//if (!$M->execute($sqlInsert))
					{
						$url = "/Admin/Index/ruleIndex/ruleID/$rid";
						$this->assign('jumpUrl',"/Admin/Index/ruleIndex/ruleID/$rid");
						$this->success('111保存失败,正在跳转 ...！');
						//$this->redirect($url, null, 1, '保存失败！');
					}
				}
				
				if ($CodeRuleClass->execute($sqlUpdate))
				{
					
					$MO = new Model();
					
					date_default_timezone_set("Asia/Shanghai");
					$vTime = date("Y-m-d H:i:s");
					$author = $_SESSION['nickname'];
					$content = "更新了编号为 $rid 的编码规则的内容";
					$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
							values('$vTime','$author','$content','rule') ";
					$MO->execute($sqlInsert);
					
					
					$url = "/Admin/Index/ruleIndex/ruleID/$rid";
					$this->assign('jumpUrl',"/Admin/Index/ruleIndex");
					$this->success('已保存更改,正在跳转 ...！');
					//$this->redirect($url, null, 1, '已保存更改！');
				} else
				{
					$url = "Admin-Index/ruleIndex/ruleID/$rid";
					$this->assign('jumpUrl',"/Admin/Index/ruleIndex");
					$this->success('保存失败,正在跳转 ...！');
					//$this->redirect($url, null, 1, '保存失败！');
				}
			}
		}
	}
	public function ruleDelete()
	{
		if ($this->checkRole())
		{
			$rids = $_GET['ruleID']; // code id
			if (empty($rids))
			{
				$this->assign('jumpUrl','/Admin/Index/ruleIndex');
				$this->success('删除项目出错,正在跳转 ...！');				
				//$this->redirect('Admin-Index/menu', null, 1, '删除项目出错！');
			} else
			{
				$rids=$this->checkUTF8($rids);
				$ridA=explode('?',$rids);
				$sqlExcute="";
				
				$operaterid= $_SESSION['acount'];
				for($i=0;$i<count($ridA);$i++)
				{
					$rid=$ridA[$i];
					//$sqlExcute .="DELETE FROM THINK_TBCODERULES  WHERE VID = '$rid';";
					if (C('DB_TYPE') == 'oracle') {
						$sqlExcute .="update  THINK_TBCODERULES set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'  WHERE VID = '$rid';";
					}
					else
					{
						$sqlExcute .="update  THINK_TBCODERULES set JLZT='0',OPERATERTIME=date('now'),OPERATERID='$operaterid'  WHERE VID = '$rid';";
						
					}
				}
				
				
				
				$state = true;
				
				if (!empty($sqlExcute)) {
					$state=$this->executeSql($sqlExcute);
				}
				if($state)			
				{
					$M = new Model();
					
					date_default_timezone_set("Asia/Shanghai");
					$vTime = date("Y-m-d H:i:s");
					$author = $_SESSION['nickname'];
					$content = "删除了编号为 $rid 的编码规则";
					$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
							values('$vTime','$author','$content','rule') ";
					$M->execute($sqlInsert);
					
					
					$this->assign('jumpUrl','/Admin/Index/ruleIndex');
					$this->success('删除成功,正在跳转 ...！');					
				}
				else
				{
					$this->assign('jumpUrl','/Admin/Index/ruleIndex');
					$this->success('发生异常，删除失败,正在跳转 ...！');	
				}
				
			}
		}
	}
	public function ruleAdd()
	{
		if ($this->checkRole())
		{
			$this->display();
		}
	}
	public function ruleInsert()
	{
		if ($this->checkRole())
		{
			$rid = trim($_POST['rid']);
			$name = trim($_POST['name']);
			$cName = trim($_POST['cName']);
			$type = trim($_POST['vType']);
			$len = trim($_POST['len']);
			$comment = trim($_POST['comment']);
			//log::write("update cid=$cid id = $id name = $name");
			if (empty($rid))
			{
				$this->assign('jumpUrl','/Admin/Index/ruleIndex');
				$this->success('添加项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '添加项目出错！');
			} else
			{
				if (empty($len)) {
					$len=0;
				}
				$CodeRuleClass = new Model();
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT
						FROM THINK_TBCODERULES WHERE VID = '$rid'  and JLZT='1'";
				$list = $CodeRuleClass->query($sql);
				if (count($list) > 0)
				{
					$url = 'Admin-Index/ruleIndex';
					$this->assign('jumpUrl','/Admin/Index/ruleIndex');
					$this->success('该项已存在,正在跳转 ...！');                  
					//$this->redirect($url, null, 1, '该项已存在！');
					return;
				}
				$operaterid= $_SESSION['acount'];
				//check exists
				$sqlInsert ="";
				{
					$MCheck =new Model();
					$sqlCheck = "select VID from THINK_TBCODERULES where VID ='$rid'";
					$checkResult=$MCheck->query($sqlCheck);
					if (count($checkResult)>0) {
						
						if (C('DB_TYPE') == 'oracle') {
							$sqlInsert="update THINK_TBCODERULES set JLZT='1',VNAME='$name',VNAMECHN='$cName',VTYPE='$type',NLENGTH='$len',TCOMMENT='$comment' 
									,OPERATERID='$operaterid',OPERATERTIME=sysdate  where VID = '$rid'" ;							
						}
						else
						{
							$sqlInsert="update THINK_TBCODERULES set JLZT='1',VNAME='$name',VNAMECHN='$cName',VTYPE='$type',NLENGTH='$len',TCOMMENT='$comment' 
									,OPERATERID='$operaterid',OPERATERTIME=date('now')  where VID = '$rid'" ;							
						}

					}
					else{
						if (C('DB_TYPE') == 'oracle') {
							$sqlInsert = "INSERT INTO THINK_TBCODERULES ( VID,VNAME,VNAMECHN,
									VTYPE ,NLENGTH ,TCOMMENT,OPERATERID,OPERATERTIME)
									values('$rid','$name','$cName', '$type', $len, '$comment','$operaterid',sysdate)";							
						}
						else
						{
							$sqlInsert = "INSERT INTO THINK_TBCODERULES ( VID,VNAME,VNAMECHN,
									VTYPE ,NLENGTH ,TCOMMENT,OPERATERID,OPERATERTIME)
									values('$rid','$name','$cName', '$type', $len, '$comment','$operaterid',date('now'))";							
						}

					}
				}
				//echo $sqlInsert;
				//return;
				
				if ($CodeRuleClass->execute($sqlInsert))
				{
					$M = new Model();
					
					date_default_timezone_set("Asia/Shanghai");
					$vTime = date("Y-m-d H:i:s");
					$author = $_SESSION['nickname'];
					$content = "增加了编号为 $rid 的编码规则";
					$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
							values('$vTime','$author','$content','rule') ";
					$M->execute($sqlInsert);
					
					
					$url = "Admin-Index/ruleIndex";
					$this->assign('jumpUrl','/Admin/Index/ruleIndex');
					$this->success('添加成功,正在跳转 ...！');                  
				} else
				{
					$url = "Admin-Index/ruleIndex";
					$this->assign('jumpUrl','/Admin/Index/ruleIndex');
					$this->success('添加失败,正在跳转 ...！');  
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
				//var_dump($codeCollectionList);
				//return;
				if(count($codeCollectionList)>0)
				{
					$cid = $codeCollectionList[0]['COLLECTIONID'];
					
				}else
				{
					$this->assign('jumpUrl','/Admin/Index/welcome');
					$this->success('尚未建立该代码集,正在跳转 ...！');
					return;
				}
			}
			$Class = new Model();
			$slqHierarchi = "SELECT ID,UPNODEID
					FROM THINK_TBCODECOLLECTION WHERE COLLECTIONID = '$cid'
					AND UPNODEID  IN (SELECT ID FROM THINK_TBCODECOLLECTION) and JLZT='1' ";
			//echo $slqHierarchi;
			//return;
			
			
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
			//var_dump($list);
			//return;
			
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
				//echo	$rownums."11111";
				//return;
				
				$this->assign('maps', $rownums);
				$array = $collection->getIndexedArray();
				//var_dump($array);
				//return;
				
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
			}
		}
		
	}
	public function specialIndexPara($tbln)
	{
		$Class = new Model();
		$condition['table_name'] = $tbln;
		$list = $Class->where($condition)->select();
		$this->assign('list', $list);
		$this->assign('tbn', $tbln);
		$this->display();
	}
	function CollectionNameDelete()
	{
		if ($this->checkRole())
		{
			$cids = $_GET['cid'];
			if (empty($cids))
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('代码集不存在,正在跳转 ...！');
				return;
			}
			$cids=$this->checkUTF8($cids);
			
			//echo $cids;
			//return;
			$cidA = explode('?',$cids);
			$sqlExcute="";
			$operaterid= $_SESSION['acount'];
			
			for($i=0;$i<count($cidA);$i++)
			{
				$cid=$cidA[$i];
				/*
				$sqlExcute.="DELETE FROM THINK_TBCODECOLLECTIONNAME WHERE COLLECTIONID = '$cid';";
				$sqlExcute.= "delete from THINK_TBCODECOLLECTION where COLLECTIONID = '$cid';";
			*/
				if (C('DB_TYPE') == 'oracle') {
					$sqlExcute.="update THINK_TBCODECOLLECTIONNAME set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'  WHERE COLLECTIONID = '$cid';";
					$sqlExcute.="update THINK_TBCODECOLLECTION set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'  WHERE COLLECTIONID = '$cid';";					
				}
				else
				{
					$sqlExcute.="update THINK_TBCODECOLLECTIONNAME set JLZT='0',OPERATERTIME=date('now'),OPERATERID='$operaterid'  WHERE COLLECTIONID = '$cid';";
					$sqlExcute.="update THINK_TBCODECOLLECTION set JLZT='0',OPERATERTIME=date('now'),OPERATERID='$operaterid'  WHERE COLLECTIONID = '$cid';";					
				}

			}
			
			//echo $sqlExcute;
			//return;
			$state = true;
			
			if (!empty($sqlExcute)) {
				$state=$this->executeSql($sqlExcute);
			}
			if ($state)
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('删除成功,正在跳转 ...！');
				//$this->redirect('Admin-Index/CollectionNameEdit', null, 1, '删除成功！正在跳转...');
			} else
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('删除失败,正在跳转 ...！');
				//$this->redirect('Admin-Index/CollectionNameEdit', null, 1, '删除失败！正在跳转...');
			}
		}
	}
	function CollectionNameInsert()
	{
		if ($this->checkRole())
		{
			$cid = trim($_POST['cid']);
			$name = trim($_POST['name']);
			$cl = trim($_POST['level']);
			$cmt = trim($_POST['comment']);
			
			if (empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('编号未填写,正在跳转 ...！');
				//$this->redirect('Admin-Index/CollectionNameEdit', null, 1, '编号未填写！');
				return;
			}
			$sqlSelect = "SELECT COLLECTIONID FROM THINK_TBCODECOLLECTIONNAME WHERE COLLECTIONID = '$cid' and JLZT='1' ";
			$InfoCollectionNameClass = new Model();
			$list = $InfoCollectionNameClass->query($sqlSelect);
			if (count($list) > 0)
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('编号已存在,正在跳转 ...！');
				//$this->redirect('Admin-Index/CollectionNameEdit', null, 1, '编号已存在！');
				return;
			}
			$operaterid= $_SESSION['acount'];
			$sqlInsert ="";
			{
				$MCheck =new Model();
				$sqlCheck = "SELECT COLLECTIONID FROM THINK_TBCODECOLLECTIONNAME WHERE COLLECTIONID = '$cid'";
				$checkResult=$MCheck->query($sqlCheck);
				if (count($checkResult)>0) {
					
					if (C('DB_TYPE') == 'oracle') {
						$sqlInsert="update THINK_TBCODECOLLECTIONNAME set JLZT='1',COLLECTIONNAME='$name',COLLECTIONLEVEL='$cl',COLLECTIONCOMMENT='$cmt' 
								,OPERATERID ='$operaterid',OPERATERTIME=sysdate  where COLLECTIONID = '$cid'" ;						
					}
					else
					{
						$sqlInsert="update THINK_TBCODECOLLECTIONNAME set JLZT='1',COLLECTIONNAME='$name',COLLECTIONLEVEL='$cl',COLLECTIONCOMMENT='$cmt' 
								,OPERATERID ='$operaterid',OPERATERTIME=date('now')  where COLLECTIONID = '$cid'" ;						
					}

				}
				else{
					if (C('DB_TYPE') == 'oracle') {
						$sqlInsert = "INSERT INTO THINK_TBCODECOLLECTIONNAME(COLLECTIONID,COLLECTIONNAME,
								COLLECTIONLEVEL,COLLECTIONCOMMENT,OPERATERID,,OPERATERTIME) 
								VALUES('$cid','$name','$cl','$cmt','$operaterid',sysdate)";						
					}
					else
					{
						$sqlInsert = "INSERT INTO THINK_TBCODECOLLECTIONNAME(COLLECTIONID,COLLECTIONNAME,
								COLLECTIONLEVEL,COLLECTIONCOMMENT,OPERATERID,,OPERATERTIME) 
								VALUES('$cid','$name','$cl','$cmt','$operaterid',date('now'))";						
					}

				}
			}
			//echo $sqlInsert;
			//return;
			
			if ($InfoCollectionNameClass->execute($sqlInsert))
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('添加完成,正在跳转 ...！');
				//$this->redirect('Admin-Index/CollectionNameEdit', null, 1, '添加完成！正在跳转...');
			} else
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('添加失败,正在跳转 ...！');
				//$this->redirect('Admin-Index/CollectionNameEdit', null, 1, '添加失败！正在跳转...');
			}
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
	
	function CollectionNameAdd()
	{
		$this->display();
	}
	function CollectionNameUpdate()
	{
		if ($this->checkRole())
		{
			$cid = $_POST['cid'];
			$name = $_POST['name'];
			$level = $_POST['level'];
			$cmt = $_POST['comment'];
			if(empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('编辑代码集异常,正在跳转 ...！');
				return;
			}
			$Class = new Model();
			$sql = "update THINK_TBCODECOLLECTIONNAME set COLLECTIONNAME = '$name',
					COLLECTIONCOMMENT = '$cmt',COLLECTIONLEVEL = '$level'
					where COLLECTIONID = '$cid'";
			if($Class->execute($sql))
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$this->assign('jumpUrl',"/Admin/Index/CollectionNameIndex");
				$this->success('已保存更改,正在跳转 ...！');
				return;
			}else
			{
				$this->assign('jumpUrl',"/Admin/Index/CollectionNameEdit/cid/".$cid);
				$this->success('保存出错,正在跳转 ...！');
				return;
			}
		}
	}
	function CollectionNameEdit()
	{
		if ($this->checkRole())
		{
			$cid = $_GET['cid'];
			//echo $cid;
			//return;
			$cid=$this->checkUTF8($cid);
			if(empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('编辑代码集异常,正在跳转 ...！');
				return;
			}
			$Class = new Model();
			$sql = "SELECT COLLECTIONID AS CID,COLLECTIONNAME AS NAME,
					COLLECTIONCOMMENT AS CMT,COLLECTIONLEVEL AS CL
					FROM THINK_TBCODECOLLECTIONNAME where COLLECTIONID = '$cid' and JLZT='1'";
			$list = $Class->query($sql);
			//var_dump($list);
			//return;
			$this->assign('codeCollectionName', $list[0]);
			$this->display();
		}
	}
	public function add()
	{
		if ($this->checkRole())
		{
			$cid = $_GET['cid'];
			if (empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/specialIndex');
				$this->success('不存在要添加的代码集,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '添加项目出错！');
				//$cid = -1;
				return;
			}
			$Class = new Model();
			$sql = "SELECT COLLECTIONID AS CID,COLLECTIONNAME AS NAME,COLLECTIONLEVEL AS CL
					FROM THINK_TBCODECOLLECTIONNAME where JLZT='1'";
			$list = $Class->query($sql);
			if (count($list) <= 0)
			{
				$this->assign('jumpUrl','/Admin/Index/specialIndex');
				$this->success('不存在要添加的代码集,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '不存在要添加的代码集！');
				return;
			}
			$sqlNameList = "SELECT COLLECTIONID AS CID,
					ID, NAME,CODECOMMENT AS CMT,UPNODEID
					FROM THINK_TBCODECOLLECTION  WHERE COLLECTIONID = '$cid' and JLZT='1'";
			$NameList = $Class->query($sqlNameList);
			$this->assign('NameList', $NameList);
			//$this->trace('menu List ', dump($list, false));
			$this->assign('codeCollectionList', $list);
			$this->assign('selectedCid', $cid);
			$this->display();
		}
	}
	// 添加新代码集数据
	public function insert()
	{
		if ($this->checkRole())
		{
			$id = trim($_POST['id']);
			$name = trim($_POST['name']);
			$cid = trim($_POST['cid']);
			$upnodeID = trim($_POST['upnodeID']);
			$comment = trim($_POST['comment']);
			if (empty($name) || empty($cid))
			{
				$this->assign('jumpUrl',"/Admin/Index/add/cid/".$cid);
				$this->success('数据填写不完全,正在跳转 ...！');
				//$this->redirect('Admin-Index/add', null, 1, '数据填写不完全！');
			} else
			{
				$Class = new Model();
				$sql = "SELECT COLLECTIONID AS CID,
						ID, NAME,CODECOMMENT AS CMT
						FROM THINK_TBCODECOLLECTION  WHERE ID = '$id' and COLLECTIONID = '$cid' and JLZT='1'";
				$list = $Class->query($sql);
				if (count($list) > 0)
				{
					$this->assign('jumpUrl',"/Admin/Index/add/cid/".$cid);
					$this->success('该项目已经添加,正在跳转 ...！');
					//$this->redirect('Admin-Index/add', null, 1, '该项目已经添加！');
					return;
				} else
				{
					$sql ="";
					$operaterid= $_SESSION['acount'];
					$MCheck =new Model();
					$sqlCheck = "select COLLECTIONID from THINK_TBCODECOLLECTION where ID = '$id' and COLLECTIONID = '$cid'";
					$checkResult=$MCheck->query($sqlCheck);
					if (count($checkResult)>0) {
						if (C('DB_TYPE') == 'oracle') {
							$sql ="update THINK_TBCODECOLLECTION set JLZT='1',OPERATERTIME=sysdate,NAME='$name',CODECOMMENT='$comment'
									,UPNODEID='$upnodeID',OPERATERID='$operaterid' 
									where  ID = '$id' and COLLECTIONID = '$cid'";
						}
						else
						{
							$sql ="update THINK_TBCODECOLLECTION set JLZT='1',OPERATERTIME=date('now'),NAME='$name',CODECOMMENT='$comment'
									,UPNODEID='$upnodeID' ,OPERATERID='$operaterid'
									where  ID = '$id' and COLLECTIONID = '$cid'";
						}						
					}else{
						
						//将数据添加到数据库中
						if (C('DB_TYPE') == 'oracle') {
							$sql = "INSERT INTO THINK_TBCODECOLLECTION(COLLECTIONID,ID,NAME,CODECOMMENT,UPNODEID,OPERATERID,OPERATERTIME)
									values('$cid','$id','$name','$comment','$upnodeID','$operaterid',sysdate) ";	
						}
						else
						{
							$sql = "INSERT INTO THINK_TBCODECOLLECTION(COLLECTIONID,ID,NAME,CODECOMMENT,UPNODEID,OPERATERID,OPERATERTIME)
									values('$cid','$id','$name','$comment','$upnodeID','$operaterid',date('now')) ";							
						}
						
					}

					if ($Class->execute($sql))
					{
						
						$M = new Model();
						$sqlSelect =
							"SELECT COLLECTIONNAME,COLLECTIONLEVEL FROM THINK_TBCODECOLLECTIONNAME
								where COLLECTIONID ='$cid' and JLZT='1'";
						$cNameList = $M->query($sqlSelect);
						if(count($cNameList)>0)
						{
							$className = $cNameList[0]['COLLECTIONNAME'];
							$classLevel = $cNameList[0]['COLLECTIONLEVEL'];
							date_default_timezone_set("Asia/Shanghai");
							$vTime = date("Y-m-d H:i:s");
							$author = $_SESSION['nickname'];
							$content = "在 $classLevel 中的 $className  增加了代码为 $id 的代码集";
							//$uniqueID = $this->GetUniqueID();
							$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
									values('$vTime','$author','$content','codeCollection') ";
							$M->execute($sqlInsert);
						}
						
						$url = "/Admin/Index/specialIndex/cid/$cid";
						$this->assign('jumpUrl',$url);
						$this->success('数据添加成功,正在跳转 ...！');
						//$this->redirect($url, null, 1, '数据添加成功！');
						return;
					} else
					{
						$this->assign('jumpUrl',"/Admin/Index/add/cid/".$cid);
						$this->success('数据添加异常,正在跳转 ...！');
						//$this->redirect('Admin-Index/add', null, 1, '数据添加异常！');
					}
				}
				
			}
			
		}
	}
	
	
	// 编辑数据
	public function edit()
	{
		if ($this->checkRole())
		{
			$cid = $_GET['cid']; //collection id
			$id = $_GET['id']; // code id
			//log::write("edit cid=$cid id = $id");
			if ((empty($id) || empty($cid)) && $id != '0' && $cid != '0')
			{
				//log::write("edit empty cid=$cid id = $id");
				$this->assign('jumpUrl','/Admin/Index/specialIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
			} else
			{
				$cid=$this->checkUTF8($cid);
				$id=$this->checkUTF8($id);
				$CodeCollectionNameClass = new Model();
				$CodeCollectionlist = $CodeCollectionNameClass->query("SELECT COLLECTIONID AS CID,COLLECTIONNAME AS NAME
							,COLLECTIONLEVEL AS CL
							FROM THINK_TBCODECOLLECTIONNAME where JLZT='1'");
				$this->assign('codeCollectionlist', $CodeCollectionlist);
				//$this->trace('edit codeCollectionlist', dump($CodeCollectionlist, false));
				
				$CodeCollectionClass = new Model();
				$sql = "SELECT COLLECTIONID AS CID,
						ID, NAME,CODECOMMENT AS CMT,UPNODEID
						FROM THINK_TBCODECOLLECTION  WHERE ID = '$id' and COLLECTIONID = '$cid' and JLZT='1' ";
				$list = $CodeCollectionClass->query($sql);
				if (count($list) <= 0)
				{
					$url = "Admin/Index/specialIndex";
					$this->assign('jumpUrl','/Admin/Index/specialIndex');
					$this->success('编辑项不存在,正在跳转 ...！');
					//$this->redirect($url, null, 1, '编辑项不存在！');
					return;
				}
				$this->assign('ccClass', $list[0]);
				//                $upnodeID = $list[0]['upnodeID'];
				//                $sqlUpnodeName = "select name from think_tbCodeCollection
				//  where id = $upnodeID' and collectionID = '$cid'";
				//                $list = $CodeCollectionClass->query($sqlUpnodeName);
				//                if (count($list) <= 0)
				//                {
				//                    $this->assign('upnodeName', "");
				//                } else
				//                {
				//                    $this->assign('upnodeName', $list[0]['name']);
				//                }
				$sqlNameList = "SELECT COLLECTIONID AS CID,
						ID, NAME,CODECOMMENT AS CMT,UPNODEID
						FROM THINK_TBCODECOLLECTION  WHERE COLLECTIONID = '$cid'
						and ID not in('$id') 
						and ID not in( SELECT ID FROM THINK_TBCODECOLLECTION where UPNODEID = '$id') and JLZT='1'";
				$NameList = $CodeCollectionClass->query($sqlNameList);
				
				$this->assign('NameList', $NameList);
				//log::write("edit class: $list[0]");
				$this->display();
				
			}
		}
	}
	
	// 删除数据
	public function delete()
	{
		if ($this->checkRole())
		{
			$strs = $_GET['strs'];
			$strs=$this->checkUTF8($strs);
			$sqlDelete = "";
			
			if (empty($strs)) {
				$this->assign('jumpUrl','/Admin/Index/specialIndex');
				$this->success('删除项目出错,正在跳转 ...！');
			}
			else
			{
				//echo $strs."<br>";
				$idandcids = explode('?',$strs);
				//var_dump( $idandcids);
				//return;
				for	($i=0;$i<count($idandcids);$i++)
				{
					$idandcid=explode(':',$idandcids[$i]);
					if (count($idandcid)<2) {
						$this->assign('jumpUrl','/Admin/Index/specialIndex');
						$this->success('删除项目出错,正在跳转 ...！');
					}
					$id=$idandcid[0];
					$cid=$idandcid[1];
					$Class = new Model();
					$sql = "SELECT COLLECTIONID,
							ID, NAME
							FROM THINK_TBCODECOLLECTION  WHERE ID = '$id' and COLLECTIONID = '$cid' and JLZT='1' ";
					$list = $Class->query($sql);
					if (count($list) <= 0)
					{
						$url = "/Admin/Index/specialIndex/cid/$cid";
						$this->assign('jumpUrl',$url);
						$this->success('该项不存在,正在跳转 ...！');
						//$this->redirect($url, null, 1, '该项不存在！');
						return;
					}
					
					if (C('DB_TYPE') == 'oracle') {
						$sqlDelete .= "update THINK_TBCODECOLLECTION set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'   WHERE ID = '$id' and COLLECTIONID = '$cid';";
						$sqlDelete .= "update THINK_TBCODECOLLECTION set UPNODEID = NULL where UPNODEID = '$id';";
					}
					else
					{
						$sqlDelete .= "update THINK_TBCODECOLLECTION set JLZT='0',OPERATERTIME=date('now'),OPERATERID='$operaterid'   WHERE ID = '$id' and COLLECTIONID = '$cid';";
						$sqlDelete .= "update THINK_TBCODECOLLECTION set UPNODEID = NULL where UPNODEID = '$id';";
					}
					
				}
				
				//echo $sqlDelete;
				//return;
				
				
				$state=true;
				
				if (!empty($sqlDelete)) {
					$state=$this->executeSql($sqlDelete);
				}
				
				if ($state) {
					
					$M = new Model();
					$sqlSelect =
						"SELECT COLLECTIONNAME,COLLECTIONLEVEL FROM THINK_TBCODECOLLECTIONNAME
							where COLLECTIONID ='$cid' and JLZT='1' ";
					$cNameList = $M->query($sqlSelect);
					if(count($cNameList)>0)
					{
						$className = $cNameList[0]['COLLECTIONNAME'];
						$classLevel = $cNameList[0]['COLLECTIONLEVEL'];
						date_default_timezone_set("Asia/Shanghai");
						$vTime = date("Y-m-d H:i:s");
						$author = $_SESSION['nickname'];
						$content = "删除了 $classLevel 中 $className  中的代码为 $id 的代码集";
						$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
								values('$vTime','$author','$content','codeCollection') ";
						$M->execute($sqlInsert);
					}
					
					$url = "/Admin/Index/specialIndex/cid/$cid";
					$this->assign('jumpUrl',$url);
					$this->success('删除成功,正在跳转 ...！');					
				}
				else
				{
					$url = "/Admin/Index/specialIndex/cid/$cid";
					$this->assign('jumpUrl',$url);
					$this->success('发生错误，删除失败,正在跳转 ...！');	
				}
				
				//$this->redirect($url, null, 1, '删除成功！');
			}
		}
	}
	
	// 更新数据
	public function update()
	{
		if ($this->checkRole())
		{
			$id = trim($_POST['id']);
			$name = trim($_POST['name']);
			$cid = trim($_POST['cid']);
			$upnodeID = trim($_POST['upnodeID']);
			$comment = trim($_POST['comment']);
			//log::write("update cid=$cid id = $id name = $name");
			if (empty($id) || empty($cid) || empty($name))
			{
				log::write("update() -> 1");
				$this->assign('jumpUrl','/Admin/Index/specialIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
			} else
			{
				$CodeCollectionClass = new Model();
				$sql = "SELECT CC.COLLECTIONID AS CID
						,CCN.COLLECTIONNAME AS CNAME,
						ID, NAME,CODECOMMENT AS CMT,CC.UPNODEID,CC.EDITRECORDID 
						FROM THINK_TBCODECOLLECTION  CC,
						THINK_TBCODECOLLECTIONNAME CCN
						WHERE CC.COLLECTIONID = '$cid' and CC.ID = '$id' and 
						CC.COLLECTIONID = CCN.COLLECTIONID and CC.JLZT='1' ";
				
				// get the old content
				$list = $CodeCollectionClass->query($sql);
				if (count($list) <= 0)
				{
					$url = '/Admin/Index/specialIndex';
					$this->assign('jumpUrl',$url);
					$this->success('该项不存在,正在跳转 ...！');
					//$this->redirect($url, null, 1, '编辑项不存在！');
					return;
				}
				/*
				*	产生修改记录
				*/
				//默认值
				$sqlUpdate = "UPDATE THINK_TBCODECOLLECTION SET NAME='$name',CODECOMMENT = '$comment',UPNODEID 
						= '$upnodeID'
						where ID = '$id'  and COLLECTIONID = '$cid'";
				$uniqueID = 0;
				
				//说明之前并未有修改过
				if (empty($list[0]['EDITRECORDID']))
				{ // empty可以用来测试返回值是否为空
					//log::write("update() -> editRecordID is null");
					// 如果尚未有修改记录的编号
					$uniqueID = $this->GetUniqueID();
					//log::write("update() -> 4");
					$sqlUpdate = "UPDATE THINK_TBCODECOLLECTION SET NAME='$name',CODECOMMENT = '$comment',
							EDITRECORDID = '$uniqueID' 
							where ID = '$id'  and COLLECTIONID = '$cid'";
					
					
				} else
				{
					//log::write("update() -> editRecordID = " . $list[0]['editRecordID']);
					$uniqueID = $list[0]['EDITRECORDID'];
				}
				//插入修改记录
				$changeLog = "";
				$bHasChanged = false;
				
				date_default_timezone_set("Asia/Shanghai");
				$vTime = date("Y-m-d H:i:s");
				$author = $_SESSION['nickname'];
				$sqlInsert = "";		
				$originalCollection = $list[0];
				$cname = $originalCollection['CNAME'];
				
				if ($name != $originalCollection['NAME'])
				{
					$oName = $originalCollection['NAME'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$cname','$id','更新',
							'字段名称','$oName','$name','$uniqueID') ;";
					//$changeLog = $changeLog . " 字段名称由 " . $oName . "  改为 " . $name;
				}
				if ($comment != $originalCollection['CMT'])
				{
					$oComment = $originalCollection['CMT'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$cname','$id','更新',
							'说明','$oComment','$comment','$uniqueID') ;";
				}
				if($upnodeID != $originalCollection['UPNODEID'])
				{
					$oupnodeID = $originalCollection['UPNODEID'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$cname','$id','更新',
							'上级字段','$oupnodeID','$upnodeID','$uniqueID') ;";
				}
				
				if ($bHasChanged)
				{
					$M = new Model();
					$state = true;
					
					if (!empty($sqlInsert)) {
						$state=$this->executeSql($sqlInsert);
					}
					
					if (!$state)
					//if (!$M->execute($sqlInsert))
					{
						$url = "/Admin/Index/specialIndex/cid/$cid";
						$this->assign('jumpUrl',$url);
						$this->success('保存失败,正在跳转 ...！');
					}
					else
					{
						//************************************************************************
						$MO = new Model();
						$sqlSelect =
							"SELECT COLLECTIONNAME,COLLECTIONLEVEL FROM THINK_TBCODECOLLECTIONNAME
								where COLLECTIONID ='$cid' and JLZT='1' ";
						$cNameList = $MO->query($sqlSelect);
						if(count($cNameList)>0)
						{
							$className = $cNameList[0]['COLLECTIONNAME'];
							$classLevel = $cNameList[0]['COLLECTIONLEVEL'];
							$content = "更新了 $classLevel 中的 $className  的 代码为 $id 的代码集内容";
							$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
									values('$vTime','$author','$content','codeCollection') ";
							$MO->execute($sqlInsert);
						}						
						//************************************************************************						
					}
				}
				
				if ($CodeCollectionClass->execute($sqlUpdate))
				{
					$url = "/Admin/Index/specialIndex/cid/$cid";
					$this->assign('jumpUrl',$url);
					$this->success('已保存更改,正在跳转 ...！');
				} else
				{
					$url = "/Admin/Index/specialIndex/cid/$cid";
					$this->assign('jumpUrl',$url);
					$this->success('保存失败,正在跳转 ...！');
				}
			}
		}
	}
	
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		//return ((float)$usec + (float)$sec);
		return ((float)$usec * 1000000);
	}
	
	public function InfoDelete()
	{
		
		if ($this->checkRole())
		{
			$strs = $_GET['strs'];
			$sqlDelete = "";
			$cid="";
			if (empty($strs)) {
				$this->assign('jumpUrl','/Admin/Index/specialIndex');
				$this->success('删除项目出错,正在跳转 ...！');
			}
			else
			{
				$strs=$this->checkUTF8($strs);
				
				//echo $strs."<br>";
				//return;
				$idandcids = explode('?',$strs);
				//var_dump( $idandcids);
				//return;
				$operaterid= $_SESSION['acount'];
				
				for	($i=0;$i<count($idandcids);$i++)
				{
					$idandcid=explode(':',$idandcids[$i]);
					if (count($idandcid)<2) {
						$this->assign('jumpUrl','/Admin/Index/specialIndex');
						$this->success('删除项目出错,正在跳转 ...！');
					}
					//var_dump($idandcid);
					//return;
					$id=$idandcid[0];
					$cid=$idandcid[1];

					//$sqlDelete .= "delete from THINK_TBINFOCLASS where ICLASSID = '$cid' and VID = '$id';";
					
					if (C('DB_TYPE') == 'oracle') {
						$sqlDelete .= "update  THINK_TBINFOCLASS set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'  where ICLASSID = '$cid' and VID = '$id';";
					}
					else
					{
						$sqlDelete .= "update  THINK_TBINFOCLASS set JLZT='0',OPERATERTIME=date('now'),OPERATERID='$operaterid'  where ICLASSID = '$cid' and VID = '$id';";
					}
					
					$M = new Model();
					$sqlSelect = "SELECT ICLASSID,VCLASSNAME,VCLASSLEVEL 
							FROM THINK_TBINFOCLASSNAME where ICLASSID = '$cid'  and JLZT='1'";
					$cNameList = $M->query($sqlSelect);
					if(count($cNameList)>0)
					{
						$className = $cNameList[0]['VCLASSNAME'];
						$classLevel = $cNameList[0]['VCLASSLEVEL'];
						date_default_timezone_set("Asia/Shanghai");
						$vTime = date("Y-m-d H:i:s");
						$author = $_SESSION['nickname'];
						$content = "删除了 $classLevel 中的 $className  中编号为 $id 的子类项";
						$sqlDelete .= "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
								values('$vTime','$author','$content','class'); ";
					}
					
				}
				//echo $sqlDelete;
				//echo $this->microtime_float()."<br>";
				//echo $this->microtime_float()."<br>";
				//return;
				$state=true;
				if (!empty($sqlDelete)) {
					$state=$this->executeSql($sqlDelete);
				}
				
				if ($state) {
					
					$url = "/Admin/Index/InfoClassIndex";
					if(!empty($cid))
					{
						$url.="/icid/$cid";
					}
					$this->assign('jumpUrl',$url);
					$this->success('删除成功,正在跳转 ...！');
					//$this->redirect($url, null, 1, '已保存更改！');
					
				}
				else
				{
					$url = "/Admin/Index/InfoClassIndex";
					if(!empty($cid))
					{
						$url.="/icid/$cid";
					}
					$this->assign('jumpUrl',$url);
					$this->success('删除失败,正在跳转 ...！');
				}
				
			}
		}
	}
	public function InfoAdd()
	{
		if ($this->checkRole())
		{
			$icid = $_GET['icid'];
			if (empty($icid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassIndex');
				$this->success('不存在该子集,正在跳转 ...！');
			} else
			{
				$InfoClassNameClass = new Model();
				$InfoClassNameList = $InfoClassNameClass->query("SELECT ICLASSID AS CID,VCLASSNAME AS NAME,VCLASSLEVEL AS CL
							FROM THINK_TBINFOCLASSNAME where JLZT='1' ");
				$this->assign('InfoClassNameList', $InfoClassNameList);
				$this->assign('icid', $icid);
				$this->display();
			}
		}
	}
	public function InfoInsert()
	{
		if ($this->checkRole())
		{
			$id = trim($_POST['itemId']);
			$name = trim($_POST['name']);
			$cName = trim($_POST['cName']);
			$icid = trim($_POST['icid']);
			$type = trim($_POST['type']);
			$len = trim($_POST['len']);
			$select = trim($_POST['select']);
			$scope = trim($_POST['scope']);
			$comment = trim($_POST['comment']);
			$ref = trim($_POST['ref']);
			
			if (empty($id) || empty($icid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
			} 
			$InfoClass = new Model();
			$sql = "SELECT IC.ICLASSID AS CID,IC.VID AS ID,
					IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
					IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
					IC.TCOMMENT AS CMT,IC.VREF AS REF
					FROM THINK_TBINFOCLASS IC
					where ICLASSID = '$icid' and VID = '$id' and JLZT='1'";
			$list = $InfoClass->query($sql);
			
			if (count($list) > 0)
			{
				$url = "/Admin/Index/InfoClassIndex/icid/".$icid;
				$this->assign('jumpUrl',$url);
				$this->success('编辑项已存在,正在跳转 ...！');
				return;
			}else
			{
				if(empty($len)) $len = 0;
				
				$sqlInsert ="";
				$operaterid= $_SESSION['acount'];
				$MCheck =new Model();
				$sqlCheck = "select * from THINK_TBINFOCLASS where ICLASSID='$icid' and VID='$id'";
				$checkResult=$MCheck->query($sqlCheck);
				if (count($checkResult)>0) {
					if (C('DB_TYPE') == 'oracle') {
						$sqlInsert = "UPDATE THINK_TBINFOCLASS set VNAME ='$name',
								VNAMECHN = '$cName',VTYPE = '$type',ILENGTH = $len,
								VSELECT = '$select',VVALUESCOPE = '$scope',TCOMMENT = '$comment',
								VREF = '$ref', JLZT='1',OPERATERTIME=sysdate,OPERATERID='$operaterid' 
								where VID = '$id'  and ICLASSID = '$icid'";
					}
					else
					{
						$sqlInsert = "UPDATE THINK_TBINFOCLASS set VNAME ='$name',
								VNAMECHN = '$cName',VTYPE = '$type',ILENGTH = $len,
								VSELECT = '$select',VVALUESCOPE = '$scope',TCOMMENT = '$comment',
								VREF = '$ref', JLZT='1',OPERATERTIME=date('now'),OPERATERID='$operaterid' 
								where VID = '$id'  and ICLASSID = '$icid'";						
					}						
				}else{
					
					//将数据添加到数据库中
					if (C('DB_TYPE') == 'oracle') {
						$sqlInsert = "INSERT INTO THINK_TBINFOCLASS(ICLASSID,VID,VNAME,VNAMECHN,VTYPE,ILENGTH,
								VSELECT,VVALUESCOPE,TCOMMENT,VREF,OPERATERID,OPERATERTIME)
								values('$icid','$id','$name','$cName','$type',$len,
								'$select','$scope','$comment','$ref','$operaterid',sysdate) ";							
					}
					else
					{
						$sqlInsert = "INSERT INTO THINK_TBINFOCLASS(ICLASSID,VID,VNAME,VNAMECHN,VTYPE,ILENGTH,
								VSELECT,VVALUESCOPE,TCOMMENT,VREF,OPERATERID,OPERATERTIME)
								values('$icid','$id','$name','$cName','$type',$len,
								'$select','$scope','$comment','$ref','$operaterid',date('now')) ";							
					}
					
				}
				
				if ($InfoClass->execute($sqlInsert))
				{
					//************************************************************
					$M = new Model();
					$sqlSelect = "SELECT ICLASSID,VCLASSNAME,VCLASSLEVEL 
							FROM THINK_TBINFOCLASSNAME where ICLASSID = '$icid' and JLZT='1'";
					$cNameList = $M->query($sqlSelect);
					if(count($cNameList)>0)
					{
						$className = $cNameList[0]['VCLASSNAME'];
						$classLevel = $cNameList[0]['VCLASSLEVEL'];
						date_default_timezone_set("Asia/Shanghai");
						$vTime = date("Y-m-d H:i:s");
						$author = $_SESSION['nickname'];
						$content = "在 $classLevel 中的 $className  增加了编号为 $id 的子类项";
						//$uniqueID = $this->GetUniqueID();
						$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
								values('$vTime','$author','$content','class') ";
						$M->execute($sqlInsert);
					}
					//************************************************************
					
					$url = "/Admin/Index/InfoClassIndex/icid/$icid";
					$this->assign('jumpUrl',$url);
					$this->success('已保存更改,正在跳转 ...！');
					
					return;
				} else
				{
					$url = "/Admin/Index/InfoClassIndex/icid/$icid";
					$this->assign('jumpUrl',$url);
					$this->success('保存失败,正在跳转 ...！');
					return;
				}
			}
		}
	}
	public function InfoUpdate()
	{
		if ($this->checkRole())
		{
			$id = trim($_POST['itemId']);
			$name = trim($_POST['name']);
			$cName = trim($_POST['cName']);
			$icid = trim($_POST['icid']);
			$type = trim($_POST['type']);
			$len = trim($_POST['len']);
			$select = trim($_POST['select']);
			$scope = trim($_POST['scope']);
			$comment = trim($_POST['comment']);
			$ref = trim($_POST['ref']);
			
			if (empty($id) || empty($icid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
			} else
			{
				$InfoClass = new Model();
				$sql = "SELECT IC.ICLASSID AS CID,ICN.VCLASSNAME AS CLASSNAME,
						IC.VID AS ID,IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
						IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
						IC.TCOMMENT AS CMT,IC.VREF AS REF,IC.EDITRECORDID 
						FROM THINK_TBINFOCLASS IC,THINK_TBINFOCLASSNAME ICN
						WHERE IC.ICLASSID = '$icid' and IC.VID = '$id'
						AND IC.ICLASSID = ICN.ICLASSID and IC.JLZT='1' ";
				//$sql = "SELECT ic.iClassID as cid,ic.vId as id,
				//	ic.vName as name,ic.vNamechn as cName,
				//	ic.vType,ic.iLength as len,ic.vSelect,ic.vValueScope as scope,
				//	ic.tComment as cmt,ic.vRef as ref
				//	FROM think_tbInfoClass as ic
				//	where iClassID = '$icid' and vId = '$id'";
				$list = $InfoClass->query($sql);
				if (count($list) <= 0)
				{
					$url = "/Admin/Index/InfoClassIndex";
					$this->assign('jumpUrl',$url);
					$this->success('该项不存在,正在跳转 ...！');
					//$this->redirect($url, null, 1, '编辑项不存在！');
					return;
				}
				$sqlUpdate = "UPDATE THINK_TBINFOCLASS set VNAME ='$name',
						VNAMECHN = '$cName',VTYPE = '$type',ILENGTH = $len,
						VSELECT = '$select',VVALUESCOPE = '$scope',TCOMMENT = '$comment',
						VREF = '$ref' 
						where VID = '$id'  and ICLASSID = '$icid'";
				
				$uniqueID = 0;
				
				if (empty($list[0]['EDITRECORDID']))
				{ // empty可以用来测试返回值是否为空
					// 如果尚未有修改记录的编号
					$uniqueID = $this->GetUniqueID();
					$sqlUpdate = "UPDATE THINK_TBINFOCLASS SET VNAME ='$name',
							VNAMECHN = '$cName',VTYPE = '$type',ILENGTH = $len,
							VSELECT = '$select',VVALUESCOPE = '$scope',TCOMMENT = '$comment',
							VREF = '$ref',EDITRECORDID = '$uniqueID' 
							where VID = '$id'  and ICLASSID = '$icid'";
					
				} else
				{
					log::write("update() -> editRecordID = " . $list[0]['EDITRECORDID']);
					$uniqueID = $list[0]['EDITRECORDID'];
				}
				
				//插入修改记录
				$changeLog = "";
				$sqlInsert = "";
				
				$bHasChanged = false;
				date_default_timezone_set("Asia/Shanghai");
				$vTime = date("Y-m-d H:i:s");
				$author = $_SESSION['nickname'];
				$originalCollection = $list[0];
				$className = $originalCollection['CLASSNAME'];
				//* 暂时注释修改记录
				if ($name != $originalCollection['NAME'])
				{
					$oName = $originalCollection['NAME'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$className','$id','更新',
							'数据项名','$oName','$name','$uniqueID') ;";
					//$changeLog = $changeLog . " 数据项名 由 " . $oName . "  改为 " . $name;
				}
				if ($cName != $originalCollection['CNAME'])
				{
					$ocName = $originalCollection['CNAME'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$className','$id','更新',
							'中文简称','$ocName','$cName','$uniqueID') ;";
					//$changeLog = $changeLog . " 中文简称 由 " . $ocName . "  改为 " . $cName;
				}
				if ($type != $originalCollection['VTYPE'])
				{
					$otype = $originalCollection['VTYPE'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$className','$id','更新',
							'类型','$otype','$type','$uniqueID') ;";
					//$changeLog = $changeLog . " 类型 由 " . $otype . "  改为 " . $type;
				}
				if ($len != $originalCollection['LEN'])
				{
					$oLen = $originalCollection['LEN'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$className','$id','更新',
							'长度','$oLen','$len','$uniqueID') ;";
					//$changeLog = $changeLog . " 长度 由 " . $oLen . "  改为 " . $len;
				}
				if ($select != $originalCollection['VSELECT'])
				{
					$osel = $originalCollection['vselect'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$className','$id','更新',
							'可选','$osel','$select','$uniqueID') ;";
					//$changeLog = $changeLog . " 可选 由 " . $osel . "  改为 " . $select;
				}
				if ($scope != $originalCollection['SCOPE'])
				{
					$oscope = $originalCollection['SCOPE'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$className','$id','更新',
							'取值范围','$oscope','$scope','$uniqueID') ;";
					//$changeLog = $changeLog . " 取值范围 由 " . $oscope . "  改为 " . $scope;
				}
				if ($comment != $originalCollection['CMT'])
				{
					$oComment = $originalCollection['CMT'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$className','$id','更新',
							'说明','$oComment','$comment','$uniqueID') ;";
					//$changeLog = $changeLog . " 说明 由 " . $oComment . "  改为 " . $comment;
				}
				if ($ref != $originalCollection['REF'])
				{
					$oref = $originalCollection['REF'];
					$bHasChanged = true;
					$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$className','$id','更新',
							'引用编号','$oref','$ref','$uniqueID') ;";
					//$changeLog = $changeLog . " 引用编号 由 " . $oref . "  改为 " . $ref;
				}
				//if ($changeLog != "")
				if ($bHasChanged)
				{
					$state=true;
					if (!empty($sqlInsert)) {
						$state=$this->executeSql($sqlInsert);
					}
					
					if (!$state)
					//if (!$M->execute($sqlInsert))
					{
						$url = "/Admin/Index/InfoClassIndex/icid/$icid";
						$this->assign('jumpUrl',$url);
						$this->success('保存失败,正在跳转 ...！');
						//$this->redirect($url, null, 1, '保存失败！');
					}
				}
				
				if ($InfoClass->execute($sqlUpdate))
				{
					//************************************************************
					$M = new Model();
					$sqlSelect = "SELECT ICLASSID,VCLASSNAME,VCLASSLEVEL 
							FROM THINK_TBINFOCLASSNAME where ICLASSID = '$icid' and JLZT='1' ";
					$cNameList = $M->query($sqlSelect);
					if(count($cNameList)>0)
					{
						$className = $cNameList[0]['VCLASSNAME'];
						$classLevel = $cNameList[0]['VCLASSLEVEL'];
						date_default_timezone_set("Asia/Shanghai");
						$vTime = date("Y-m-d H:i:s");
						$author = $_SESSION['nickname'];
						$content = "更新了 $classLevel 中的 $className  中编号为 $id 的子类项";
						$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
								values('$vTime','$author','$content','class') ";
						$M->execute($sqlInsert);
					}
					//************************************************************
					
					$url = "/Admin/Index/InfoClassIndex/icid/$icid";
					//log::write("update() -> url " . $url);
					$this->assign('jumpUrl',$url);
					$this->success("已保存更改,正在跳转 ...！");
					//$this->redirect($url, null, 1, '已保存更改！');
				} else
				{
					$url = "/Admin/Index/InfoClassIndex/icid/$icid";
					$this->assign('jumpUrl',$url);
					$this->success('保存失败,正在跳转 ...！');
					//$this->redirect($url, null, 1, '保存失败！');
				}
			}
		}
	}
	public function InfoClassEdit()
	{
		if ($this->checkRole())
		{
			$icid = $_GET['icid']; //collection id
			$id = $_GET['id']; // code id
			if (empty($id) || empty($icid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
			} else
			{
				$icid=$this->checkUTF8($icid);
				$id=$this->checkUTF8($id);
				$InfoClassNameClass = new Model();
				$InfoClassNameList = $InfoClassNameClass->query("SELECT ICLASSID AS CID,VCLASSNAME AS NAME,VCLASSLEVEL AS CL
							FROM THINK_TBINFOCLASSNAME where JLZT='1' ");
				$this->assign('InfoClassNameList', $InfoClassNameList);
				
				$InfoClass = new Model();
				$sql = "SELECT IC.ICLASSID AS CID,IC.VID AS ID,
						IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
						IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
						IC.TCOMMENT AS CMT,IC.VREF AS REF
						FROM THINK_TBINFOCLASS IC
						WHERE ICLASSID = '$icid' and VID = '$id' and JLZT='1' ";
				$list = $InfoClass->query($sql);
				if (count($list) <= 0)
				{
					$url = "/Admin/Index/InfoClassIndex";
					$this->assign('jumpUrl',$url);
					$this->success('该项不存在,正在跳转 ...！');
					return;
				}
				$this->assign('InfoClass', $list[0]);
				$this->display();
				
			}
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
					$this->assign('jumpUrl','/Admin/Index/welcome');
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
	public function InfoClassNameAdd()
	{
		if ($this->checkRole())
		{
			$this->display();
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
	public function InfoClassNameUpdate()
	{
		if ($this->checkRole())
		{
			$cid = $_POST['cid'];
			$name = $_POST['name'];
			$level = $_POST['level'];
			$cmt = $_POST['comment'];
			if(empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('编辑代码集异常,正在跳转 ...！');
				return;
			}
			$Class = new Model();
			$sql = "update THINK_TBINFOCLASSNAME set VCLASSNAME = '$name',
					TCOMMENT = '$cmt',VCLASSLEVEL = '$level'
					where ICLASSID = '$cid'";
			if($Class->execute($sql))
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('已保存更改,正在跳转 ...！');
				return;
			}else
			{
				$this->assign('jumpUrl',"/Admin/Index/InfoClassNameEdit/$cid");
				$this->success('保存出错,正在跳转 ...！');
				return;
			}
		}
	}
	public function InfoClassNameEdit()
	{
		if ($this->checkRole())
		{
			$cid = $_GET['cid'];
			$cid=$this->checkUTF8($cid);
			if(empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('编辑信息子集异常,正在跳转 ...！');
				return;
			}
			$InfoClassNameClass = new Model();
			$InfoClassNameList = $InfoClassNameClass->query(
					"SELECT ICLASSID AS CID,VCLASSNAME AS NAME,VCLASSLEVEL AS CL,TCOMMENT AS CMT
						FROM THINK_TBINFOCLASSNAME where ICLASSID = '$cid' and JLZT='1' ");
			$this->assign('InfoClassName', $InfoClassNameList[0]);
			$this->display();
		}
	}
	public function InfoClassNameInsert()
	{
		if ($this->checkRole())
		{
			$cid = trim($_POST['cid']);
			$name = trim($_POST['name']);
			$cl = trim($_POST['level']);
			$cmt = trim($_POST['comment']);
			
			if (empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('编号未填写,正在跳转 ...！');
				return;
			}
			$sqlSelect = "SELECT ICLASSID FROM THINK_TBINFOCLASSNAME WHERE ICLASSID = '$cid' and JLZT='1'";
			$InfoClassNameClass = new Model();
			$list = $InfoClassNameClass->query($sqlSelect);
			if (count($list) > 0)
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('编号已存在,正在跳转 ...！');
				return;
			}
			$sqlInsert ="";
			$operaterid= $_SESSION['acount'];
			$MCheck =new Model();
			$sqlCheck =  "SELECT ICLASSID FROM THINK_TBINFOCLASSNAME WHERE ICLASSID = '$cid'";
			$checkResult=$MCheck->query($sqlCheck);
			//var_dump($checkResult);
			//return;
			
			if (count($checkResult)>0) {
				if (C('DB_TYPE') == 'oracle') {

					$sqlInsert = "update THINK_TBINFOCLASSNAME set VCLASSNAME = '$name',
							TCOMMENT = '$cmt',VCLASSLEVEL = '$cl', JLZT='1',OPERATERTIME=sysdate,OPERATERID='$operaterid' 
							where ICLASSID = '$cid'";
				}
				else
				{
					$sqlInsert = "update THINK_TBINFOCLASSNAME set VCLASSNAME = '$name',
							TCOMMENT = '$cmt',VCLASSLEVEL = '$cl', JLZT='1',OPERATERTIME=date('now'),OPERATERID='$operaterid' 
							where ICLASSID = '$cid'";
				}						
			}else{
				
				//将数据添加到数据库中
				if (C('DB_TYPE') == 'oracle') {
						
					$sqlInsert = "INSERT INTO THINK_TBINFOCLASSNAME(ICLASSID,VCLASSNAME,VCLASSLEVEL,TCOMMENT,OPERATERID,OPERATERTIME) 
							values('$cid','$name','$cl','$cmt','$operaterid',sysdate) ";							
				}
				else
				{
						
					$sqlInsert = "INSERT INTO THINK_TBINFOCLASSNAME(ICLASSID,VCLASSNAME,VCLASSLEVEL,TCOMMENT,OPERATERID,OPERATERTIME) 
							values('$cid','$name','$cl','$cmt','$operaterid',date('now')) ";						
				}
				
			}
			//echo $sqlInsert;
			//return;
			
			if ($InfoClassNameClass->execute($sqlInsert))
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('添加完成,正在跳转 ...！');
			} else
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('添加失败,正在跳转 ...！');
			}
		}
	}
	public function InfoClassNameDelete()
	{
		if ($this->checkRole())
		{
			$cids = $_GET['cid'];
			if (empty($cids))
			{
				$url = "/Admin/Index/InfoClassNameEdit";
				$this->assign('jumpUrl',$url);
				$this->success('该子集不存在,正在跳转 ...！');
				return;
			}
			$cids=$this->checkUTF8($cids);
			//echo $cids;
			//return;
			$cidA = explode('?',$cids);
			$sqlExcute="";
			$operaterid= $_SESSION['acount'];
			
			for($i=0;$i<count($cidA);$i++)
			{
				$cid=$cidA[$i];
				//$sqlExcute.="DELETE FROM THINK_TBINFOCLASSNAME WHERE ICLASSID = '$cid';";
				
				if (C('DB_TYPE') == 'oracle') {
					$sqlExcute.="update  THINK_TBINFOCLASSNAME set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'  WHERE ICLASSID = '$cid';";
				}
				else
				{
					$sqlExcute.="update  THINK_TBINFOCLASSNAME set JLZT='0',OPERATERTIME=date('now'),OPERATERID='$operaterid'  WHERE ICLASSID = '$cid';";
				}
			}
			$state = true;
			
			if (!empty($sqlExcute)) {
				$state=$this->executeSql($sqlExcute);
			}
			if($state)
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('删除成功,正在跳转 ...！');
			} else
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('删除失败,正在跳转 ...！');
				
			}
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
	public function ChangeLogDelete()
	{
		if ($this->checkRole())
		{	
			$strs = $_GET['strs'];
			$strs=$this->checkUTF8($strs);
			
			//echo $strs;
			//return;
			$sqlDelete = "";
			
			if (empty($strs)) {
				$this->assign('jumpUrl','/Admin/Index/specialIndex');
				$this->success('删除出错,正在跳转 ...！');
			}
			else
			{
				//echo $strs."<br>";
				$idandcids = explode('?',$strs);
				//var_dump( $idandcids);
				//return;
				$operaterid= $_SESSION['acount'];
				
				for	($i=0;$i<count($idandcids);$i++)
				{
					$idandcid=explode('|',$idandcids[$i]);
					if (count($idandcid)<6) {
						$this->assign('jumpUrl','/Admin/Index/specialIndex');
						$this->success('删除项目出错,正在跳转 ...！');
					}
					//var_dump($idandcid);
					//return;
					
					$time = $idandcid[0];
					$author = $idandcid[1];
					$parent = $idandcid[2];
					$action = $idandcid[3];
					$fieldname = $idandcid[4];
					$itemid = $idandcid[5];
					
					/*
					$sqlDelete .= "DELETE FROM THINK_TBCHANGERECORD WHERE VTIME='$time' and VAUTHOR='$author' 
							and VPARENTPAGE = '$parent' and VACTION = '$action' and VFIELDNAME = '$fieldname' 
							and ITEMID = '$itemid';";
							*/

					if (C('DB_TYPE') == 'oracle') {
						$sqlDelete .= "update THINK_TBCHANGERECORD set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'  WHERE VTIME='$time' and VAUTHOR='$author' 
								and VPARENTPAGE = '$parent' and VACTION = '$action' and VFIELDNAME = '$fieldname' 
								and ITEMID = '$itemid';";						
					}
					else
					{
						$sqlDelete .= "update THINK_TBCHANGERECORD set JLZT='0',OPERATERTIME=date('now'),OPERATERID='$operaterid'  WHERE VTIME='$time' and VAUTHOR='$author' 
								and VPARENTPAGE = '$parent' and VACTION = '$action' and VFIELDNAME = '$fieldname' 
								and ITEMID = '$itemid';";						
					}
				}
				
				$state=true;
				
				if (!empty($sqlDelete)) {
					$state =$this->executeSql($sqlDelete);
				}
				
				if ($state) {
					$url = '/Admin/Index/ChangeLogIndex/id/all';
					$this->assign('jumpUrl',$url);
					$this->success('删除成功,正在跳转 ...！');
					//$this->redirect('Admin-Index/ChangeLogIndex/id/all', null, 1, '删除成功！正在跳转...');
				} else
				{
					$url = '/Admin/Index/ChangeLogIndex/id/all';
					$this->assign('jumpUrl',$url);
					$this->success('删除失败,正在跳转 ...！');
					//$this->redirect('Admin-Index/ChangeLogIndex/id/all', null, 1, '删除失败！正在跳转...');
				}
			}
		}
	}
	public function CustomClassItemDelete()
	{
		if ($this->checkRole())
		{
			$sqlDelete="";
			$strs = $_GET['name'];
			if (empty($strs))
			{
				$this->assign('jumpUrl','/Admin/Index/CustomClassIndex');
				$this->success('项目不存在,正在跳转 ...！');
			}
			else
			{
				$strs=$this->checkUTF8($strs);
				$idcids = explode('?',$strs);
				//var_dump( $idandcids);
				//return;
				date_default_timezone_set("Asia/Shanghai");
				$vTime = date("Y-m-d H:i:s");
				$author = $_SESSION['nickname'];    
				$operaterid= $_SESSION['acount'];
				
				for	($i=0;$i<count($idcids);$i++)
				{
					$idandcid=explode(':',$idcids[$i]);
					if (count($idandcid)<2) {
						$this->assign('jumpUrl','/Admin/Index/CustomClassItemIndex');
						$this->success('删除项目出错,正在跳转 ...！');
					}
					$id=$idandcid[0];
					$cid=$idandcid[1];
					$Class = new Model();
					$sql = "SELECT class.ICLASSID as CID,class.VNAME AS CLASSNAME,VITEMID as ID, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
							,items.TCOMMENT AS CMT,EDITRECORDID,class.VCLASSLEVEL 
							FROM THINK_TBCUSTOMITEMS items,THINK_TBCUSTOMCLASSES class
							WHERE items.ICLASSID=class.ICLASSID and items.ICLASSID = '$cid' and items.VITEMID = '$id'  and items.JLZT='1'";
					
					$list = $Class->query($sql);
					if (count($list) <= 0)
					{
						$url = "/Admin/Index/CustomClassItemIndex/cid/$cid";
						$this->assign('jumpUrl',$url);
						$this->success('删除项目出错,正在跳转 ...！');
						return;
					}
					$ClassName=$list[0]['CLASSNAME'];
					if (C('DB_TYPE') == 'oracle') {
						$sqlDelete .= "update  THINK_TBCUSTOMITEMS set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'   WHERE VITEMID = '$id' and ICLASSID = '$cid';";
					}
					else
					{
						$sqlDelete .= "update  THINK_TBCUSTOMITEMS set JLZT='0',OPERATERTIME=date('now'),OPERATERID='$operaterid'   WHERE VITEMID = '$id' and ICLASSID = '$cid';";
					}
					//$sqlDelete .= "DELETE FROM THINK_TBCUSTOMITEMS  WHERE VITEMID = '$id' and ICLASSID = '$cid';";
					$sqlDelete .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
							VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$ClassName','$id','删除',
							'','','','') ;";  
					$classLevel = $list[0]['VCLASSLEVEL'];
					$content = "删除了 $classLevel 中的 $ClassName  的 编码为 $id 的自定义项目内容";
					$sqlDelete .= "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
							values('$vTime','$author','$content','custom');";
					
				}
				if ($this->executeSql($sqlDelete))
				{
					$url = "/Admin/Index/CustomClassItemIndex/cid/$cid";
					$this->assign('jumpUrl',$url);
					$this->success('删除成功,正在跳转 ...！');
				} else
				{
					$url = "/Admin/Index/CustomClassItemIndex/cid/$cid";
					$this->assign('jumpUrl',$url);
					$this->success('删除失败,正在跳转 ...！');
				}				
				
			}
		}
	}
	public function CustomClassItemInsert()
	{
		if ($this->checkRole())
		{
			
			$id = trim($_POST['id']);
			$name = trim($_POST['name']);
			$content = trim($_POST['content']);
			$cmt = trim($_POST['comment']);
			$cid = trim($_POST['cid']);
			
			if (empty($cid)) {
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('没有该集合可以添加,正在跳转 ...！');
			}			
			if (empty($id))
			{
				$url = "/Admin/Index/CustomClassItemAdd/cid/" . $cid;
				$this->assign('jumpUrl',$url);
				$this->success('条目编号必须填写,正在跳转 ...！');
			}
			if (empty($name)) {
				$name="未定义名称";
			}
			//echo "$cid <br>";
			//echo "$id <br>";
			//return;
			
			$M = new Model();
			$sqlSelect = "select * from THINK_TBCUSTOMITEMS where ICLASSID='$cid' and VITEMID ='$id' and JLZT='1' ";
			
			$list = $M->query($sqlSelect);
			if (count($list)>0) {
				$url = "/Admin/Index/CustomClassItemAdd/cid/" . $cid;
				$this->assign('jumpUrl',$url);
				$this->success('已经定义过该条目,正在跳转 ...！');
			}
			
			$sqlInsert ="";
			$operaterid= $_SESSION['acount'];
			$MCheck =new Model();
			$sqlCheck =  "select * from THINK_TBCUSTOMITEMS where ICLASSID='$cid' and VITEMID ='$id' ";
			$checkResult=$MCheck->query($sqlCheck);
			if (count($checkResult)>0) {
				if (C('DB_TYPE') == 'oracle') {
					$sqlInsert = "UPDATE THINK_TBCUSTOMITEMS SET VITEMNAME ='$name',VITEMCONTENT ='$content'
							,TCOMMENT='$cmt', JLZT='1',OPERATERTIME=sysdate,OPERATERID='$operaterid' 
							where VITEMID='$id' and ICLASSID='$cid';";

				}
				else
				{
					$sqlInsert = "UPDATE THINK_TBCUSTOMITEMS SET VITEMNAME ='$name',VITEMCONTENT ='$content'
							,TCOMMENT='$cmt', JLZT='1',OPERATERTIME=date('now'),OPERATERID='$operaterid' 
							where VITEMID='$id' and ICLASSID='$cid';";					

				}						
			}else{
				
				//将数据添加到数据库中
				if (C('DB_TYPE') == 'oracle') {
					$sqlInsert = "INSERT INTO THINK_TBCUSTOMITEMS(ICLASSID,VITEMID,VITEMNAME,VITEMCONTENT,TCOMMENT,OPERATERID,OPERATERTIME) 
							values('$cid','$id','$name','$content','$cmt','$operaterid',sysdate); ";				
				}
				else
				{
					$sqlInsert = "INSERT INTO THINK_TBCUSTOMITEMS(ICLASSID,VITEMID,VITEMNAME,VITEMCONTENT,TCOMMENT,OPERATERID,OPERATERTIME) 
							values('$cid','$id','$name','$content','$cmt','$operaterid',date('now')) ;";					
				}
				
			}
			

			date_default_timezone_set("Asia/Shanghai");
			$vTime = date("Y-m-d H:i:s");
			$author = $_SESSION['nickname'];    
			$ClassName="";
			$sql = "select ICLASSID,VNAME,VCLASSLEVEL from THINK_TBCUSTOMCLASSES where ICLASSID ='$cid' and JLZT='1' ";
			$nameList=$M->query($sql);
			if (count($nameList)>0) {
				$ClassName=$nameList[0]['VNAME'];
				$classLevel = $nameList[0]['VCLASSLEVEL'];
			}
			$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
					VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$ClassName','$id','新增',
					'','','','') ;";  
			$content = "新增 $classLevel 中的 $ClassName  的 编码为 $id 的自定义项目内容";
			$sqlInsert .= "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
					values('$vTime','$author','$content','custom');";
			
			if ($this->executeSql($sqlInsert))
			{
				$url = "/Admin/Index/CustomClassItemIndex/cid/" . $cid;
				$this->assign('jumpUrl',$url);
				$this->success('保存成功,正在跳转 ...！');
			} else
			{
				$url = "/Admin/Index/CustomClassItemIndex/cid/" . $cid;
				$this->assign('jumpUrl',$url);
				$this->success('添加失败,正在跳转 ...！');
			}
		}
	}
	public function CustomClassItemUpdate()
	{
		if ($this->checkRole())
		{
			$cid=trim($_POST['cid']);
			$id=trim($_POST['id']);
			$name = trim($_POST['name']);
			$content = trim($_POST['content']);
			$cmt = trim($_POST['comment']);
			if(empty($cid)|| empty($id))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('编辑异常,正在跳转 ...！');
				return;
			}
			
			$M = new Model();
	
			$sql = "SELECT class.ICLASSID as CID,class.VNAME AS CLASSNAME,VITEMID as ID, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
					,items.TCOMMENT AS CMT,EDITRECORDID 
					FROM THINK_TBCUSTOMITEMS items,THINK_TBCUSTOMCLASSES class
					WHERE items.ICLASSID=class.ICLASSID and items.ICLASSID = '$cid' and items.VITEMID = '$id' and items.JLZT='1' ";
			
			$list = $M->query($sql);
			if (count($list) <= 0)
			{
				$url = "/Admin/Index/CustomClassItemIndex/cid/$cid";
				$this->assign('jumpUrl',$url);
				$this->success('编辑异常,正在跳转 ...！');
				return;
			}
			$ClassName=$list[0]['CLASSNAME'];
			
			$sqlUpdate ="";
			//如果已经含有修改记录ID，则会使用该sql，否则会做其他处理
			$sqlUpdate = "UPDATE THINK_TBCUSTOMITEMS SET VITEMNAME ='$name',VITEMCONTENT ='$content'
					,TCOMMENT='$cmt' 
					where VITEMID='$id' and ICLASSID='$cid';";

			/*
			*	产生修改记录
			*/
			//默认值
			$uniqueID = 0;
			
			if (empty($list[0]['EDITRECORDID']))
			{ // empty可以用来测试返回值是否为空
				// 如果尚未有修改记录的编号
				$uniqueID = $this->GetUniqueID();

				$sqlUpdate = "UPDATE THINK_TBCUSTOMITEMS SET VITEMNAME ='$name',VITEMCONTENT ='$content'
						,TCOMMENT='$cmt',EDITRECORDID = '$uniqueID'  
						where VITEMID='$id' and ICLASSID='$cid';";
				
			} else
			{
				$uniqueID = $list[0]['EDITRECORDID'];
			}
			//插入修改记录
			$changeLog = "";
			$bHasChanged = false; 
			date_default_timezone_set("Asia/Shanghai");
			$vTime = date("Y-m-d H:i:s");
			$author = $_SESSION['nickname'];           
			$originalCollection = $list[0];
			if ($name!=$originalCollection['NAME']) {
				
				$bHasChanged = true;
				$oName = $originalCollection['NAME'];
				
				$sqlUpdate .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
						VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$ClassName','$id','更新',
						'名称','$oName','$name','$uniqueID') ;";                
			}
			if ($content != $originalCollection['CONTENT'])
			{
				$bHasChanged = true;
				
				$ocontent = $originalCollection['CONTENT'];
				$sqlUpdate .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
						VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$ClassName','$id','更新',
						'内容','$ocontent','$content','$uniqueID') ;";                
				//$changeLog = $changeLog . " 内容 由 " . $ocontent . "  改为 " . $content;
			}
			if ($cmt != $originalCollection['CMT'])
			{
				$bHasChanged = true;
				
				$oComment = $originalCollection['CMT'];
				$sqlUpdate .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
						VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$ClassName','$id','更新',
						'说明','$oComment','$cmt','$uniqueID') ;";  
			}
			
			if ($bHasChanged) {
				//************************************************************************
				$MO = new Model();
				$sqlSelect =
					"SELECT VNAME,VCLASSLEVEL FROM THINK_TBCUSTOMCLASSES
						where ICLASSID ='$cid' and JLZT='1' ";
				$cNameList = $MO->query($sqlSelect);
				if(count($cNameList)>0)
				{
					$className = $cNameList[0]['VNAME'];
					$classLevel = $cNameList[0]['VCLASSLEVEL'];
					$content = "更新了 $classLevel 中的 $className  的 代码为 $id 的自定义项目内容";
					$sqlUpdate .= "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
							values('$vTime','$author','$content','custom');";
				}						
				//echo $sqlUpdate;
				//return;
				
				//************************************************************************	
				if ($this->executeSql($sqlUpdate))
				{
					$url = "/Admin/Index/CustomClassItemIndex/cid/$cid";
					$this->assign('jumpUrl',$url);
					$this->success('保存成功,正在跳转 ...！');
				} else
				{
					$url = "/Admin/Index/CustomClassItemIndex/cid/$cid";
					$this->assign('jumpUrl',$url);
					$this->success('保存失败,正在跳转 ...！');
				}				
				
			}
			else
			{
				
				$url = "/Admin/Index/CustomClassItemIndex/cid/$cid";
				$this->assign('jumpUrl',$url);
				$this->success('该项没有任何更改,正在跳转 ...！');
			}
			
		}
	}
	public function CustomClassItemEdit()
	{
		if ($this->checkRole())
		{
			$id = $_GET['id'];
			$cid=$_GET['cid'];
			
			if (empty($id)|| empty($cid))
			{
				$url = "/Admin/Index/CustomClassItemIndex";
				$this->assign('jumpUrl',$url);
				$this->success('操作异常,正在跳转 ...！');
				return;
			}
			$id =$this->checkUTF8($id);
			$cid =$this->checkUTF8($cid);
			/*
			$sql = "SELECT VCLASSNAME AS CLASSNAME, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
					,TCOMMENT AS CMT,EDITRECORDID 
					FROM THINK_TBCUSTOMITEMS WHERE VITEMNAME = '" . $itemName . "'";
					*/
			$sql = "SELECT class.ICLASSID as CID,class.VNAME AS CLASSNAME,VITEMID as ID, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
					,items.TCOMMENT AS CMT,EDITRECORDID 
					FROM THINK_TBCUSTOMITEMS items,THINK_TBCUSTOMCLASSES class
					WHERE items.ICLASSID=class.ICLASSID and items.ICLASSID = '$cid' and items.VITEMID = '$id' and items.JLZT='1' ";
			
			//echo $sql;
			//return;
			
			//log::write("CustomClassItemEdit -> " . $sql);
			$M = new Model();
			$list = $M->query($sql);
			if (count($list) > 0)
			{
				$this->assign('item', $list[0]);
				$this->assign('itemName', $list[0]['CLASSNAME']);
				$this->display();
			}
			else
			{
				
				$url = "/Admin/Index/CustomClassItemIndex/cid/$cid";
				$this->assign('jumpUrl',$url);
				$this->success('不存在要编辑的项,正在跳转 ...！');
			}
		}
	}
	public function CustomClassItemAdd()
	{
		if ($this->checkRole())
		{
			$cid = $_GET['cid'];
			if (empty($cid))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('不存在该类,正在跳转 ...！');
				// $this->redirect('Admin-Index/CustomClassIndex', null, 1, '不存在该类...');
				return;
			}
			$cid = $this->checkUTF8($cid);
			$M = new Model();
			$this->assign('cid', $cid);
			$sql = "select ICLASSID as CID,  VNAME as NAME from THINK_TBCUSTOMCLASSES where ICLASSID = '$cid' and JLZT='1' ";
			//echo $sql ."<br>";
			//return;
			
			$nameList = $M->query($sql);
			//var_dump($nameList);
			//return;
			
			$this->assign('itemName', $nameList[0]['NAME']);
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
					$url = "/Admin/Index/CustomClassIndex";
					$this->assign('jumpUrl',$url);
					$this->success('不存在该类,正在跳转 ...！');
					//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '不存在该类...');
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
			$CustomClassNameList = $M->query("SELECT ICLASSID as CID, VNAME AS NAME,VCLASSLEVEL AS LEVEL,TCOMMENT AS CMT
						FROM THINK_TBCUSTOMCLASSES WHERE  JLZT='1'");
			$this->assign('list', $CustomClassNameList);
			$this->display();
		}
	}
	public function CustomClassInsert()
	{
		if ($this->checkRole())
		{
			$cid = trim($_POST['cid']);
			$name = trim($_POST['name']);
			$cmt = trim($_POST['comment']);
			$level=trim($_POST['level']);
			if (empty($cid))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('请添加有效编码后再保存,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '请添加有效名称后再保存...');
				return;
			}
			if (empty($name)) {
				$name="未定义名称";
			}
			if (empty($level)) {
				$level="未定义类别";
			}
			
			$M = new Model();
			$sqlSelect = "select * from THINK_TBCUSTOMCLASSES where ICLASSID='$cid' and JLZT='1' ";
			
			$list = $M->query($sqlSelect);
			if (count($list)>0) {
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('已经定义过该条目,正在跳转 ...！');
			}
			
			$sqlInsert ="";
			$operaterid= $_SESSION['acount'];
			$MCheck =new Model();
			$sqlCheck =  "select * from THINK_TBCUSTOMCLASSES where ICLASSID='$cid'";
			$checkResult=$MCheck->query($sqlCheck);
			if (count($checkResult)>0) {
				if (C('DB_TYPE') == 'oracle') {

					$sqlInsert = "UPDATE THINK_TBCUSTOMCLASSES
							SET VNAME ='$name',VCLASSLEVEL = '$level',TCOMMENT ='$comment', JLZT='1',OPERATERTIME=sysdate,OPERATERID='$operaterid'  
							where ICLASSID='$cid'";
					
				}
				else
				{
				
					$sqlInsert = "UPDATE THINK_TBCUSTOMCLASSES
							SET VNAME ='$name',VCLASSLEVEL = '$level',TCOMMENT ='$comment', JLZT='1',OPERATERTIME=date('now'),OPERATERID='$operaterid'  
							where ICLASSID='$cid'";
					
				}						
			}else{
				
				//将数据添加到数据库中
				if (C('DB_TYPE') == 'oracle') {
					$sqlInsert = "INSERT INTO THINK_TBCUSTOMCLASSES(ICLASSID,VNAME,TCOMMENT,VCLASSLEVEL,OPERATERID,OPERATERTIME) 
							VALUES('$cid','$name','$cmt','$level','$operaterid',sysdate) ";								
				}
				else
				{
					$sqlInsert = "INSERT INTO THINK_TBCUSTOMCLASSES(ICLASSID,VNAME,TCOMMENT,VCLASSLEVEL,OPERATERID,OPERATERTIME) 
							VALUES('$cid','$name','$cmt','$level','$operaterid',date('now')) ";						
				}
				
			}
			
			$M = new Model();
			if ($M->execute($sqlInsert))
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('保存成功,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '保存成功...');
			} else
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('保存失败,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '保存失败...');
			}
		}		
	}
	public function CustomClassAdd()
	{
		if ($this->checkRole())
		{
			$this->display();
		}
	}
	public function CustomClassUpdate()
	{
		if ($this->checkRole())
		{
			$cid = $_POST['cid'];
			$name = $_POST['name'];
			$level=$_POST['level'];
			$comment=$_POST['comment'];
			//echo $cid;
			//return;
			
			if (empty($cid)) {
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('编辑项不存在,正在跳转 ...！');
				return;
			}
			if (empty($name)) {
				$name="未定义名称";
			}
			if (empty($level)) {
				$level="未定义类别";
			}
			$cid=$this->checkUTF8($cid);
			$sqlUpdate = "UPDATE THINK_TBCUSTOMCLASSES
					SET VNAME ='$name',VCLASSLEVEL = '$level',TCOMMENT ='$comment' 
					where ICLASSID='$cid'";
			
			//echo $sql;
			//return;
			$M = new Model();
			if($M->execute($sqlUpdate))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('保存成功,正在跳转 ...！');
			}
			else
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('保存失败,正在跳转 ...！');
			}
		}
	}
	public function CustomClassEdit()
	{
		if ($this->checkRole())
		{
			$cid = $_GET['cid'];
			//echo $cid;
			//return;
			
			if (empty($cid)) {
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('编辑项不存在,正在跳转 ...！');
				return;
			}
			$cid=$this->checkUTF8($cid);
			
			$sql="SELECT ICLASSID as CID, VNAME AS NAME,VCLASSLEVEL AS LEVEL,TCOMMENT AS CMT
					FROM THINK_TBCUSTOMCLASSES where ICLASSID='$cid' and JLZT='1' ";
			//echo $sql;
			//return;
			$M = new Model();
			$list = $M->query($sql);
			$this->assign('class', $list[0]);
			$this->display();
		}
	}
	public function CustomClassDelete()
	{
		if ($this->checkRole())
		{
			$cids = $_GET['cid'];
			if (empty($cids))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('删除失败,正在跳转 ...！');
			}
			//echo $cids;
			//return;
			
			$cidA=explode('?',$cids);
			$state=true;
			date_default_timezone_set("Asia/Shanghai");
			$vTime = date("Y-m-d H:i:s");
			$author = $_SESSION['nickname'];
			$operaterid= $_SESSION['acount'];
			
			$sqlDelete = "";
			for($i=0;$i<count($cidA);$i++)
			{
				$cid=$cidA[$i];
				$cid=$this->checkUTF8($cid);

				if (C('DB_TYPE') == 'oracle') {
					$sqlDelete .= "update THINK_TBCUSTOMCLASSES set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'  WHERE ICLASSID = '$cid';";
					$sqlDelete .= "update THINK_TBCUSTOMITEMS set JLZT='0',OPERATERTIME=sysdate,OPERATERID='$operaterid'  WHERE ICLASSID = '$cid';";					
				}
				else
				{
					$sqlDelete .= "update THINK_TBCUSTOMCLASSES set JLZT='0',OPERATERTIME=date('now')  WHERE ICLASSID = '$cid';";
					$sqlDelete .= "update THINK_TBCUSTOMITEMS set JLZT='0',OPERATERTIME=date('now')  WHERE ICLASSID = '$cid';";					
				}
				//$sqlDelete .= "DELETE FROM THINK_TBCUSTOMCLASSES WHERE ICLASSID = '$cid';";
				//$sqlDelete .= "DELETE FROM THINK_TBCUSTOMITEMS WHERE ICLASSID = '$cid';";
				/*
				$content = "删除了接口规范 $fileName ";
				$sqlInsert .= "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
						values('$vTime','$author','$content','interface'); ";
								
				if ( !unlink($path) || !$this->executeSql($sqlInsert) ) {
					$state=false;
					break;
				}
				*/
			}
			
			if (!empty($sqlDelete)) {
				$state=$this->executeSql($sqlDelete);
			}
			//if ($M->execute($sqlDelete))
			if ($state)
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('删除成功,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '删除成功...');
			} else
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('删除失败,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '删除失败...');
			}
		}
	}
	public function GetUniqueID()
	{
		$finded = false;
		$today = getdate();
		
		$uniqueID = $today[0];
		
		while (!$finded)
		{
			$M = new Model();
			$uniqueIDList = $M->query("SELECT ID FROM THINK_TBUNIQUEID WHERE ID = '$uniqueID'");
			if (count($uniqueIDList) > 0)
			{
				$uniqueID = $uniqueID + 1;
			} else
			{
				$sql = "INSERT INTO THINK_TBUNIQUEID(ID) VALUES('$uniqueID') ";
				if ($M->execute($sql))
				{
					$finded = true;
					break;
				} else
				{
					$uniqueID = $uniqueID + 1;
				}
			}
		}
		return $uniqueID;
	}
	//public function DisplayInfo
}

?>
