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
			$path = "./Public/Uploads/Interfaces/".$fileNameSystemBased;
			$sqlDelete="delete from T_INTERFACES_FILE where FILE_NAME='".$fileName."'";
			$M = new Model(); 
			if (!$M->execute($sqlDelete) || !unlink($path)) {
				$state=false;
			}
		}
		if ($state) {
			
			//************************************************************
			$M = new Model();
			date_default_timezone_set("Asia/Shanghai");
			$vTime = date("Y-m-d H:i:s");
			$author = $_SESSION['nickname'];
			$content = "删除了接口规范 $filename ";
			$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
					values('$vTime','$author','$content','interface') ";
			$M->execute($sqlInsert);
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
			$sqlSelect="select FILE_NAME,UPLOAD_DATE,FILE_SIZE,AUTHOR from T_INTERFACES_FILE;";
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
			/*
			if("GB2312" !=mb_detect_encoding($fileNameSystemBased))
			{
				$fileNameSystemBased =
					mb_convert_encoding( $fileNameSystemBased,'GB2312','utf-8');
			} 
			*/
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
		//    	$fileName = mb_convert_encoding( $filename,'gb2312','utf-8'); 
		$path = "./Public/Uploads/Interfaces/".$fileName;
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
		//var_dump($_FILES);return;
		$filename =$_FILES["file"]["name"];
		$M = new Model();  
		$sqlSelect="select FILE_NAME  from T_INTERFACES_FILE where FILE_NAME='".$filename."';";
		$fileSelectList = $M->query($sqlSelect);
		log::write($filename." uploadInterface ".count($fileSelectList) );
		if (count($fileSelectList)>0) {
			$this->assign('jumpUrl','/Admin/Index/InterfacesIndex');
			$this->success("文件已经上传，如果需要更新，请先删除之前的文件,正在跳转 ...！");
			return;
		}
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200000;
		//设置附件上传目录
		$upload->savePath = './Public/Uploads/Interfaces/';
		//设置上传文件规则
		//if(eregi(“WIN”,PHP_OS))
		if (!$upload->upload()) {
			//捕获上传异常
			log::write('_upload error' );
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
			$size = $uploadList[0]['size']/1000;
			
			$sqlInsertNewFile =
				"insert into T_INTERFACES_FILE(FILE_NAME,UPLOAD_DATE,FILE_SIZE,AUTHOR)
					values('".$filename."','".$vTime."','".$size."','".$author."')";
			
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
			
			//log::write('_upload filename ='.$uploadList[0]['savename'] );
			//log::write('_upload savepath ='.$uploadList[0]['savepath'] );
			
			//$filepathUtf8 = $uploadList[0]['savepath'].$uploadList[0]['savename'];
			//	$filepath = mb_convert_encoding( $filepathUtf8,'gb2312','utf-8'); 
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
				WHERE IC.ICLASSID = '$cid' AND IC.ICLASSID = ICN.ICLASSID";
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
			//log::write("exportData id=$id ");
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
		// mysql version
		$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN,TCOMMENT AS CMT
				FROM THINK_TBCODERULES";
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
				CC.COLLECTIONID = CCN.COLLECTIONID";
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
			//$this->assign('jumpUrl','/Admin/Index/menu');
			//$this->success('代码集不存在,正在跳转 ...！');
			//$this->redirect('Admin-Index/menu', null, 1, '代码集不存在！');
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
		log::write("main->");
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
			$levelList = $Class->query("select distinct COLLECTIONLEVEL as CL from THINK_TBCODECOLLECTIONNAME");
			//$this->trace('menu levelList ', dump($levelList, false));
			$this->assign('levelList', $levelList);
			$list = $Class->query("SELECT COLLECTIONID AS ID,COLLECTIONNAME AS NAME
						,COLLECTIONLEVEL AS CL FROM THINK_TBCODECOLLECTIONNAME");
			//$this->trace('menu List ', dump($list, false));
			$this->assign('codeCollectionlist', $list);
			
			$CodeRulesClass = new Model();
			$codeRulesList = $CodeRulesClass->query("SELECT VID AS ID,VNAME AS NAME,
						VNAMECHN AS SHOWNAME,VTYPE AS TP,NLENGTH AS LEN,TCOMMENT AS CMT
						FROM THINK_TBCODERULES");
			$this->assign('rulesList', $codeRulesList);
			
			$InfoClassNameClass = new Model();
			$InfoClassNameLevelList = $InfoClassNameClass->query("SELECT DISTINCT VCLASSLEVEL AS CL
						FROM THINK_TBINFOCLASSNAME");
			$this->assign('InfoClassNamelevelList', $InfoClassNameLevelList);
			$InfoClassNameList = $InfoClassNameClass->query("SELECT ICLASSID AS CID,VCLASSNAME AS NAME
						,VCLASSLEVEL AS CL FROM THINK_TBINFOCLASSNAME");
			$this->assign('InfoClassNameList', $InfoClassNameList);
			
			$M = new Model();
			$CustomClassNameList = $M->query("SELECT VNAME AS NAME,TCOMMENT AS CMT
						FROM THINK_TBCUSTOMCLASSES ");
			$this->assign('CustomClassNamelist', $CustomClassNameList);
			
			
			$this->display();
		}
	}
	public function checkRole()
	{
		//        $role = Cookie::get('role');
		//        if (empty($role) || $role == 'demo')
		//        {
		//            $this->redirect('Home-Welcome/welcome', null, 1, '正在跳转到登录页面...');
		//            return false;
		//        } else
		if ($_SESSION['logined'] != 1)
		{
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Welcome/logout'";
			echo "</script>";
			return;
		}
		if ($_SESSION['role_name'] != "editor")
		{
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Welcome/logout'";
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
						FROM THINK_TBCODERULES WHERE VID = '$rid'";
			} else
			{
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT,EDITRECORDID 
						FROM THINK_TBCODERULES";
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
			//log::write("ruleEdit rid=$rid ");
			if (empty($rid))
			{
				$this->assign('jumpUrl','/Admin/Index/ruleIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
			} else
			{
				$CodeRuleClass = new Model();
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT  FROM THINK_TBCODERULES WHERE VID = '$rid'";
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
			//log::write("update cid=$cid id = $id name = $name");
			if (empty($rid))
			{
				$this->assign('jumpUrl','/Admin/Index/ruleIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
			} else
			{
				$CodeRuleClass = new Model();
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT,EDITRECORDID 
						FROM THINK_TBCODERULES WHERE VID = '$rid'";
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
				/***************************************
				//  将修改记录注释 oracle数据时使用这种sql写法
				if (C('DB_TYPE') == 'oracle') {
					$sqlInsert = "begin ";
				}
				///////////////////////////////////////*/	
				
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
					
					$M = new Model();
					//***************************************
					//  将修改记录注释 oracle数据时使用这种sql写法
					if (C('DB_TYPE') == 'oracle') {
						$sqlInsert="begin ".$sqlInsert." end;";	
						if (!$M->execute($sqlInsert))
						{
							$state=false;
						}
					}
					////////////////////////////////////////
					
					// sqlite使用不同的方法执行
					if (C('DB_TYPE') == 'pdo') {
						if (!empty($sqlInsert)) {
							$sqlArray = explode(';',$sqlInsert);
							//var_dump($sqlArray);
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
			//log::write("delete cid=$cid id = $id");
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
				
				for($i=0;$i<count($ridA);$i++)
				{
					$rid=$ridA[$i];
					$sqlExcute .="DELETE FROM THINK_TBCODERULES  WHERE VID = '$rid';";
					
				}
				
				
				
				$state = true;
				
				//***************************************
				//  将修改记录注释 oracle数据时使用这种sql写法
				if (C('DB_TYPE') == 'oracle') {
					if (!empty($sqlExcute)) {
						$sqlExcute ="begin ".$sqlExcute." end;";	
					}
					$ConfigM = new Model();
					if (!empty($sqlExcute)) {
						if (!$ConfigM->execute($sqlExcute)) {
							$state=false;
						}
					}				
				}
				////////////////////////////////////////
				//******************************************
				// sqlite使用不同的方法执行
				//echo $sqlExcute."<br>";
				//TODO sqlite execute sqls
				if (C('DB_TYPE') == 'pdo') {
					if (!empty($sqlExcute)) {
						$sqlArray = explode(';',$sqlExcute);
						//var_dump($sqlArray);
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
				
				/*
				$CodeRuleClass = new Model();
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT
						FROM THINK_TBCODERULES WHERE VID = '$rid'";
				$list = $CodeRuleClass->query($sql);
				if (count($list) <= 0)
				{
					$this->assign('jumpUrl','/Admin/Index/ruleIndex');
					$this->success('该项不存在,正在跳转 ...！');
					//$url = "Admin-Index/menu";
					//$this->redirect($url, null, 1, '该项不存在！');
					return;
				}
				$sqlDelete = "DELETE FROM THINK_TBCODERULES  WHERE VID = '$rid'";
								
				if($CodeRuleClass->execute($sqlDelete))
				*/	
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
				
				//$this->redirect($url, null, 1, '删除成功！');
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
				$CodeRuleClass = new Model();
				$sql = "SELECT VID AS RULEID,VNAME AS NAME,VNAMECHN AS CNAME,VTYPE,NLENGTH AS LEN
						,TCOMMENT AS CMT
						FROM THINK_TBCODERULES WHERE VID = '$rid'";
				$list = $CodeRuleClass->query($sql);
				if (count($list) > 0)
				{
					$url = 'Admin-Index/ruleIndex';
					$this->assign('jumpUrl','/Admin/Index/ruleIndex');
					$this->success('该项已存在,正在跳转 ...！');                  
					//$this->redirect($url, null, 1, '该项已存在！');
					return;
				}
				$sqlInsert = "INSERT INTO THINK_TBCODERULES ( VID,VNAME,VNAMECHN,
						VTYPE ,NLENGTH ,TCOMMENT)
						values('$rid','$name','$cName', '$type', $len, '$comment')";
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
						FROM THINK_TBCODECOLLECTIONNAME";
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
				//$this->assign('jumpUrl','/Admin/Index/menu');
				//$this->success('不存在该代码集,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '不存在该代码集！');
				//return;
			}
			$Class = new Model();
			$slqHierarchi = "SELECT ID,UPNODEID
					FROM THINK_TBCODECOLLECTION WHERE COLLECTIONID = '$cid'
					AND UPNODEID  IN (SELECT ID FROM THINK_TBCODECOLLECTION) ";
			$sql = "SELECT CC.COLLECTIONID AS CID
					,CCN.COLLECTIONNAME AS CNAME,
					ID, NAME,CODECOMMENT AS CMT,CC.EDITRECORDID,CC.UPNODEID  
					FROM THINK_TBCODECOLLECTION CC,
					THINK_TBCODECOLLECTIONNAME CCN
					WHERE CC.COLLECTIONID = '$cid' and
					CC.COLLECTIONID = CCN.COLLECTIONID";
			
			//$sql = "SELECT CC.COLLECTIONID AS CID ,CCN.COLLECTIONNAME AS CNAME, ID, NAME,CODECOMMENT AS CMT,CC.EDITRECORDID,CC.UPNODEID FROM THINK_TBCODECOLLECTION CC, THINK_TBCODECOLLECTIONNAME CCN WHERE CC.COLLECTIONID = '测试代码集1' and CC.COLLECTIONID = CCN.COLLECTIONID";
			//echo $sql;
			//return;
			$list = $Class->query($sql);	
			
			/*		
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
			*/
			//{
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
						WHERE COLLECTIONID = '$cid' ";
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
			//}
			
			//$this->display();
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
				//$this->redirect('Admin-Index/CollectionNameEdit', null, 1, '该代码集不存在！');
				return;
			}
			$cids=$this->checkUTF8($cids);
			
			//echo $cids;
			//return;
			$cidA = explode('?',$cids);
			$sqlExcute="";
			
			for($i=0;$i<count($cidA);$i++)
			{
				$cid=$cidA[$i];
				$sqlExcute.="DELETE FROM THINK_TBCODECOLLECTIONNAME WHERE COLLECTIONID = '$cid';";
				$sqlExcute.= "delete from THINK_TBCODECOLLECTION where COLLECTIONID = '$cid';";
				
			}
			
			//echo $sqlExcute;
			//return;
			$state = true;
			
			//***************************************
			//  将修改记录注释 oracle数据时使用这种sql写法
			if (C('DB_TYPE') == 'oracle') {
				if (!empty($sqlExcute)) {
					$sqlExcute ="begin ".$sqlExcute." end;";	
				}
				$ConfigM = new Model();
				if (!empty($sqlExcute)) {
					if (!$ConfigM->execute($sqlExcute)) {
						$state=false;
					}
				}				
			}
			////////////////////////////////////////
			//******************************************
			// sqlite使用不同的方法执行
			//echo $sqlExcute."<br>";
			//TODO sqlite execute sqls
			if (C('DB_TYPE') == 'pdo') {
				if (!empty($sqlExcute)) {
					$sqlArray = explode(';',$sqlExcute);
					//var_dump($sqlArray);
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
			
			//$InfoCollectionNameClass = new Model();
			//$InfoCollectionNameClass2 = new Model();
			//if ($InfoCollectionNameClass->execute($sqlDelete1) )
			//if ($InfoCollectionNameClass->execute($sqlDelete))
			//&& $InfoCollectionNameClass->execute($sqlDeleteRelations))
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
			$sqlSelect = "SELECT COLLECTIONID FROM THINK_TBCODECOLLECTIONNAME WHERE COLLECTIONID = '$cid' ";
			$InfoCollectionNameClass = new Model();
			$list = $InfoCollectionNameClass->query($sqlSelect);
			if (count($list) > 0)
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('编号已存在,正在跳转 ...！');
				//$this->redirect('Admin-Index/CollectionNameEdit', null, 1, '编号已存在！');
				return;
			}
			$sqlInsert = "INSERT INTO THINK_TBCODECOLLECTIONNAME(COLLECTIONID,COLLECTIONNAME,
					COLLECTIONLEVEL,COLLECTIONCOMMENT) 
					VALUES('$cid','$name','$cl','$cmt')";
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
					FROM THINK_TBCODECOLLECTIONNAME";
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
			if(empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/CollectionNameIndex');
				$this->success('编辑代码集异常,正在跳转 ...！');
				return;
			}
			$Class = new Model();
			$sql = "SELECT COLLECTIONID AS CID,COLLECTIONNAME AS NAME,
					COLLECTIONCOMMENT AS CMT,COLLECTIONLEVEL AS CL
					FROM THINK_TBCODECOLLECTIONNAME where COLLECTIONID = '$cid'";
			$list = $Class->query($sql);
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
					FROM THINK_TBCODECOLLECTIONNAME";
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
					FROM THINK_TBCODECOLLECTION  WHERE COLLECTIONID = '$cid'";
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
						FROM THINK_TBCODECOLLECTION  WHERE ID = '$id' and COLLECTIONID = '$cid'";
				$list = $Class->query($sql);
				if (count($list) > 0)
				{
					$this->assign('jumpUrl',"/Admin/Index/add/cid/".$cid);
					$this->success('该项目已经添加,正在跳转 ...！');
					//$this->redirect('Admin-Index/add', null, 1, '该项目已经添加！');
					return;
				} else
				{
					//将数据添加到数据库中
					$sql = "INSERT INTO THINK_TBCODECOLLECTION(COLLECTIONID,ID,NAME,CODECOMMENT,UPNODEID)
							values('$cid','$id','$name','$comment','$upnodeID') ";
					if ($Class->execute($sql))
					{
						//TODO THINK_TBEDITRECORD
						
						$M = new Model();
						$sqlSelect =
							"SELECT COLLECTIONNAME,COLLECTIONLEVEL FROM THINK_TBCODECOLLECTIONNAME
								where COLLECTIONID ='$cid'";
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
							FROM THINK_TBCODECOLLECTIONNAME");
				$this->assign('codeCollectionlist', $CodeCollectionlist);
				//$this->trace('edit codeCollectionlist', dump($CodeCollectionlist, false));
				
				$CodeCollectionClass = new Model();
				$sql = "SELECT COLLECTIONID AS CID,
						ID, NAME,CODECOMMENT AS CMT,UPNODEID
						FROM THINK_TBCODECOLLECTION  WHERE ID = '$id' and COLLECTIONID = '$cid'";
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
						and ID not in( SELECT ID FROM THINK_TBCODECOLLECTION where UPNODEID = '$id')";
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
			/*
			之前的版本
			$cid = $_GET['cid']; //collection id
			$id = $_GET['id']; // code id
			if (empty($id_copy) || empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/specialIndex');
				$this->success('删除项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '删除项目出错！');
			}			
			*/
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
							FROM THINK_TBCODECOLLECTION  WHERE ID = '$id' and COLLECTIONID = '$cid'";
					$list = $Class->query($sql);
					if (count($list) <= 0)
					{
						$url = "/Admin/Index/specialIndex/cid/$cid";
						$this->assign('jumpUrl',$url);
						$this->success('该项不存在,正在跳转 ...！');
						//$this->redirect($url, null, 1, '该项不存在！');
						return;
					}
					$sqlDelete .= "DELETE FROM THINK_TBCODECOLLECTION  WHERE ID = '$id' and COLLECTIONID = '$cid';";
					//$Class->execute($sqlDelete);
					$sqlDelete .= "update THINK_TBCODECOLLECTION set UPNODEID = NULL where UPNODEID = '$id';";
					
				}
				
				//echo $sqlDelete;
				//return;
				
				
				$state=true;
				
				
				//***************************************
				//  将修改记录注释 oracle数据时使用这种sql写法
				if (C('DB_TYPE') == 'oracle') {
					$sqlDelete.="begin ".$sqlDelete." end;";	
					if (!$Class->execute($sqlDelete)) {
						$state=false;
					}
				}
				////////////////////////////////////////
				
				// sqlite使用不同的方法执行
				if (C('DB_TYPE') == 'pdo') {
					if (!empty($sqlDelete)) {
						$sqlArray = explode(';',$sqlDelete);
						//var_dump($sqlArray);
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
				
				//if ($Class->execute($sqlDelete)) {
				if ($state) {
					
					$M = new Model();
					$sqlSelect =
						"SELECT COLLECTIONNAME,COLLECTIONLEVEL FROM THINK_TBCODECOLLECTIONNAME
							where COLLECTIONID ='$cid'";
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
						CC.COLLECTIONID = CCN.COLLECTIONID";
				
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
				
				
				
				/***************************************
				//将修改记录注释 oracle数据时使用这种sql写法
				if (C('DB_TYPE') == 'oracle') {
					$sqlInsert = "begin ";	
				}
								
				//////////////////////////////////////*/	
				
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
					
					//***************************************
					//  将修改记录注释 oracle数据时使用这种sql写法
					if (C('DB_TYPE') == 'oracle') {
						$sqlInsert="begin ".$sqlInsert." end;";	
						
						if (!$M->execute($sqlInsert))
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
						if (!empty($sqlInsert)) {
							$sqlArray = explode(';',$sqlInsert);
							//var_dump($sqlArray);
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
								where COLLECTIONID ='$cid'";
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
					/*
										$Class = new Model();
										$sql = "SELECT COLLECTIONID,
												ID, NAME
												FROM THINK_TBCODECOLLECTION  WHERE ID = '$id' and COLLECTIONID = '$cid'";
										$list = $Class->query($sql);
										if (count($list) <= 0)
										{
											$url = "/Admin/Index/specialIndex/cid/$cid";
											$this->assign('jumpUrl',$url);
											$this->success('该项不存在,正在跳转 ...！');
											//$this->redirect($url, null, 1, '该项不存在！');
											return;
										}
					*/
					$sqlDelete .= "delete from 
							THINK_TBINFOCLASS 
							where ICLASSID = '$cid' and VID = '$id';";
					
					
					$M = new Model();
					$sqlSelect = "SELECT ICLASSID,VCLASSNAME,VCLASSLEVEL 
							FROM THINK_TBINFOCLASSNAME where ICLASSID = '$cid' ";
					$cNameList = $M->query($sqlSelect);
					if(count($cNameList)>0)
					{
						$className = $cNameList[0]['VCLASSNAME'];
						$classLevel = $cNameList[0]['VCLASSLEVEL'];
						date_default_timezone_set("Asia/Shanghai");
						$vTime = date("Y-m-d H:i:s ");
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
				if ($sqlDelete=="") {
					return;
				}
				//***************************************
				//  将修改记录注释 oracle数据时使用这种sql写法
				if (C('DB_TYPE') == 'oracle') {
					$sqlDelete.="begin ".$sqlDelete." end;";	
					if (!$Class->execute($sqlDelete)) {
						$state=false;
					}
				}
				////////////////////////////////////////
				
				// sqlite使用不同的方法执行
				if (C('DB_TYPE') == 'pdo') {
					if (!empty($sqlDelete)) {
						$sqlArray = explode(';',$sqlDelete);
						//var_dump($sqlArray);
						for($i=0;$i<count($sqlArray)-1;$i++)
						{
							$ConfigM = new Model();
							//var_dump($sqlArray);
							//return;
							if (!$ConfigM->execute($sqlArray[$i])) {
								//var_dump($sqlArray[$i]);
								//return;
								$state=false;
								break;
							}
						}
					}					
				}
				//******************************************	
				
				if ($state) {
					
					$url = "/Admin/Index/InfoClassIndex";
					$this->assign('jumpUrl',$url);
					$this->success('删除成功,正在跳转 ...！');
					//$this->redirect($url, null, 1, '已保存更改！');
					
				}
				else
				{
					$url = "/Admin/Index/InfoClassIndex";
					$this->assign('jumpUrl',$url);
					$this->success('删除失败,正在跳转 ...！');
					//$this->redirect($url, null, 1, '保存失败！');
				}
				
				/*			
							$icid = $_GET['icid'];
							$id = $_GET['id'];
							
							if (empty($icid) || empty($id))
							{
								$this->assign('jumpUrl','/Admin/Index/welcome');
								$this->success('不存在该子集,正在跳转 ...！');
								//$this->redirect('Admin-Index/menu', null, 1, '不存在该子集！');
							}
							else
							{
								
								$InfoClass = new Model();
								$sqlDelete = "delete from 
										THINK_TBINFOCLASS 
										where ICLASSID = '$icid' and VID = '$id'";
								
								//log::Write("InfoDelete->  11 ".$sqlDelete);
								if ($InfoClass->execute($sqlDelete))
								{
									//log::Write("InfoDelete-> 22");
									//************************************************************
									$M = new Model();
									$sqlSelect = "SELECT ICLASSID,VCLASSNAME,VCLASSLEVEL 
											FROM THINK_TBINFOCLASSNAME where ICLASSID = '$icid' ";
									$cNameList = $M->query($sqlSelect);
									if(count($cNameList)>0)
									{
										$className = $cNameList[0]['VCLASSNAME'];
										$classLevel = $cNameList[0]['VCLASSLEVEL'];
										date_default_timezone_set("Asia/Shanghai");
										$vTime = date("Y-m-d H:i:s");
										$author = $_SESSION['nickname'];
										$content = "删除了 $classLevel 中的 $className  中编号为 $id 的子类项";
										$sqlInsert = "insert into THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
												values('$vTime','$author','$content','class') ";
										$M->execute($sqlInsert);
									}
									//************************************************************
									$url = "/Admin/Index/InfoClassIndex";
									$this->assign('jumpUrl',$url);
									$this->success('删除成功,正在跳转 ...！');
									//$this->redirect($url, null, 1, '已保存更改！');
								} else
								{
									$url = "/Admin/Index/InfoClassIndex";
									$this->assign('jumpUrl',$url);
									$this->success('删除失败,正在跳转 ...！');
									//$this->redirect($url, null, 1, '保存失败！');
								}
								
							}
						*/			
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
				//$this->redirect('Admin-Index/menu', null, 1, '不存在该子集！');
			} else
			{
				$InfoClassNameClass = new Model();
				$InfoClassNameList = $InfoClassNameClass->query("SELECT ICLASSID AS CID,VCLASSNAME AS NAME,VCLASSLEVEL AS CL
							FROM THINK_TBINFOCLASSNAME");
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
			
			//log::write("InfoAdd icid=$icid id = $id name = $name");
			if (empty($id) || empty($icid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
			} else
			{
				$InfoClass = new Model();
				if(empty($len)) $len = 0;
				$sql = "SELECT IC.ICLASSID AS CID,IC.VID AS ID,
						IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
						IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
						IC.TCOMMENT AS CMT,IC.VREF AS REF
						FROM THINK_TBINFOCLASS IC
						where ICLASSID = '$icid' and VID = '$id'";
				$list = $InfoClass->query($sql);
				
				//log::write("InfoInsert -> ".count($list));
				
				if (count($list) > 0)
				{
					$url = "/Admin/Index/InfoClassIndex/icid/".$icid;
					$this->assign('jumpUrl',$url);
					$this->success('编辑项已存在,正在跳转 ...！');
					//$this->redirect($url, null, 1, '编辑项已存在！');
					return;
				}
				$sqlInsert = "INSERT INTO THINK_TBINFOCLASS(ICLASSID,VID,VNAME,VNAMECHN,VTYPE,ILENGTH,
						VSELECT,VVALUESCOPE,TCOMMENT,VREF )
						values('$icid','$id','$name','$cName','$type',$len,
						'$select','$scope','$comment','$ref')";
				if ($InfoClass->execute($sqlInsert))
				{
					//************************************************************
					$M = new Model();
					$sqlSelect = "SELECT ICLASSID,VCLASSNAME,VCLASSLEVEL 
							FROM THINK_TBINFOCLASSNAME where ICLASSID = '$icid' ";
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
					
					//log::write("InfoInsert -> insert success");
					$url = "/Admin/Index/InfoClassIndex/icid/$icid";
					//log::write("InfoInsert -> jump url".$url);
					$this->assign('jumpUrl',$url);
					//log::write("InfoInsert -> to jump ");
					$this->success('已保存更改,正在跳转 ...！');
					
					//log::write("InfoInsert -> jump success");
					return;
					//$this->redirect($url, null, 1, '已保存更改！');
				} else
				{
					log::write("InfoInsert -> insert failed");
					$url = "/Admin/Index/InfoClassIndex/icid/$icid";
					$this->assign('jumpUrl',$url);
					$this->success('保存失败,正在跳转 ...！');
					log::write("InfoInsert -> jump failed");
					//$this->redirect($url, null, 1, '保存失败！');
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
			
			log::write("InfoUpdate icid=$icid id = $id name = $name");
			if (empty($id) || empty($icid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
			} else
			{
				$InfoClass = new Model();
				$sql = "SELECT IC.ICLASSID AS CID,ICN.VCLASSNAME AS CLASSNAME,
						IC.VID AS ID,IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
						IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
						IC.TCOMMENT AS CMT,IC.VREF AS REF,IC.EDITRECORDID 
						FROM THINK_TBINFOCLASS IC,THINK_TBINFOCLASSNAME ICN
						WHERE IC.ICLASSID = '$icid' and IC.VID = '$id'
						AND IC.ICLASSID = ICN.ICLASSID";
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
				
				/***************************************
				//  将修改记录注释 oracle数据时使用这种sql写法
				if (C('DB_TYPE') == 'oracle') {
					$sqlInsert = "begin ";
				}
				///////////////////////////////////////*/	
				
				
				$bHasChanged = false;
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
					
					$M = new Model();
					//***************************************
					//  将修改记录注释 oracle数据时使用这种sql写法
					if (C('DB_TYPE') == 'oracle') {
						$sqlInsert = "begin ".$sqlInsert."  end;";
						if (!$M->execute($sqlInsert))
						{
							$state=false;
						}
					}
					////////////////////////////////////////	
					
					// sqlite使用不同的方法执行
					if (C('DB_TYPE') == 'pdo') {
						if (!empty($sqlInsert)) {
							$sqlArray = explode(';',$sqlInsert);
							//var_dump($sqlArray);
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
							FROM THINK_TBINFOCLASSNAME where ICLASSID = '$icid' ";
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
			//log::write("edit cid=$cid id = $id");
			if (empty($id) || empty($icid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassIndex');
				$this->success('编辑项目出错,正在跳转 ...！');
				//$this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
			} else
			{
				$icid=$this->checkUTF8($icid);
				$id=$this->checkUTF8($id);
				$InfoClassNameClass = new Model();
				$InfoClassNameList = $InfoClassNameClass->query("SELECT ICLASSID AS CID,VCLASSNAME AS NAME,VCLASSLEVEL AS CL
							FROM THINK_TBINFOCLASSNAME");
				$this->assign('InfoClassNameList', $InfoClassNameList);
				//$this->trace('edit codeCollectionlist', dump($CodeCollectionlist, false));
				
				$InfoClass = new Model();
				$sql = "SELECT IC.ICLASSID AS CID,IC.VID AS ID,
						IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
						IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
						IC.TCOMMENT AS CMT,IC.VREF AS REF
						FROM THINK_TBINFOCLASS IC
						WHERE ICLASSID = '$icid' and VID = '$id'";
				//log::write("edit sql: $sql");
				$list = $InfoClass->query($sql);
				if (count($list) <= 0)
				{
					$url = "/Admin/Index/InfoClassIndex";
					$this->assign('jumpUrl',$url);
					$this->success('该项不存在,正在跳转 ...！');
					//$this->redirect($url, null, 1, '编辑项不存在！');
					return;
				}
				$this->assign('InfoClass', $list[0]);
				//log::write("edit class: $list[0]");
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
			
			//log::write("InfoClassIndex() -> icid =" . $icid);
			if (!empty($icid))
			{
				$icid=$this->checkUTF8($icid);			
			} else
			{
				//$this->trace('Admin specialIndex', 'empty input');
				//如果传进来的icid为为空，则找一个现有icid跳转过去
				$M = new Model();
				//***************************************
				//  将修改记录注释 oracle数据时使用这种sql写法
				if (C('DB_TYPE') == 'oracle') {
					$sql = "SELECT ICLASSID,VCLASSNAME  
							FROM THINK_TBINFOCLASSNAME WHERE rownum <= 1";
				}
				////////////////////////////////////////
				else
				{
					$sql = "SELECT ICLASSID,VCLASSNAME  
							FROM THINK_TBINFOCLASSNAME";
				}
				
				$classCidList = $M->query($sql);
				if(count($classCidList)>0)
				{
					$icid = $classCidList[0]['ICLASSID'];
				}else
				{
					$this->assign('jumpUrl','/Admin/Index/welcome');
					$this->success('尚未建立任何子集,正在跳转 ...！');
					//$this->redirect('Admin-Index/menu', null, 1, '不存在该子集！');
					return;
				}
			}
			$Class = new Model();
			$sql = "SELECT IC.ICLASSID AS CID,ICN.VCLASSNAME AS CLASSNAME,
					IC.VID AS ID,IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
					IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
					IC.TCOMMENT AS CMT,IC.VREF AS REF,IC.EDITRECORDID 
					FROM THINK_TBINFOCLASS IC,THINK_TBINFOCLASSNAME ICN
					WHERE IC.ICLASSID = '$icid' and IC.ICLASSID = ICN.ICLASSID";
			$list = $Class->query($sql);
			$className = "";
			//log::write("InfoClassIndex() -> sql =" . $sql);
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
						FROM THINK_TBINFOCLASSNAME WHERE ICLASSID = '$icid'";
				$nameList=	$Class->query($sql);
				if (count($nameList)>0) {
					$className=$nameList[0]['VCLASSNAME'];
				}
				//$this->redirect('Admin-Index/menu', null, 1, '不存在该子集！');
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
			$InfoClassNameList = $InfoClassNameClass->query("SELECT ICLASSID AS CID,VCLASSNAME AS NAME,VCLASSLEVEL AS CL,TCOMMENT AS CMT
						FROM THINK_TBINFOCLASSNAME");
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
			if(empty($cid))
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('编辑信息子集异常,正在跳转 ...！');
				return;
			}
			$InfoClassNameClass = new Model();
			$InfoClassNameList = $InfoClassNameClass->query(
					"SELECT ICLASSID AS CID,VCLASSNAME AS NAME,VCLASSLEVEL AS CL,TCOMMENT AS CMT
						FROM THINK_TBINFOCLASSNAME where ICLASSID = '$cid'");
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
				//$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '编号未填写！');
				return;
			}
			$sqlSelect = "SELECT ICLASSID FROM THINK_TBINFOCLASSNAME WHERE ICLASSID = '$cid' ";
			$InfoClassNameClass = new Model();
			$list = $InfoClassNameClass->query($sqlSelect);
			if (count($list) > 0)
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('编号已存在,正在跳转 ...！');
				//$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '编号已存在！');
				return;
			}
			$sqlInsert = "INSERT INTO THINK_TBINFOCLASSNAME(ICLASSID,VCLASSNAME,VCLASSLEVEL,TCOMMENT) 
					values('$cid','$name','$cl','$cmt')";
			if ($InfoClassNameClass->execute($sqlInsert))
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('添加完成,正在跳转 ...！');
				//$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '添加完成！正在跳转...');
			} else
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('添加失败,正在跳转 ...！');
				//$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '添加失败！正在跳转...');
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
				//$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '该子集不存在！');
				return;
			}
			$cids=$this->checkUTF8($cids);
			//echo $cids;
			//return;
			$cidA = explode('?',$cids);
			$sqlExcute="";
			
			for($i=0;$i<count($cidA);$i++)
			{
				$cid=$cidA[$i];
				$sqlExcute.="DELETE FROM THINK_TBINFOCLASSNAME WHERE ICLASSID = '$cid';";
			}
			$state = true;
			
			//***************************************
			//  将修改记录注释 oracle数据时使用这种sql写法
			if (C('DB_TYPE') == 'oracle') {
				if (!empty($sqlExcute)) {
					$sqlExcute ="begin ".$sqlExcute." end;";	
				}
				$ConfigM = new Model();
				if (!empty($sqlExcute)) {
					if (!$ConfigM->execute($sqlExcute)) {
						$state=false;
					}
				}				
			}
			////////////////////////////////////////
			//******************************************
			// sqlite使用不同的方法执行
			//echo $sqlExcute."<br>";
			//TODO sqlite execute sqls
			if (C('DB_TYPE') == 'pdo') {
				if (!empty($sqlExcute)) {
					$sqlArray = explode(';',$sqlExcute);
					//var_dump($sqlArray);
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
			
			
			//$slqDelete = "DELETE FROM THINK_TBINFOCLASSNAME WHERE ICLASSID = '$cid'";
			//$InfoClassNameClass = new Model();
			//if ($InfoClassNameClass->execute($slqDelete))
			if($state)
			{
				echo "<script language='javascript' type='text/javascript'>";
				echo "parent.menu.location.reload()";
				echo "</script>";
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('删除成功,正在跳转 ...！');
				//$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '删除成功！正在跳转...');
			} else
			{
				$this->assign('jumpUrl','/Admin/Index/InfoClassNameIndex');
				$this->success('删除失败,正在跳转 ...！');
				
				//$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '删除失败！正在跳转...');
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
				//$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '该记录尚未修改过...');
				//$sql = "SELECT VTIME AS TM,VAUTHOR AS AUTHOR,TCONTENT AS CMT
				//	FROM THINK_TBEDITRECORD WHERE EDITID = 'nothing' ORDER BY TM DESC";
				$sql = "SELECT VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID
						FROM THINK_TBCHANGERECORD WHERE EDITID = 'nothing' ORDER BY VTIME DESC";
			} else
				if ($logID == "all")
				{
					//$sql = "SELECT VTIME AS TM,VAUTHOR AS AUTHOR,TCONTENT AS CMT
					//FROM THINK_TBEDITRECORD ORDER BY TM DESC";
					$sql = "SELECT VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID
							FROM THINK_TBCHANGERECORD ORDER BY VTIME DESC";
				} else
				{
					$sql = "SELECT VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID
							FROM THINK_TBCHANGERECORD WHERE EDITID = '" . $logID . "'  ORDER BY VTIME DESC";
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
					
					$sqlDelete .= "DELETE FROM THINK_TBCHANGERECORD WHERE VTIME='$time' and VAUTHOR='$author' 
							and VPARENTPAGE = '$parent' and VACTION = '$action' and VFIELDNAME = '$fieldname' 
							and ITEMID = '$itemid';";
				}
				
				
				
				$state=true;
				
				
				//***************************************
				//  将修改记录注释 oracle数据时使用这种sql写法
				if (C('DB_TYPE') == 'oracle') {
					$sqlDelete.="begin ".$sqlDelete." end;";	
					if (!$Class->execute($sqlDelete)) {
						$state=false;
					}
				}
				////////////////////////////////////////
				
				// sqlite使用不同的方法执行
				if (C('DB_TYPE') == 'pdo') {
					if (!empty($sqlDelete)) {
						$sqlArray = explode(';',$sqlDelete);
						//var_dump($sqlArray);
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
	/*
	public function ChangeLogDelete()
	{
		if ($this->checkRole())
		{
			$time = $_GET['tm'];
			$author = $_GET['author'];
			$parent = $_GET['prt'];
			$action = $_GET['act'];
			$fieldname = $_GET['fld'];
			$itemid = $_GET['itemid'];
			
			if (empty($time) || empty($author) || empty($parent)
					|| empty($action) || empty($fieldname) || empty($itemid))
			{
				$url = '/Admin/Index/ChangeLogIndex/id/all';
				$this->assign('jumpUrl',$url);
				$this->success('删除失败,正在跳转 ...！');
				//$this->redirect('Admin-Index/ChangeLogIndex/id/all', null, 1, '删除失败！正在跳转...');
			}
			$M = new Model();
			$sql = "DELETE FROM THINK_TBCHANGERECORD WHERE VTIME='$time' and VAUTHOR='$author' 
					and VPARENTPAGE = '$parent' and VACTION = '$action' and VFIELDNAME = '$fieldname' 
					and ITEMID = '$itemid'";
			if ($M->execute($sql))
			{
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
	*/
	public function CustomClassItemDelete()
	{
		if ($this->checkRole())
		{
			$name = $_GET['name'];
			if (empty($name))
			{
				$this->assign('jumpUrl','/Admin/Index/CustomClassIndex');
				$this->success('条目不存在,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassIndex", null, 1, '条目不存在...');
			}
			
			$M = new Model();
			$sql = "SELECT VCLASSNAME AS CLASSNAME, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
					,TCOMMENT AS CMT,EDITRECORDID 
					FROM THINK_TBCUSTOMITEMS WHERE VITEMNAME = '" . $name . "'";
			
			$list = $M->query($sql);
			if (count($list) <= 0)
			{
				$this->assign('jumpUrl','/Admin/Index/CustomClassIndex');
				$this->success('条目不存在,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassIndex", null, 1, '条目不存在...');
				return;
			}
			$ClassName = $list[0]['CLASSNAME'];
			$sqlDelete = "DELETE FROM THINK_TBCUSTOMITEMS WHERE VITEMNAME='$name'";
			if ($M->execute($sqlDelete))
			{
				$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('删除成功,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//    '删除成功...');
			} else
			{
				$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('删除失败,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//    '删除失败...');
			}
		}
	}
	public function CustomClassItemInsert()
	{
		if ($this->checkRole())
		{
			$name = trim($_POST['name']);
			$content = trim($_POST['content']);
			$cmt = trim($_POST['comment']);
			$ClassName = trim($_POST['className']);
			if (empty($name))
			{
				$url = "/Admin/Index/CustomClassItemAdd/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('条目名称必须填写,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassItemAdd/cn/" . $ClassName, null, 1,
				//     '条目名称必须填写...');
			}
			$sqlInsert = "INSERT INTO THINK_TBCUSTOMITEMS(VCLASSNAME,VITEMNAME,VITEMCONTENT,TCOMMENT) 
					values('" . $ClassName . "','" . $name . "','" . $content . "','" . $cmt .
				"')";
			//log::write("CustomClassItemInsert -> ".$sqlInsert);
			$M = new Model();
			if ($M->execute($sqlInsert))
			{
				$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('保存成功,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//    '保存成功...');
			} else
			{
				$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('已经定义过该条目,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//    '已经定义过该条目...');
			}
		}
	}
	public function CustomClassItemUpdate()
	{
		if ($this->checkRole())
		{
			$name = trim($_POST['name']);
			$content = trim($_POST['content']);
			$cmt = trim($_POST['comment']);
			$ClassName = trim($_POST['className']);
			if(empty($ClassName))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('编辑异常,正在跳转 ...！');
				
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//    '条目名称必须填写...');
				return;
				
			}
			if (empty($name))
			{
				$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('条目名称必须填写,正在跳转 ...！');
				
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//    '条目名称必须填写...');
				return;
			}
			
			$M = new Model();
			$sql = "SELECT VCLASSNAME AS CLASSNAME, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
					,TCOMMENT AS CMT,EDITRECORDID 
					FROM THINK_TBCUSTOMITEMS WHERE VITEMNAME = '" . $name . "'";
			
			//				$sql = "select collectionID,
			//			id, name,codeComment,editRecordID
			//		from think_tbCodeCollection  where id = '$id' and collectionID = '$cid'";
			$list = $M->query($sql);
			if (count($list) <= 0)
			{
				$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('编辑项不存在,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//    '编辑项不存在！');
				return;
			}
			$sqlUpdate = "UPDATE THINK_TBCUSTOMITEMS SET VITEMCONTENT ='" . $content .
				"',TCOMMENT='" . $cmt . "' 
					where VITEMNAME='" . $name . "'";
			//log::write("CustomClassItemUpdate: $sqlUpdate");
			
			/*
			*	产生修改记录
			*/
			//默认值
			$uniqueID = 0;
			
			if (empty($list[0]['EDITRECORDID']))
			{ // empty可以用来测试返回值是否为空
				//log::write("update() -> editRecordID is null");
				// 如果尚未有修改记录的编号
				$uniqueID = $this->GetUniqueID();
				$sqlUpdate = "UPDATE THINK_TBCUSTOMITEMS SET VITEMCONTENT ='" . $content .
					"',TCOMMENT='" . $cmt . "',
						EDITRECORDID = '$uniqueID' 
						where VITEMNAME='" . $name . "'";
			} else
			{
				$uniqueID = $list[0]['EDITRECORDID'];
			}
			//插入修改记录
			$sqlInsert = "begin ";
			$changeLog = "";
			$bHasChanged = false; 
			$vTime = date("Y-m-d H:i:s");
			$author = $_SESSION['nickname'];           
			$originalCollection = $list[0];
			if ($content != $originalCollection['CONTENT'])
			{
				$bHasChanged = true;
				
				$ocontent = $originalCollection['CONTENT'];
				$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
						VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$ClassName','$name','更新',
						'内容','$ocontent','$content','$uniqueID') ;";                
				//$changeLog = $changeLog . " 内容 由 " . $ocontent . "  改为 " . $content;
			}
			if ($cmt != $originalCollection['CMT'])
			{
				$bHasChanged = true;
				
				$oComment = $originalCollection['CMT'];
				$sqlInsert .= "insert into THINK_TBCHANGERECORD(VTIME,VAUTHOR,VPARENTPAGE,ITEMID,VACTION,
						VFIELDNAME,VOLDCONTENT,VNEWCONTENT,EDITID) values('$vTime','$author','$ClassName','$name','更新',
						'说明','$oComment','$cmt','$uniqueID') ;";  
				//$changeLog = $changeLog . " 说明 由 " . $oComment . "  改为 " . $cmt;
			}
			
			//if ($changeLog != "")
			if ($bHasChanged)
			{
				//log::write("update() -> cName" . $originalCollection['CNAME']);
				//$changeLog = "将 自定义类别 " . $ClassName . " 中名称为 " . $name . " 的记录的" . $changeLog;
				//log::write("update() -> " . $changeLog);
				date_default_timezone_set("Asia/Shanghai");
				$vTime = date("Y-m-d H:i:s");
				//$sqlLog = "INSERT INTO THINK_TBEDITRECORD(VTIME,VAUTHOR,TCONTENT,EDITID)
				//values('" . $vTime . "','" . $_SESSION['nickname'] . "','" . $changeLog . "','" . $uniqueID . "')";
				$M = new Model();
				$sqlInsert.=" end;";
				//if (!$M->execute($sqlLog))
				if (!$M->execute($sqlInsert))
				{
					$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
					$this->assign('jumpUrl',$url);
					$this->success('保存失败,正在跳转 ...！');
					//$this->redirect($url, null, 1, '保存失败！');
				}
			}
			
			if ($M->execute($sqlUpdate))
			{
				$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('保存成功,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//   '保存成功...');
			} else
			{
				$url = "/Admin/Index/CustomClassItemIndex/cn/" . $ClassName;
				$this->assign('jumpUrl',$url);
				$this->success('保存失败,正在跳转 ...！');
				//$this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
				//    '保存失败...');
			}
		}
	}
	public function CustomClassItemEdit()
	{
		if ($this->checkRole())
		{
			$itemName = $_GET['name'];
			if (empty($itemName))
			{
				$url = "/Admin/Index/CustomClassItemIndex";
				$this->assign('jumpUrl',$url);
				$this->success('不存在该类,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '不存在该类...');
				return;
			}
			$sql = "SELECT VCLASSNAME AS CLASSNAME, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
					,TCOMMENT AS CMT,EDITRECORDID 
					FROM THINK_TBCUSTOMITEMS WHERE VITEMNAME = '" . $itemName . "'";
			//log::write("CustomClassItemEdit -> " . $sql);
			$M = new Model();
			$list = $M->query($sql);
			if (count($list) > 0)
			{
				$this->assign('item', $list[0]);
				$this->assign('itemName', $list[0]['CLASSNAME']);
			}
			$this->display();
		}
	}
	public function CustomClassItemAdd()
	{
		if ($this->checkRole())
		{
			$ClassName = $_GET['cn'];
			if (empty($ClassName))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('不存在该类,正在跳转 ...！');
				// $this->redirect('Admin-Index/CustomClassIndex', null, 1, '不存在该类...');
				return;
			}
			$this->assign('itemName', $ClassName);
			$this->display();
		}
	}
	public function CustomClassItemIndex()
	{
		if ($this->checkRole())
		{
			$ClassName = $_GET['cn'];
			if (empty($ClassName))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('不存在该类,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '不存在该类...');
				return;
			}
			$sql = "SELECT VCLASSNAME AS CLASSNAME, VITEMNAME AS NAME,VITEMCONTENT AS CONTENT
					,TCOMMENT AS CMT,EDITRECORDID 
					FROM THINK_TBCUSTOMITEMS WHERE VCLASSNAME = '" . $ClassName . "'";
			
			//log::write("CustomClassItemIndex -> " . $sql);
			$M = new Model();
			$list = $M->query($sql);
			$this->assign('list', $list);
			$this->assign('itemName', $ClassName);
			$this->display();
		}
	}
	public function CustomClassIndex()
	{
		if ($this->checkRole())
		{
			//log::write("CustomClassIndex -> ");
			
			$M = new Model();
			$CustomClassNameList = $M->query("SELECT VNAME AS NAME,TCOMMENT AS CMT
						FROM THINK_TBCUSTOMCLASSES ");
			$this->assign('list', $CustomClassNameList);
			$this->display();
		}
	}
	public function CustomClassInsert()
	{
		if ($this->checkRole())
		{
			$name = trim($_POST['name']);
			$cmt = trim($_POST['comment']);
			if (empty($name))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('请添加有效名称后再保存,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '请添加有效名称后再保存...');
				return;
			}
			$sqlInsert = "INSERT INTO THINK_TBCUSTOMCLASSES(VNAME,TCOMMENT) VALUES('" . $name .
				"','" . $cmt . "')";
			//log::write("CustomClassAdd ->  $sqlInsert");
			
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
	public function CustomClassDelete()
	{
		if ($this->checkRole())
		{
			$name = $_GET['name'];
			if (empty($name))
			{
				$url = "/Admin/Index/CustomClassIndex";
				$this->assign('jumpUrl',$url);
				$this->success('删除失败,正在跳转 ...！');
				//$this->redirect('Admin-Index/CustomClassIndex', null, 1, '删除失败...');
			}
			$sqlDelete = "begin ";
			$sqlDelete .= "DELETE FROM THINK_TBCUSTOMCLASSES WHERE VNAME = '" . $name . "';";
			$sqlDelete .= "DELETE FROM THINK_TBCUSTOMITEMS WHERE VCLASSNAME = '" . $name . "';";
			
			$sqlDelete .=" end;";
			$M = new Model();
			if ($M->execute($sqlDelete))
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
