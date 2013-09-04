<?php

class DataImportAction extends Action {
	
	  public function checkRole()
    {
        if ($_SESSION['logined'] != 1)
        {
						$host = $_SERVER['HTTP_HOST'];
						echo "<script language='javascript' type='text/javascript'>";
						echo "top.location.href = 'http://".$host."/index.php/Welcome/logout'";
						echo "</script>";
            return;
				}
		if ($_SESSION['ROLE_NAME'] != "adminer")
        {
						$host = $_SERVER['HTTP_HOST'];
						echo "<script language='javascript' type='text/javascript'>";
						echo "top.location.href = 'http://".$host."/index.php/Welcome/logout'";
						echo "</script>";
            return ;
        }
        return true;
    }
	public function index() {
		if($this->checkRole())
		{
			$this->display();
		}
	}
	public function downloadTmplate()
	{
		$tmpType = $_GET['tmp'];
		//echo $tmpType;
		//return;
		
		$fileNameSystemBased = "";
		if($this->checkRole() && !empty($tmpType))
		{
			if ($tmpType == "codeCollectonName")//代码集名称
			{
				$fileNameSystemBased = "代码集名称导入-模板.xls";
			}
			if ($tmpType == "codeCollecton")//代码集基础数据
			{
				$fileNameSystemBased = "代码集基础数据导入-模板.xls";
			}
			if ($tmpType == "rule")//编码规则
			{
				$fileNameSystemBased = "基础数据编号规则-模板.xls";
			}
			if ($tmpType == "className")//数据子集名称
			{
				$fileNameSystemBased = "数据类导入-模板.xls";
			}
			if ($tmpType == "class")//数据子集
			{
				$fileNameSystemBased = "数据类基础数据导入-模板.xls";
			}
			$fileNameForPath = $fileNameSystemBased;
			$agent = $_SERVER['HTTP_USER_AGENT'];
		//如果客户端浏览器使用的是IE，则应该使用的windows系统，此时编码应该是gb2312
			if (eregi("MSIE",$agent))
			{
				if("GB2312" !=mb_detect_encoding($fileNameSystemBased))
				{
					$fileNameSystemBased =
							 mb_convert_encoding( $fileNameSystemBased,'GB2312','utf-8');
				} 
			}
			//如果部署在windows服务器上，需要转为GB2312，才能找到该文件
	    	if(eregi('WIN',PHP_OS))
			{
				if("GB2312" !=mb_detect_encoding($fileNameForPath))
				{
					$fileNameForPath = mb_convert_encoding( $fileNameForPath,'GB2312','utf-8');
				} 
			}
			else {
				if("UTF-8" !=mb_detect_encoding($fileNameForPath))
				{
					$fileNameForPath = mb_convert_encoding( $fileNameForPath,'UTF-8','GB2312');
				}
			}
			//echo $fileNameForPath;
			//return;
			
			//    	$fileName = mb_convert_encoding( $filename,'gb2312','utf-8'); 
			$path = C('TEMPLATE_FILE_PATH').$fileNameForPath;
			//echo $path;
			//return;
			
			//$path = "./Data/".$fileNameForPath;
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
					$this->assign('jumpUrl','/Admin/DataImport/index');
				    $this->success($filename."文件不存在,正在跳转 ...！");
				
				}	    	
			}
			else
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
			    $this->success($filename." 文件不存在,正在跳转 ...！");
			
			}
		}
	}
	/* TODO: Add code here */
	public function upload() {
		//var_dump($_FILES);
		//return;
		$filename =$_FILES["xlsfile"]["name"];
		if(!empty($filename))
		//if (!empty($_FILES)) 
		{
			//如果有文件上传 上传附件
			//$this->_upload();
			$operateType = $_GET['ot'];
			switch($operateType) {
				case 1://代码集基础数据
					$this->_importCodaCollection();
					break;
				case 2:
					$this->_importCodaCollectionName();
					break;
				case 3:
					$this->_importCodeRules();
					break;
				case 4:
					$this->_importInfoClassName();
					break;
				case 5:
					$this->_importInfoClass();
					break;
			}
			//$this->forward();
		}
		else
		{
			$this->assign('jumpUrl','/Admin/DataImport/index');
			$this->success('请先选择要导入的数据文件！');	
		}
	}
	protected function _importInfoClass()
	{
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200000;
		//设置附件上传目录
		$upload->savePath = './Public/Uploads/';
		//设置上传文件规则
		if (!$upload->upload()) {
			//捕获上传异常
			log::write('_upload error' );
			//$this->error($upload->getErrorMsg());
		} else
		{
			//			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			//log::write('_upload filename ='.$uploadList[0]['savename'] );
			//log::write('_upload savepath ='.$uploadList[0]['savepath'] );
			
			$InfoClass = new Model();
			//$InfoClass = M("tbInfoClass");
			Vendor("PHPExcel.PHPExcel");
			$objReader = new PHPExcel_Reader_Excel5();
			
			$filepathUtf8 = $uploadList[0]['savepath'].$uploadList[0]['savename'];
			//$filepath = mb_convert_encoding( $filepathUtf8,'gb2312','utf-8'); 
			if ($this->checkWindows()) {
				$filepath=$this->checkGB2312($filepathUtf8);
			}
			else
			{
				$filepath=$filepathUtf8;
			}
			$objPHPExcel = $objReader->load($filepath);
			$objPHPExcel->setActiveSheetIndex(0);
			$rowArray = $objPHPExcel->getActiveSheet()->getRowDimensions();
			$columnArray = $objPHPExcel->getActiveSheet()->getColumnDimensions();
			$rowCount = count($rowArray);
			$columnCount = count($columnArray);
			if ($columnCount < 9) {
				log::write('_upload $columnCount '.$columnCount);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$cell01 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,1)->getValue();
			$cell11 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,1)->getValue();
			$cell21 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,1)->getValue();
			$cell31 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,1)->getValue();
			$cell41 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4,1)->getValue();
			$cell51 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5,1)->getValue();
			$cell61 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(6,1)->getValue();
			$cell71 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7,1)->getValue();
			$cell81 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(8,1)->getValue();
			$cell91 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(9,1)->getValue();
			if (
				'编号' != $cell01 
					|| '数据项名' != $cell11
					|| '中文简称' != $cell21
					|| '类型' != $cell31
					|| '长度' != $cell41
					|| '可选' != $cell51
					|| '取值范围' != $cell61
					|| '说明' != $cell71
					|| '引用编号' != $cell81
					|| '所属子集' != $cell91
				) {
					log::write('cell01 '.$cell01.' cell11 = '.$cell11.
						' cell21 = '.$cell21.' cell31 = '.$cell31);
					//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
					$this->assign('jumpUrl','/Admin/DataImport/index');
					$this->success('文件上传有误！');
					return;
			}
			$sqlInsert = "";
			$insertCount = 0;

			for($rowIndex = 2; $rowIndex <= $rowCount; $rowIndex++)
			{
				$id = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,$rowIndex)->getValue();
				$name = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,$rowIndex)->getValue();
				$cName = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,$rowIndex)->getValue();
				$type = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,$rowIndex)->getValue();
				$len = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4,$rowIndex)->getValue();
				$select = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5,$rowIndex)->getValue();
				$scope = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(6,$rowIndex)->getValue();
				$comment = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7,$rowIndex)->getValue();
				$ref = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(8,$rowIndex)->getValue();
				$icid = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(9,$rowIndex)->getValue();
				if (!empty($id) && !empty($icid))
				{
					$sql = "SELECT IC.ICLASSID AS CID,IC.VID AS ID,
							IC.VNAME AS NAME,IC.VNAMECHN AS CNAME,
							IC.VTYPE,IC.ILENGTH AS LEN,IC.VSELECT,IC.VVALUESCOPE AS SCOPE,
							IC.TCOMMENT AS CMT,IC.VREF AS REF
							FROM THINK_TBINFOCLASS IC
							where ICLASSID = '$icid' and VID = '$id'";
					$list = $InfoClass->query($sql);
					if (count($list) <= 0)
					{
						$sqlInsert .= "insert into THINK_TBINFOCLASS(ICLASSID,VID,VNAME,VNAMECHN,VTYPE,ILENGTH,
								VSELECT,VVALUESCOPE,TCOMMENT,VREF )
								values('$icid','$id','$name','$cName','$type',$len,
								'$select','$scope','$comment','$ref');";
						$insertCount = $insertCount + 1;
								
					}
				}
			}
			if($insertCount <= 0)
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据已经导入！');
				return;				
			}		
			$state = true;
			//***************************************
			//  将修改记录注释 oracle数据时使用这种sql写法
			if (C('DB_TYPE') == 'oracle') {
				if (!empty($sqlInsert)) {
					$sqlInsert ="begin ".$sqlInsert." end;";	
				}
				$ConfigM = new Model();
				if (!empty($sqlInsert)) {
					if (!$ConfigM->execute($sqlInsert)) {
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
				if (!empty($sqlInsert)) {
					$sqlArray = explode(';',$sqlInsert);
					//var_dump($sqlArray);
					for($i=0;$i<count($sqlArray)-1;$i++)
					{
						$ConfigM = new Model();
						if (!$ConfigM->execute($sqlArray[$i])) {
							var_dump($sqlArray[$i]);
							return;
							$state=false;
							break;
						}
					}
				}					
			}
			
			if($state)
			//if ($InfoClass->execute($sqlInsert))
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据导入成功！');
				return;					
			} else
			{
				
				//$this->redirect('Admin-DataImport/index', null, 1, '数据添加失败！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据添加失败！');
				return;
			}			
			
			//$this->redirect('Admin-DataImport/index', null, 1, '数据导入成功！');

		}
	}
	protected function _importInfoClassName()
	{
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200000;
		//设置附件上传目录
		$upload->savePath = './Public/Uploads/';
		//设置上传文件规则
		if (!$upload->upload()) {
			//捕获上传异常
			log::write('_upload error' );
			//$this->error($upload->getErrorMsg());
		} else
		{
			//			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			//log::write('_upload filename ='.$uploadList[0]['savename'] );
			//log::write('_upload savepath ='.$uploadList[0]['savepath'] );
			
			//$InfoClassNameClass = M("tbInfoClassName");
			$InfoClassNameClass = new Model();
			
			Vendor("PHPExcel.PHPExcel");
			$objReader = new PHPExcel_Reader_Excel5();
			
			$filepathUtf8 = $uploadList[0]['savepath'].$uploadList[0]['savename'];
			//$filepath = mb_convert_encoding( $filepathUtf8,'gb2312','utf-8'); 
			if ($this->checkWindows()) {
				$filepath=$this->checkGB2312($filepathUtf8);
			}
			else
			{
				$filepath=$filepathUtf8;
			}
			$objPHPExcel = $objReader->load($filepath);
			$objPHPExcel->setActiveSheetIndex(0);
			$rowArray = $objPHPExcel->getActiveSheet()->getRowDimensions();
			$columnArray = $objPHPExcel->getActiveSheet()->getColumnDimensions();
			$rowCount = count($rowArray);
			$columnCount = count($columnArray);
			if ($columnCount < 3) {
				log::write('_upload $columnCount '.$columnCount);
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
			}
			$cell01 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,1)->getValue();
			$cell11 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,1)->getValue();
			$cell21 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,1)->getValue();
			$cell31 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,1)->getValue();
			if (
				'编号' != $cell01 
					|| '子类名称' != $cell11
					|| '所属类别' != $cell21
					|| '说明' != $cell31
				) {
				//log::write('cell01 '.$cell01.' cell11 = '.$cell11.
				//		' cell21 = '.$cell21.' cell31 = '.$cell31);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$sqlInsert = "";
			$insertCount = 0;

			for($rowIndex = 2; $rowIndex <= $rowCount; $rowIndex++)
			{
				$cid = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,$rowIndex)->getValue();
				$name = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,$rowIndex)->getValue();
				$cl = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,$rowIndex)->getValue();
				$cmt = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,$rowIndex)->getValue();
				if (!empty($cid))
				{
					$sqlSelect = "select ICLASSID from THINK_TBINFOCLASSNAME where ICLASSID = '$cid'";
					$list = $InfoClassNameClass->query($sqlSelect);
					if (count($list) <= 0)
					{
						$sqlInsert .= "insert into THINK_TBINFOCLASSNAME(ICLASSID,VCLASSNAME,VCLASSLEVEL,TCOMMENT) 
								values($cid,'$name','$cl','$cmt');";
						$insertCount = $insertCount + 1;

					}
					
				}
			}
			if($insertCount <= 0)
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据已经导入！');
				return;				
			}		
			
			$state = true;
			//***************************************
			//  将修改记录注释 oracle数据时使用这种sql写法
			if (C('DB_TYPE') == 'oracle') {
				if (!empty($sqlInsert)) {
					$sqlInsert ="begin ".$sqlInsert." end;";	
				}
				$ConfigM = new Model();
				if (!empty($sqlInsert)) {
					if (!$ConfigM->execute($sqlInsert)) {
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
				if (!empty($sqlInsert)) {
					$sqlArray = explode(';',$sqlInsert);
					//var_dump($sqlArray);
					for($i=0;$i<count($sqlArray)-1;$i++)
					{
						$ConfigM = new Model();
						if (!$ConfigM->execute($sqlArray[$i])) {
							var_dump($sqlArray[$i]);
							return;
							$state=false;
							break;
						}
					}
				}					
			}
			
			if($state)
			//if ($InfoClassNameClass->execute($sqlInsert))
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据导入成功！');
				return;				
			} else
			{
				//$this->redirect('Admin-DataImport/index', null, 1, '数据添加失败！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据添加失败！');
				return;
			}			
			//$this->redirect('Admin-DataImport/index', null, 1, '数据导入成功！');

		}
	}
	protected function _importCodeRules()
	{
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200000;
		//设置附件上传目录
		$upload->savePath = './Public/Uploads/';
		//设置上传文件规则
		if (!$upload->upload()) {
			//捕获上传异常
			log::write('_upload error' );
			//$this->error($upload->getErrorMsg());
		} else
		{
			//			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			//log::write('_upload filename ='.$uploadList[0]['savename'] );
			//log::write('_upload savepath ='.$uploadList[0]['savepath'] );
			
			$CodeRuleClass = new Model();
			//$CodeRuleClass = M("tbCodeRules");
			Vendor("PHPExcel.PHPExcel");
			$objReader = new PHPExcel_Reader_Excel5();
			
			$filepathUtf8 = $uploadList[0]['savepath'].$uploadList[0]['savename'];
			//$filepath = mb_convert_encoding( $filepathUtf8,'gb2312','utf-8'); 
			if ($this->checkWindows()) {
				$filepath=$this->checkGB2312($filepathUtf8);
			}
			else
			{
				$filepath=$filepathUtf8;
			}
			$objPHPExcel = $objReader->load($filepath);
			$objPHPExcel->setActiveSheetIndex(0);
			$rowArray = $objPHPExcel->getActiveSheet()->getRowDimensions();
			$columnArray = $objPHPExcel->getActiveSheet()->getColumnDimensions();
			$rowCount = count($rowArray);
			$columnCount = count($columnArray);
			if ($columnCount < 5) {
				log::write('_upload $columnCount '.$columnCount);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$cell01 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,1)->getValue();
			$cell11 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,1)->getValue();
			$cell21 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,1)->getValue();
			$cell31 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,1)->getValue();
			$cell41 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4,1)->getValue();
			$cell51 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5,1)->getValue();
			if (
				'编号' != $cell01 
					|| '数据项名' != $cell11
					|| '中文简称' != $cell21
					|| '类型' != $cell31
					|| '长度' != $cell41
					|| '说明' != $cell51
				) {
				log::write('cell01 '.$cell01.' cell11 = '.$cell11.
						' cell21 = '.$cell21.' cell31 = '.$cell31.
						' cell41 = '.$cell41.' cell51 = '.$cell51);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$sqlInsert = "";
			$insertCount = 0;

			for($rowIndex = 2; $rowIndex <= $rowCount; $rowIndex++)
			{
				$rid = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,$rowIndex)->getValue();
				$name = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,$rowIndex)->getValue();
				$cName = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,$rowIndex)->getValue();
				$type = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,$rowIndex)->getValue();
				$len = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4,$rowIndex)->getValue();
				$comment = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5,$rowIndex)->getValue();
				if (!empty($rid))
				{
					$sql = "SELECT VID as RULEID,VNAME as NAME,VNAMECHN as CNAME,VTYPE,NLENGTH as LEN,TCOMMENT as CMT
							FROM THINK_TBCODERULES where VID = '$rid'";
					$list = $CodeRuleClass->query($sql);
					if (count($list) <= 0)
					{
						$sqlInsert .= "insert into THINK_TBCODERULES ( VID,VNAME,VNAMECHN,
								VTYPE ,NLENGTH ,TCOMMENT)  values('$rid','$name','$cName', '$type', $len, '$comment');";
						$insertCount = $insertCount + 1;
							
					}
				}
			}
			if($insertCount <= 0)
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据已经导入！');
				return;				
			}		
			$state = true;
			//***************************************
			//  将修改记录注释 oracle数据时使用这种sql写法
			if (C('DB_TYPE') == 'oracle') {
				if (!empty($sqlInsert)) {
					$sqlInsert ="begin ".$sqlInsert." end;";	
				}
				$ConfigM = new Model();
				if (!empty($sqlInsert)) {
					if (!$ConfigM->execute($sqlInsert)) {
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
				if (!empty($sqlInsert)) {
					$sqlArray = explode(';',$sqlInsert);
					//var_dump($sqlArray);
					for($i=0;$i<count($sqlArray)-1;$i++)
					{
						$ConfigM = new Model();
						if (!$ConfigM->execute($sqlArray[$i])) {
							var_dump($sqlArray[$i]);
							return;
							$state=false;
							break;
						}
					}
				}					
			}
			
			if($state)
			//if ($CodeRuleClass->execute($sqlInsert))
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据导入成功！');
				return;				
			} else
			{
				//$this->redirect('Admin-DataImport/index', null, 1, '数据添加失败！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据添加失败！');
				return;
			}			
			
			//$this->redirect('Admin-DataImport/index', null, 1, '数据导入成功！');

		}
	}
	protected function _importCodaCollectionName()
	{
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200000;
		//设置附件上传目录
		$upload->savePath = './Public/Uploads/';
		//设置上传文件规则
		if (!$upload->upload()) {
			//捕获上传异常
			log::write('_upload error' );
			//$this->error($upload->getErrorMsg());
		} else
		{
			//			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			//log::write('_upload filename ='.$uploadList[0]['savename'] );
			//log::write('_upload savepath ='.$uploadList[0]['savepath'] );
			
			$InfoCollectionNameClass = new Model();
			//$InfoCollectionNameClass = M("tbCodeCollectionName");
			Vendor("PHPExcel.PHPExcel");
			$objReader = new PHPExcel_Reader_Excel5();
			
			$filepathUtf8 = $uploadList[0]['savepath'].$uploadList[0]['savename'];
			//$filepath = mb_convert_encoding( $filepathUtf8,'gb2312','utf-8'); 
			if ($this->checkWindows()) {
				$filepath=$this->checkGB2312($filepathUtf8);
			}
			else
			{
				$filepath=$filepathUtf8;
			}
			//echo $filepath;
			//return;
			
			$objPHPExcel = $objReader->load($filepath);
			$rowArray = $objPHPExcel->getActiveSheet()->getRowDimensions();
			$columnArray = $objPHPExcel->getActiveSheet()->getColumnDimensions();
			$rowCount = count($rowArray);
			$columnCount = count($columnArray);
			if ($columnCount < 3) {
				log::write('_upload $columnCount '.$columnCount);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
							$this->success('文件上传有误！');
							return;
			}
			$cell01 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,1)->getValue();
			$cell11 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,1)->getValue();
			$cell21 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,1)->getValue();
			$cell31 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,1)->getValue();
			if (
				'代码集ID' != $cell01 
					|| '代码集名称' != $cell11
					|| '代码集说明' != $cell21
					|| '代码集类别' != $cell31
				) {
				log::write('cell01 '.$cell01.' cell11 = '.$cell11.' cell21 = '.$cell21.' cell31 = '.$cell31);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$sqlInsert = "";
			$insertCount = 0;

			for($rowIndex = 2; $rowIndex <= $rowCount; $rowIndex++)
			{
				$cid = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,$rowIndex)->getValue();
				$name = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,$rowIndex)->getValue();
				$cl = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,$rowIndex)->getValue();
				$cmt = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,$rowIndex)->getValue();
				if (!empty($cid))
				{
					$sqlSelect = "select COLLECTIONID from THINK_TBCODECOLLECTIONNAME where COLLECTIONID = '$cid' ";
					$list = $InfoCollectionNameClass->query($sqlSelect);
					if (count($list) <= 0)
					{
						$sqlInsert .= "insert into THINK_TBCODECOLLECTIONNAME(COLLECTIONID,COLLECTIONNAME,COLLECTIONLEVEL,COLLECTIONCOMMENT) 
								values('$cid','$name','$cl','$cmt');";
						$insertCount = $insertCount + 1;
												
					}
				}
			}
			if($insertCount <= 0)
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据已经导入！');
				return;				
			}		
			//echo $sqlInsert;
			//return;
			
			$state = true;
			//***************************************
			//  将修改记录注释 oracle数据时使用这种sql写法
			if (C('DB_TYPE') == 'oracle') {
				if (!empty($sqlInsert)) {
					$sqlInsert ="begin ".$sqlInsert." end;";	
				}
				$ConfigM = new Model();
				if (!empty($sqlInsert)) {
					if (!$ConfigM->execute($sqlInsert)) {
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
				if (!empty($sqlInsert)) {
					$sqlArray = explode(';',$sqlInsert);
					//var_dump($sqlArray);
					for($i=0;$i<count($sqlArray)-1;$i++)
					{
						$ConfigM = new Model();
						if (!$ConfigM->execute($sqlArray[$i])) {
							var_dump($sqlArray[$i]);
							return;
							$state=false;
							break;
						}
					}
				}					
			}
			
			if($state)
			//if ($InfoCollectionNameClass->execute($sqlInsert))
			{
					$this->assign('jumpUrl','/Admin/DataImport/index');
					$this->success('数据导入成功！');
					return;				
			} else
			{
				//$this->redirect('Admin-DataImport/index', null, 1, '数据添加失败！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据添加失败！');
				return;
			}		
			//$this->redirect('Admin-DataImport/index', null, 1, '数据导入成功！');

		}
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
	
	
	
	/**
	 * This is method _importCodaCollection
	 *
	 * @return mixed This is the return value description
	 *
	protected function _importCodaCollection()
	{
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200;
		//设置附件上传目录
		$upload->savePath = './Public/Uploads/';
		//设置上传文件规则
		if (!$upload->upload()) {
			//捕获上传异常
			log::write('_upload error' );
			//$this->error($upload->getErrorMsg());
		} else
		{
			//			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			//log::write('_upload filename ='.$uploadList[0]['savename'] );
			//log::write('_upload savepath ='.$uploadList[0]['savepath'] );
			
			$Class = new Model();
			//$Class = M("tbCodeCollection");
			Vendor("PHPExcel.PHPExcel");
			$objReader = new PHPExcel_Reader_Excel5();
			
			$filepathUtf8 = $uploadList[0]['savepath'].$uploadList[0]['savename'];
			//echo $filepathUtf8."   1111";
			if ($this->checkWindows()) {
				$filepath=$this->checkGB2312($filepathUtf8);
			}
			else
			{
				$filepath=$filepathUtf8;
			}
			//return;
			//$filepath=$filepathUtf8;
			//$filepath = mb_convert_encoding( $filepathUtf8,'gb2312','utf-8'); 
			//echo $filepath;
			//return;
			
			$objPHPExcel = $objReader->load($filepath);
			$rowArray = $objPHPExcel->getActiveSheet()->getRowDimensions();
			$columnArray = $objPHPExcel->getActiveSheet()->getColumnDimensions();
			$rowCount = count($rowArray);
			$columnCount = count($columnArray);
			if ($columnCount < 6) {
				log::write('_upload $columnCount '.$columnCount);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$cell01 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,1)->getValue();
			$cell11 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,1)->getValue();
			$cell21 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,1)->getValue();
			$cell31 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,1)->getValue();
			$cell41 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4,1)->getValue();
			$cell51 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5,1)->getValue();
			$cell61 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(6,1)->getValue();
			if (
				'代码' != $cell01 
					|| '名称' != $cell11
					|| '描述' != $cell21
					|| '代码集' != $cell31
					|| '代码集名称' != $cell41
					|| '上级代码' != $cell51
					|| '代码集类别' != $cell61
				) {
				log::write('cell01 '.$cell01.' cell11 = '.$cell11.' cell21 = '.$cell21.' cell31 = '.$cell31);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$insertCount = 0;
			$sqlExcute="";
			$collectionNameList = array();
			for($rowIndex = 2; $rowIndex <= $rowCount; $rowIndex++)
			{
				$id = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,$rowIndex)->getValue();
				$name = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,$rowIndex)->getValue();
				$cid = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,$rowIndex)->getValue();
				$comment = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,$rowIndex)->getValue();
				$cidName = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4,$rowIndex)->getValue();
				$upnodeID = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5,$rowIndex)->getValue();
				$cidLevel = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(6,$rowIndex)->getValue();
				if (empty($id)|| empty($name)||empty($cid)||empty($cidName)) {
					continue;
				}
				//$MCName = new Model();
				//$sqlS = "select COLLECTIONID from THINK_TBCODECOLLECTIONNAME where COLLECTIONID = '$cid' and COLLECTIONLEVEL = '$cidLevel'";
				//echo $sqlS;
				//return;
				
				//$list=$MCName->query($sqlS);
					
				//if(count($list)<=0)
				if(!in_array($cidName,$collectionNameList))
				{
					$sqlExcute.= "insert into THINK_TBCODECOLLECTIONNAME
							(COLLECTIONID,COLLECTIONNAME,COLLECTIONLEVEL,COLLECTIONCOMMENT) 
							values('$cid','$cidName','$cidLevel','');";
					$collectionNameList[]=$cidName;
					$insertCount++;
				}
				//$MC = new Model();
				//$lt = $MC->query("select * from THINK_TBCODECOLLECTION where COLLECTIONID = '$cid' and ID = '$id'");
				//if (count($lt)<=0) {
				$sqlExcute .= "insert into THINK_TBCODECOLLECTION(COLLECTIONID,ID,NAME,CODECOMMENT,UPNODEID)
						values('$cid','$id','$name','$comment','$upnodeID');";
					//$insertCount++;
				//}
			}
			//echo $sqlExcute;
			//return;
			
			if($insertCount <= 0)
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据已经导入！');
				return;				
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
							var_dump($sqlArray[$i]);
							return;
							$state=false;
							break;
						}
					}
				}					
			}
		//	$sqlInsert = "begin ";			
		//	$sqlInsert .= "  end;";
		//	log::write("111111 -> insert sql   ".$sqlInsert);
			//if ($Class->execute($sqlInsert))
			if ($state)
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据导入成功！');
				return;				
			} else
			{
				//$this->redirect('Admin-DataImport/index', null, 1, '数据添加失败！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据添加失败！');
				return;
			}			
		}
	}
	 */
	protected function _importCodaCollection()
	{
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200;
		//设置附件上传目录
		$upload->savePath = './Public/Uploads/';
		//设置上传文件规则
		if (!$upload->upload()) {
			//捕获上传异常
			log::write('_upload error' );
			//$this->error($upload->getErrorMsg());
		} else
		{
			//			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			//log::write('_upload filename ='.$uploadList[0]['savename'] );
			//log::write('_upload savepath ='.$uploadList[0]['savepath'] );
			
			$Class = new Model();
			//$Class = M("tbCodeCollection");
			Vendor("PHPExcel.PHPExcel");
			$objReader = new PHPExcel_Reader_Excel5();
			
			$filepathUtf8 = $uploadList[0]['savepath'].$uploadList[0]['savename'];
			//$filepath = mb_convert_encoding( $filepathUtf8,'gb2312','utf-8'); 
			if ($this->checkWindows()) {
				$filepath=$this->checkGB2312($filepathUtf8);
			}
			else
			{
				$filepath=$filepathUtf8;
			}
			$objPHPExcel = $objReader->load($filepath);
			$rowArray = $objPHPExcel->getActiveSheet()->getRowDimensions();
			$columnArray = $objPHPExcel->getActiveSheet()->getColumnDimensions();
			$rowCount = count($rowArray);
			$columnCount = count($columnArray);
			if ($columnCount < 3) {
				log::write('_upload $columnCount '.$columnCount);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$cell01 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,1)->getValue();
			$cell11 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,1)->getValue();
			$cell21 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,1)->getValue();
			$cell31 = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,1)->getValue();
			if (
				'代码' != $cell01 
					|| '名称' != $cell11
					|| '描述' != $cell21
					||  !eregi('代码集',$cell31)
				) {
				
				//|| '所属代码集' != $cell31
				log::write('cell01 '.$cell01.' cell11 = '.$cell11.' cell21 = '.$cell21.' cell31 = '.$cell31);
				//$this->redirect('Admin-DataImport/index', null, 1, '文件上传有误！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('文件上传有误！');
				return;
			}
			$insertCount = 0;
			$sqlInsert = "";			
			for($rowIndex = 2; $rowIndex <= $rowCount; $rowIndex++)
			{
				$id = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0,$rowIndex)->getValue();
				$name = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,$rowIndex)->getValue();
				$cid = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,$rowIndex)->getValue();
				$comment = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2,$rowIndex)->getValue();
				$upnodeID = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5,$rowIndex)->getValue();
				if (!empty($name) && !empty($cid))
				{
					$sql = "select COLLECTIONID as CID,
							ID, NAME,CODECOMMENT AS CMT
							from THINK_TBCODECOLLECTION  where ID = '$id' and COLLECTIONID = '$cid'";
					$list = $Class->query($sql);
					if (count($list) <= 0)
					{
						$sqlInsert .= "insert into THINK_TBCODECOLLECTION(COLLECTIONID,ID,NAME,CODECOMMENT,UPNODEID)
								values('$cid','$id','$name','$comment','$upnodeID');";
						$insertCount = $insertCount + 1;
					}
					
				}
			}
			//echo $sqlInsert;
			//return;
			
			//log::write("111111 -> insert sql   ".$sqlInsert);
			if($insertCount <= 0)
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据已经导入！');
				return;				
			}		
			$state = true;
			//***************************************
			//  将修改记录注释 oracle数据时使用这种sql写法
			if (C('DB_TYPE') == 'oracle') {
				if (!empty($sqlInsert)) {
					$sqlInsert ="begin ".$sqlInsert." end;";	
				}
				$ConfigM = new Model();
				if (!empty($sqlInsert)) {
					if (!$ConfigM->execute($sqlInsert)) {
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
				if (!empty($sqlInsert)) {
					$sqlArray = explode(';',$sqlInsert);
					//var_dump($sqlArray);
					
					$ConfigM = new Model();
					for($i=0;$i<count($sqlArray)-1;$i++)
					{
						if (!$ConfigM->execute($sqlArray[$i])) {
							var_dump($sqlArray[$i]);
							return;
							$state=false;
							break;
						}
					}
				}					
			}
			
			if($state)
			//if ($Class->execute($sqlInsert))
			{
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据导入成功！');
				return;				
			} else
			{
				//$this->redirect('Admin-DataImport/index', null, 1, '数据添加失败！');
				$this->assign('jumpUrl','/Admin/DataImport/index');
				$this->success('数据添加失败！');
				return;
			}			
			//$this->redirect('Admin-DataImport/index', null, 1, '数据导入成功！');

		}
	}
	// 文件上传
	protected function _upload() {
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize = 3292200;
		//设置上传文件类型
		//$upload->allowExts = explode(',', 'xsl');
		//设置附件上传目录
		$upload->savePath = './Public/Uploads/';
		//设置上传文件规则
		//$upload->saveRule = uniqid;
		if (!$upload->upload()) {
			//捕获上传异常
			log::write('_upload error' );
			//$this->error($upload->getErrorMsg());
		} else
		{
			//			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo();
			log::write('_upload filename ='.$uploadList[0]['savename'] );
			log::write('_upload savepath ='.$uploadList[0]['savepath'] );
			
			Vendor("PHPExcel.PHPExcel");
			$objReader = new PHPExcel_Reader_Excel5();
			//log::write(dump(iconv_get_encoding('all'),false));
			//$files1 = scandir('./Public/Uploads');
			//			for($i=0;$i<count($files1);$i++)
			//			{
			//				log::write('_upload file dir '. $i .' '.$files1[$i]);
			//			}
			$filepathUtf8 = $uploadList[0]['savepath'].$uploadList[0]['savename'];
			$filepath = mb_convert_encoding( $filepathUtf8,'gb2312','utf-8'); 
			//			log::write('_upload filepath '.$filepath );
			//			
			//			if(file_exists($filepath))
			//			{
			//				
			//				log::write('_upload filepath '.$filepath.' exists' );
			//			}
			//			else
			//			{
			//				log::write('_upload filepath '.$filepath.' not exists' );
			//				
			//			}
			//			if(!is_readable($filepath))
			//			{
			//				log::write('_upload filepath '.$filepath.' not readable' );
			//				return;
			//			}
			//			else
			//			{
			//				log::write('_upload filepath '.$filepath.' readable' );
			//			}
			
			//log::write('_upload savePath '.$uploadList[0]['savepath'].$uploadList[0]['savename']);
			$objPHPExcel = $objReader->load($filepath);
			$objPHPExcel->setActiveSheetIndex(0);
			$rowArray = $objPHPExcel->getActiveSheet()->getRowDimensions();
			$columnArray = $objPHPExcel->getActiveSheet()->getColumnDimensions();
			$rowCount = count($rowArray);
			$columnCount = count($columnArray);
			for($rowIndex = 1; $rowIndex <= $rowCount; $rowIndex++)
			{
				for($columnIndex = 0; $columnIndex <= $columnCount; $columnIndex++)
				{
					$a1Value = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columnIndex,$rowIndex)->getValue();
					log::write('_upload row '.$rowIndex.' column '.$columnIndex.' value = '.$a1Value );
				}
			}
			//$a1Value = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1,2)->getValue();
			//$a1utfV = iconv('utf-8','gb2312', $a1Value); 
			//$cellValue = mb_convert_encoding($a1Value,'utf-8','gbk');
			//$a1utfV = iconv('gb2312','utf-8', $a1Value); 
			//log::write('_upload a1 value '.$a1Value );
			
		}
		
	}
	
}
?>
