<?php

//class IndexAction extends Action{
class IndexAction extends LoginCommonAction
{
    public $UserName = "unknown";

    public function exportInfoClassData()
    {
        $cid = $_GET['cid'];
        if (empty($cid))
            return;
        $Class = M("tbInfoClass");
	/*
	// mysql version
        $sql = "SELECT ic.iClassID as cid,icn.vClassName as className,
				ic.vId as id,ic.vName as name,ic.vNamechn as cName,
				ic.vType,ic.iLength as len,ic.vSelect,ic.vValueScope as scope,
				ic.tComment as cmt,ic.vRef as ref
				FROM think_tbInfoClass as ic,think_tbInfoClassName as icn
				where ic.iClassID = '$cid' and ic.iClassID = icn.iClassID";
	*/
	// oracle vesion
        $sql = "SELECT \"think_tbInfoClass\".\"iClassID\" as \"cid\",\"think_tbInfoClassName\".\"vClassName\" as \"className\",
				\"think_tbInfoClass\".\"vId\" as \"id\",\"think_tbInfoClass\".\"vName\" as \"name\",\"think_tbInfoClass\".\"vNamechn\" as \"cName\",
				\"think_tbInfoClass\".\"vType\",\"think_tbInfoClass\".\"iLength\" as \"len\",\"think_tbInfoClass\".\"vSelect\",\"think_tbInfoClass\".\"vValueScope\" as \"scope\",
				\"think_tbInfoClass\".\"tComment\" as \"cmt\",\"think_tbInfoClass\".\"vRef\" as \"ref\"
				FROM \"think_tbInfoClass\" ,\"think_tbInfoClassName\" 
				where \"think_tbInfoClass\".\"iClassID\" = '$cid' and \"think_tbInfoClass\".\"iClassID\" = \"think_tbInfoClassName\".\"iClassID\"";
        $list = $Class->query($sql);
        Vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '编号')->setCellValue('B1',
            '数据项名')->setCellValue('c1', '中文简称')->setCellValue('d1', '类型')->setCellValue('e1',
            '长度')->setCellValue('f1', '可选')->setCellValue('g1', '取值范围')->setCellValue('h1',
            '说明')->setCellValue('i1', '引用编号');

        $codeCollectionName = $list[0]['className'];
        for ($i = 0; $i < count($list); $i++)
        {
            $rowN = $i + 2;
            $id = $list[$i]['id'];
            $name = $list[$i]['name'];
            $cName = $list[$i]['cName'];
            $vType = $list[$i]['vType'];
            $len = $list[$i]['len'];
            $vSelect = $list[$i]['vSelect'];
            $scope = $list[$i]['scope'];
            $cmt = $list[$i]['cmt'];
            $ref = $list[$i]['ref'];
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowN", "$id")->
                setCellValue("b$rowN", $name)->setCellValue("c$rowN", $cName)->setCellValue("d$rowN",
                $vType)->setCellValue("e$rowN", $len)->setCellValue("f$rowN", $vSelect)->
                setCellValue("g$rowN", $scope)->setCellValue("h$rowN", $cmt)->setCellValue("i$rowN",
                $ref);
            //log::write("exportData id=$id ");
        }
        $objPHPExcel->getActiveSheet()->setTitle($codeCollectionName);

        $objPHPExcel->setActiveSheetIndex(0);
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
        $Class = M("tbCodeRules");
	/*
	// mysql version
        $sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,nLength as len,tComment as cmt
				FROM think_tbCodeRules";
	*/
	// oracle version
        $sql = "SELECT \"vID\" as \"ruleID\",\"vName\" as \"name\",\"vNamechn\" as \"cName\",\"vType\",\"nLength\" as \"len\",\"tComment\" as \"cmt\"
				FROM \"think_tbCodeRules\"";
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
            $id = $list[$i]['ruleID'];
            $name = $list[$i]['name'];
            $cName = $list[$i]['cName'];
            $vType = $list[$i]['vType'];
            $len = $list[$i]['len'];
            $cmt = $list[$i]['cmt'];
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowN", "$id")->
                setCellValue("b$rowN", $name)->setCellValue("c$rowN", $cName)->setCellValue("d$rowN",
                $vType)->setCellValue("e$rowN", $len)->setCellValue("f$rowN", $cmt);
            //log::write("exportData id=$id ");
        }
        $objPHPExcel->getActiveSheet()->setTitle($codeCollectionName);

        $objPHPExcel->setActiveSheetIndex(0);
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
        $Class = M("tbCodeCollection");
        Vendor("PHPExcel.PHPExcel");
        $cid = $_GET['cid'];
        if (empty($cid))
            return;

        $codeCollectionName = "";
	/*
	// mysql version
        $sql = "select cc.collectionID as cid
				,ccn.collectionName as cName,
				id, name,codeComment as cmt
				from think_tbCodeCollection as cc,
				think_tbCodeCollectionName as ccn
				where cc.collectionID = '$cid' and
				cc.collectionID = ccn.collectionID";
	*/
	// oracle version
        $sql = "select \"think_tbCodeCollection\".\"collectionID\" as \"cid\"
				,\"think_tbCodeCollectionName\".\"collectionName\" as \"cName\",
				\"id\", \"name\",\"codeComment\" as \"cmt\"
				from \"think_tbCodeCollection\" ,
				\"think_tbCodeCollectionName\" 
				where \"think_tbCodeCollection\".\"collectionID\" = '$cid' and
				\"think_tbCodeCollection\".\"collectionID\" = \"think_tbCodeCollectionName\".\"collectionID\"";
        $list = $Class->query($sql);
        if (count($list) > 0)
        {
            $codeCollectionName = $list[0]['cName'];


            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '代码')->setCellValue('B1',
                '名称')->setCellValue('c1', '描述')->setCellValue('d1', '所属代码集');
            for ($i = 0; $i < count($list); $i++)
            {
                $rowN = $i + 2;
                $id = $list[$i]['id'];
                $name = $list[$i]['name'];
                $cmt = $list[$i]['cmt'];
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowN", "$id")->
                    setCellValue("b$rowN", $name)->setCellValue("c$rowN", $cmt)->setCellValue("d$rowN",
                    $codeCollectionName);
                //log::write("exportData id=$id ");
            }
            $objPHPExcel->getActiveSheet()->setTitle($codeCollectionName);

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
            $this->redirect('Admin-Index/menu', null, 1, '代码集不存在！');
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
                    $Class = M("tbCodeCollection");
/*
// mysql version
                    $sql = "select cc.collectionID as cid
							,ccn.collectionName as cName,
							id, name,codeComment as cmt
							from think_tbCodeCollection as cc,
							think_tbCodeCollectionName as ccn
							where cc.name like '%$keyword%' or
							cc.codeComment like '%$keyword%'
							group by cc.collectionID,id";
*/
// oracle version
                    $sql = "select \"think_tbCodeCollection\".\"collectionID\" as \"cid\"
							,\"think_tbCodeCollectionName\".\"collectionName\" as \"cName\",
							\"id\", \"name\",\"codeComment\" as \"cmt\"
							from \"think_tbCodeCollection\" ,
							\"think_tbCodeCollectionName\" 
							where \"think_tbCodeCollection\".\"name\" like '%$keyword%' or
							\"think_tbCodeCollection\".\"codeComment\" like '%$keyword%'
							group by \"think_tbCodeCollection\".\"collectionID\",\"id\"";
                    $list = $Class->query($sql);
                    $this->assign('list', $list);
                    $this->display('specialIndex');
                    break;
                case 2:
                    $Class = M("tbCodeRules");
/*

                    $sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,
							nLength as len,tComment as cmt
							FROM think_tbCodeRules
							where vID like '%$keyword%'
							or vName like '%$keyword%'
							or vNamechn like '%$keyword%'
							or tComment like '%$keyword%'";
*/
                    $sql = "SELECT \"vID\" as \"ruleID\",\"vName\" as \"name\",\"vNamechn\" as \"cName\",\"vType\",
							\"nLength\" as \"len\",\"tComment\" as \"cmt\"
							FROM \"think_tbCodeRules\"
							where \"vID\" like '%$keyword%'
							or \"vName\" like '%$keyword%'
							or \"vNamechn\" like '%$keyword%'
							or \"tComment\" like '%$keyword%'";
                    $list = $Class->query($sql);
                    $this->assign('ruleList', $list);
                    $this->display('ruleIndex');
                    break;
                case 3:
                    $Class = M("tbInfoClass");
/*
                    $sql = "SELECT ic.iClassID as cid,icn.vClassName as className,
							ic.vId as id,ic.vName as name,ic.vNamechn as cName,
							ic.vType,ic.iLength as len,ic.vSelect,ic.vValueScope as scope,
							ic.tComment as cmt,ic.vRef as ref
							FROM think_tbInfoClass as ic,think_tbInfoClassName as icn
							where ic.vId like '%$keyword%'
							or ic.vName like '%$keyword%' or ic.vNamechn like '%$keyword%'
							or ic.tComment like '%$keyword%'
							group by ic.iClassID,ic.vId";
*/
                    $sql = "SELECT \"think_tbInfoClass\".\"iClassID\" as \"cid\",\"think_tbInfoClassName\".\"vClassName\" as \"className\",
							\"think_tbInfoClass\".\"vId\" as \"id\",\"think_tbInfoClass\".\"vName\" as \"name\",\"think_tbInfoClass\".\"vNamechn\" as \"cName\",
							\"think_tbInfoClass\".\"vType\",\"think_tbInfoClass\".\"iLength\" as \"len\",\"think_tbInfoClass\".\"vSelect\",\"think_tbInfoClass\".\"vValueScope\" as \"scope\",
							\"think_tbInfoClass\".\"tComment\" as \"cmt\",\"think_tbInfoClass\".\"vRef\" as \"ref\"
							FROM \"think_tbInfoClass\" ,\"think_tbInfoClassName\" 
							where \"think_tbInfoClass\".\"vId\" like '%$keyword%'
							or \"think_tbInfoClass\".\"vName\" like '%$keyword%' or \"think_tbInfoClass\".\"vNamechn\" like '%$keyword%'
							or \"think_tbInfoClass\".\"tComment\" like '%$keyword%'
							group by \"think_tbInfoClass\".\"iClassID\",\"think_tbInfoClass\".\"vId\"";
                    $list = $Class->query($sql);
                    $this->assign('list', $list);
                    $this->display('InfoClassIndex');
                    break;
                case 4:
                    $M = new Model();
/*
                    $sql = "SELECT vTime as tm,vAuthor as author,tContent as cmt
							FROM think_tbEditRecord
							where vAuthor like '%$keyword%' or  tContent like '%$keyword%'
							order by tm desc";
*/
                    $sql = "SELECT \"vTime\" as \"tm\",\"vAuthor\" as \"author\",\"tContent\" as \"cmt\"
							FROM \"think_tbEditRecord\"
							where \"vAuthor\" like '%$keyword%' or  \"tContent\" like '%$keyword%'
							order by \"tm\" desc";
                    $logList = $M->query($sql);
                    $this->assign('list', $logList);
                    $this->display('ChangeLogIndex');
                    break;
                case 5:
/*
                    $sql = "SELECT vClassName as className, vItemName as name,
							vItemContent as content,tComment as cmt,editRecordID 
							FROM think_tbCustomItems
							where vItemName like '%$keyword%' 
							or vItemContent like '%$keyword%'
							or tComment like '%$keyword%'";
*/
                    $sql = "SELECT \"vClassName\" as \"className\", \"vItemName\" as \"name\",
							\"vItemContent\" as \"content\",\"tComment\" as \"cmt\",\"editRecordID\" 
							FROM \"think_tbCustomItems\"
							where \"vItemName\" like '%$keyword%' 
							or \"vItemContent\" like '%$keyword%'
							or \"tComment\" like '%$keyword%'";

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
            $Class = M("tbCodeCollectionName");
	    // mysql version
            //$levelList = $Class->query("select distinct collectionlevel as cl from think_tbCodeCollectionName");
	   // oracle version
            $levelList = $Class->query("select distinct \"collectionlevel\" as \"cl\" from \"think_tbCodeCollectionName\"");
            //$this->trace('menu levelList ', dump($levelList, false));
            $this->assign('levelList', $levelList);
	   // mysql
            //$list = $Class->query("select collectionID as id,collectionName as name
            //,collectionlevel as cl from think_tbCodeCollectionName");
		// oracle
            $list = $Class->query("select \"collectionID\" as \"id\",\"collectionName\" as \"name\"
            ,\"collectionlevel\" as \"cl\" from \"think_tbCodeCollectionName\"");
            //$this->trace('menu List ', dump($list, false));
            $this->assign('codeCollectionlist', $list);

            $CodeRulesClass = M("tbCodeRules");
		// mysql
            //$codeRulesList = $CodeRulesClass->query("select vID as id,vName as name,
                                    //vNamechn as showName,vType as tp,nLength as len,tComment as cmt
						//from think_tbCodeRules");
// oracle
            $codeRulesList = $CodeRulesClass->query("select \"vID\" as \"id\",\"vName\" as \"name\",
                                    \"vNamechn\" as \"showName\",\"vType\" as \"tp\",\"nLength\" as \"len\",\"tComment\" as \"cmt\"
						from \"think_tbCodeRules\"");
            $this->assign('rulesList', $codeRulesList);

            $InfoClassNameClass = M("tbInfoClassName");
/*
            $InfoClassNameLevelList = $InfoClassNameClass->query("select distinct vClasslevel as cl
                                                             from think_tbInfoClassName");
*/
		// oracle
            $InfoClassNameLevelList = $InfoClassNameClass->query("select distinct \"vClasslevel\" as \"cl\"
                                                             from \"think_tbInfoClassName\"");
            $this->assign('InfoClassNamelevelList', $InfoClassNameLevelList);
/*
            $InfoClassNameList = $InfoClassNameClass->query("select iClassID as cid,vClassName as name
                                          ,vClasslevel as cl from think_tbInfoClassName");
*/
            $InfoClassNameList = $InfoClassNameClass->query("select \"iClassID\" as \"cid\",\"vClassName\" as \"name\"
                                          ,\"vClasslevel\" as \"cl\" from \"think_tbInfoClassName\"");
            $this->assign('InfoClassNameList', $InfoClassNameList);

            $M = new Model();
/*
            $CustomClassNameList = $M->query("SELECT vName as name,tComment as cmt
						FROM think_tbCustomClasses ");
*/
            $CustomClassNameList = $M->query("SELECT \"vName\" as \"name\",\"tComment\" as \"cmt\"
						FROM \"think_tbCustomClasses\" ");
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
            $this->redirect('Home-Welcome/welcome', null, 1, '正在跳转到登录页面...');
            return;
        }
        if ($_SESSION['role_name'] != "editor")
        {
            $this->redirect('Home-Welcome/welcome', null, 1, '您没有权利登录此页面，正在跳转到登录页面...');
            return;
        }
        if ($this->UserName == "unknown")
        {
            //			Vendor("CAS.CAS");
            //			phpCAS::setDebug();
            //
            //			//// 初始化phpcas
            //			////设定服务地址,端口号,CAS的访问地址
            //			phpCAS::client(CAS_VERSION_2_0,'211.68.70.17',6001,'sso');
            //			////phpCAS::client(CAS_VERSION_2_0,'211.68.70.15',7001,'sso');
            //
            //			//// 不使用SSL服务校验
            //			phpCAS::setNoCasServerValidation();
            //
            //			//// 访问CAS的验证
            //			phpCAS::forceAuthentication();
            //
            //			////这时候就验证完毕了
            //			////获得用户名可以通过phpCAS::getUser()
            //			////phpCAS::getUser();
            //			$arrUserDetails = phpCAS::getUserDetails();
            //			// log::write("checkRole userName ".$this->UserName);
            //			$this->UserName = $arrUserDetails['userName'];
            //			// log::write("checkRole userName ".$this->UserName);
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
/*
                $sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,nLength as len
					,tComment as cmt,editRecordID 
					FROM think_tbCodeRules where vID = '$rid'";
*/
                $sql = "SELECT \"vID\" as \"ruleID\",\"vName\" as \"name\",\"vNamechn\" as \"cName\",\"vType\",\"nLength\" as \"len\"
					,\"tComment\" as \"cmt\",\"editRecordID\" 
					FROM \"think_tbCodeRules\" where \"vID\" = '$rid'";
            } else
            {
/*
                $sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,nLength as len
					,tComment as cmt,editRecordID 
					FROM think_tbCodeRules";
*/
                $sql = "SELECT \"vID\" as \"ruleID\",\"vName\" as \"name\",\"vNamechn\" as \"cName\",\"vType\",\"nLength\" as \"len\"
					,\"tComment\" as \"cmt\",\"editRecordID\" 
					FROM \"think_tbCodeRules\"";
                //$this->redirect('Admin-Index/menu', null, 1, '不存在该编码规则！');
                //return;
            }
            $Class = M("tbCodeRules");

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
            log::write("ruleEdit rid=$rid ");
            if (empty($rid))
            {
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $CodeRuleClass = M("tbCodeRules");
/*
                $sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,nLength as len
                        ,tComment as cmt  FROM think_tbCodeRules where vID = '$rid'";
*/
                $sql = "SELECT \"vID\" as \"ruleID\",\"vName\" as \"name\",\"vNamechn\" as \"cName\",\"vType\",\"nLength\" as \"len\"
                        ,\"tComment\" as \"cmt\"  FROM \"think_tbCodeRules\" where \"vID\" = '$rid'";
                //log::write("edit sql: $sql");
                $list = $CodeRuleClass->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/menu";
                    $this->redirect($url, null, 1, '编辑项不存在！');
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
            $rid = $_POST['rid'];
            $name = $_POST['name'];
            $cName = $_POST['cName'];
            $type = $_POST['vType'];
            $len = $_POST['len'];
            $comment = $_POST['comment'];
            //log::write("update cid=$cid id = $id name = $name");
            if (empty($rid))
            {
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $CodeRuleClass = M("tbCodeRules");
                //$sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,nLength as len
                //,tComment as cmt
                //						FROM think_tbCodeRules where vID = '$rid'";
/*
                $sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,nLength as len
						,tComment as cmt,editRecordID 
						FROM think_tbCodeRules where vID = '$rid'";
*/
                $sql = "SELECT \"vID\" as \"ruleID\",\"vName\" as \"name\",\"vNamechn\" as \"cName\",\"vType\",\"nLength\" as \"len\"
						,\"tComment\" as \"cmt\",\"editRecordID\" 
						FROM \"think_tbCodeRules\" where \"vID\" = '$rid'";
                $list = $CodeRuleClass->query($sql);
                if (count($list) <= 0)
                {
                    $url = 'Admin-Index/menu';
                    $this->redirect($url, null, 1, '编辑项不存在！');
                    return;
                }


                /*
                *	产生修改记录
                */
                //默认值
/*
                $sqlUpdate = "update think_tbCodeRules set vName='$name',vNamechn = '$cName',
						vType = '$type',nLength = $len,tComment = '$comment'
						where vID = '$rid'";
*/
                $sqlUpdate = "update \"think_tbCodeRules\" set \"vName\"='$name',\"vNamechn\" = '$cName',
						\"vType\" = '$type',\"nLength\" = $len,\"tComment\" = '$comment'
						where \"vID\" = '$rid'";
                $uniqueID = 0;

                if (empty($list[0]['editRecordID']))
                { // empty可以用来测试返回值是否为空
                    //log::write("update() -> editRecordID is null");
                    // 如果尚未有修改记录的编号
                    $uniqueID = $this->GetUniqueID();
/*
                    $sqlUpdate = "update think_tbCodeRules set vName='$name',vNamechn = '$cName',
							vType = '$type',nLength = $len,tComment = '$comment',
							editRecordID = '$uniqueID' 
							where vID = '$rid'";
*/
                    $sqlUpdate = "update \"think_tbCodeRules\" set \"vName\"='$name',\"vNamechn\" = '$cName',
							\"vType\" = '$type',\"nLength\" = $len,\"tComment\" = '$comment',
							\"editRecordID\" = '$uniqueID' 
							where \"vID\" = '$rid'";

                } else
                {
                    $uniqueID = $list[0]['editRecordID'];
                }
                //插入修改记录
                $changeLog = "";
                $originalCollection = $list[0];
                if ($name != $originalCollection['name'])
                {
                    $oName = $originalCollection['name'];
                    $changeLog = $changeLog . " 数据项名 由 " . $oName . "  改为 " . $name;
                }
                if ($cName != $originalCollection['cName'])
                {
                    $ocName = $originalCollection['cName'];
                    $changeLog = $changeLog . " 中文简称 由 " . $ocName . "  改为 " . $cName;
                }
                if ($type != $originalCollection['vType'])
                {
                    $otype = $originalCollection['vType'];
                    $changeLog = $changeLog . " 类型 由 " . $otype . "  改为 " . $type;
                }
                if ($len != $originalCollection['len'])
                {
                    $olen = $originalCollection['len'];
                    $changeLog = $changeLog . " 长度 由 " . $olen . "  改为 " . $len;
                }
                if ($comment != $originalCollection['cmt'])
                {
                    $oComment = $originalCollection['cmt'];
                    $changeLog = $changeLog . " 说明 由 " . $oComment . "  改为 " . $comment;
                }

                if ($changeLog != "")
                {
                    log::write("update() -> cName" . $originalCollection['cName']);
                    $changeLog = "将 编码规则 中编号为 " . $originalCollection['ruleID'] . " 的记录的" . $changeLog;
                    date_default_timezone_set("Asia/Shanghai");
                    $vTime = date("Y-m-d H:i:s");

/*

                    $sqlLog = "insert into think_tbEditRecord(vTime,vAuthor,tContent,editID)
							values('" . $vTime . "','" . $this->UserName . "','" . $changeLog . "','" .
                        $uniqueID . "')";
*/
                    $sqlLog = "insert into \"think_tbEditRecord\"(\"vTime\",\"vAuthor\",\"tContent\",\"editID\")
							values('" . $vTime . "','" . $this->UserName . "','" . $changeLog . "','" .
                        $uniqueID . "')";
                    $M = new Model();
                    if (!$M->execute($sqlLog))
                    {
                        $url = "Admin-Index/specialIndex/cid/$cid";
                        $this->redirect($url, null, 1, '保存失败！');
                    }
                }

                if ($CodeRuleClass->execute($sqlUpdate))
                {
                    $url = "Admin-Index/ruleIndex/ruleID/$rid";
                    $this->redirect($url, null, 1, '已保存更改！');
                } else
                {
                    $url = "Admin-Index/ruleIndex/ruleID/$rid";
                    $this->redirect($url, null, 1, '保存失败！');
                }
            }
        }
    }
    public function ruleDelete()
    {
        if ($this->checkRole())
        {
            $rid = $_GET['ruleID']; // code id
            //log::write("delete cid=$cid id = $id");
            if (empty($rid))
            {
                $this->redirect('Admin-Index/menu', null, 1, '删除项目出错！');
            } else
            {
                $CodeRuleClass = M("tbCodeRules");
/*
                $sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,nLength as len
                        ,tComment as cmt
						FROM think_tbCodeRules where vID = '$rid'";
*/
                $sql = "SELECT \"vID\" as \"ruleID\",\"vName\" as \"name\",\"vNamechn\" as \"cName\",\"vType\",\"nLength\" as \"len\"
                        ,\"tComment\" as \"cmt\"
						FROM \"think_tbCodeRules\" where \"vID\" = '$rid'";
                $list = $CodeRuleClass->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/menu";
                    $this->redirect($url, null, 1, '该项不存在！');
                    return;
                }
/*
                $sqlDelete = "delete from think_tbCodeRules  where vID = '$rid'";
*/
                $sqlDelete = "delete from \"think_tbCodeRules\"  where \"vID\" = '$rid'";
                $CodeRuleClass->execute($sqlDelete);
                $url = "Admin-Index/menu";
                $this->redirect($url, null, 1, '删除成功！');
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
            $rid = $_POST['rid'];
            $name = $_POST['name'];
            $cName = $_POST['cName'];
            $type = $_POST['vType'];
            $len = $_POST['len'];
            $comment = $_POST['comment'];
            //log::write("update cid=$cid id = $id name = $name");
            if (empty($rid))
            {
                $this->redirect('Admin-Index/menu', null, 1, '添加项目出错！');
            } else
            {
                $CodeRuleClass = M("tbCodeRules");
/*
                $sql = "SELECT vID as ruleID,vName as name,vNamechn as cName,vType,nLength as len
                ,tComment as cmt
						FROM think_tbCodeRules where vID = '$rid'";
*/
                $sql = "SELECT vID as \"ruleID\",\"vName\" as \"name\",\"vNamechn\" as \"cName\",\"vType\",\"nLength\" as \"len\"
                ,\"tComment\" as \"cmt\"
						FROM \"think_tbCodeRules\" where \"vID\" = '$rid'";
                $list = $CodeRuleClass->query($sql);
                if (count($list) > 0)
                {
                    $url = 'Admin-Index/menu';
                    $this->redirect($url, null, 1, '该项已存在！');
                    return;
                }
/*
                $sqlInsert = "insert into think_tbCodeRules ( vID,vName,vNamechn,
					vType ,nLength ,tComment)
                                values('$rid','$name','$cName', '$type', $len, '$comment')";
*/
                $sqlInsert = "insert into \"think_tbCodeRules\" ( \"vID\",\"vName\",\"vNamechn\",
					\"vType\" ,\"nLength\" ,\"tComment\")
                                values('$rid','$name','$cName', '$type', $len, '$comment')";
                if ($CodeRuleClass->execute($sqlInsert))
                {
                    $url = "Admin-Index/ruleIndex/ruleID/$rid";
                    $this->redirect($url, null, 1, '已保存！');
                } else
                {
                    $url = "Admin-Index/ruleIndex/ruleID/$rid";
                    $this->redirect($url, null, 1, '保存失败！');
                }
            }
        }
    }
    //查询属于某表的所有字段
    public function specialIndex()
    {
        if ($this->checkRole())
        {
            $cnt = 0;
            $cid = $_GET['cid']; //collection id
            if (!empty($cid))
            {
                //$this->trace('Admin specialIndex ' + $cnt, dump($cid, false));
                $cnt++;
            } else
            {
                //$this->trace('Admin specialIndex', 'empty input');
                $this->redirect('Admin-Index/menu', null, 1, '不存在该代码集！');
                return;
            }
            $Class = M("tbCodeCollection");
/*
            $slqHierarchi = "SELECT id,upnodeID
                              FROM think_tbcodecollection where collectionID = '$cid'
                               and upnodeID  in (select id from think_tbcodecollection) ";
            $sql = "select cc.collectionID as cid
					,ccn.collectionName as cName,
					id, name,codeComment as cmt,cc.editRecordID,cc.upnodeID  
					from think_tbCodeCollection as cc,
					think_tbCodeCollectionName as ccn
					where cc.collectionID = '$cid' and
					cc.collectionID = ccn.collectionID";
*/
            $slqHierarchi = "SELECT \"id\",\"upnodeID\"
                              FROM \"think_tbcodecollection\" where \"collectionID\" = '$cid'
                               and \"upnodeID\"  in (select \"id\" from \"think_tbcodecollection\") ";

            $sql = "select \"think_tbCodeCollection\".\"collectionID\" as \"cid\"
					,\"think_tbCodeCollectionName\".\"collectionName\" as \"cName\",
					\"id\", \"name\",\"codeComment\" as \"cmt\",\"think_tbCodeCollection\".\"editRecordID\",\"think_tbCodeCollection\".\"upnodeID\"  
					from \"think_tbCodeCollection\" ,
					\"think_tbCodeCollectionName\" 
					where \"think_tbCodeCollection\".\"collectionID\" = '$cid' and
					\"think_tbCodeCollection\".\"collectionID\" = \"think_tbCodeCollectionName\".\"collectionID\"";
            $hieList = $Class->query($slqHierarchi);
            $list = $Class->query($sql);
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
                    $item = new CodeCollectionlItem($vo['name'], $vo['id'], $vo['upnodeID'], $vo['cid'],
                        $vo['cName'], $vo['cmt'], $vo['editRecordID']);
                    $collection->add($item);
                }
                //                echo "->startIndex";
                //                echo ("<br>");
                $collection->startIndex();
                $rownums = $collection->getIndexedRowNumbers();
                $this->assign('maps', $rownums);
                $array = $collection->getIndexedArray();
                //var_dump($array);
                $this->assign('list', $array);
                $this->assign('cltInfo', $array[0]);
                //                $this->assign('list', $list);
                //                $this->assign('cltInfo', $list[0]);
                $this->display();
                //  echo $rownums;
                //  echo ("<br>");
                //                echo "->outputIndexedItems";
                //                echo ("<br>");
                //                $collection->outputIndexedItems();

            } else
            {
                $this->assign('bHierarchi', 0);

                //var_dump($list);
                //                if (count($list) > 0)
                //                {
                $this->assign('list', $list);
                $this->assign('cltInfo', $list[0]);
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
    public function specialIndexPara($tbln)
    {
        $Class = M("Class");
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
            $cid = $_GET['cid'];
            if (empty($cid))
            {
                $this->redirect('Admin-Index/CollectionNameEdit', null, 1, '该代码集不存在！');
                return;
            }
/*
            $slqDelete = "delete from think_tbCodeCollectionName where collectionID = '$cid'";
*/
            $slqDelete = "delete from \"think_tbCodeCollectionName\" where \"collectionID\" = '$cid'";
            $InfoCollectionNameClass = M("think_tbCodeCollectionName");
            if ($InfoCollectionNameClass->execute($slqDelete))
            {
                $this->redirect('Admin-Index/CollectionNameEdit', null, 1, '删除成功！正在跳转...');
            } else
            {

                $this->redirect('Admin-Index/CollectionNameEdit', null, 1, '删除失败！正在跳转...');
            }
        }
    }
    function CollectionNameInsert()
    {
        if ($this->checkRole())
        {
            $cid = $_POST['cid'];
            $name = $_POST['name'];
            $cl = $_POST['level'];
            $cmt = $_POST['comment'];

            if (empty($cid))
            {
                $this->redirect('Admin-Index/CollectionNameEdit', null, 1, '编号未填写！');
                return;
            }
/*
            $sqlSelect = "select collectionID from think_tbCodeCollectionName where collectionID = '$cid' ";
*/
            $sqlSelect = "select \"collectionID\" from \"think_tbCodeCollectionName\" where \"collectionID\" = '$cid' ";
            $InfoCollectionNameClass = M("tbCodeCollectionName");
            $list = $InfoCollectionNameClass->query($sqlSelect);
            if (count($list) > 0)
            {
                $this->redirect('Admin-Index/CollectionNameEdit', null, 1, '编号已存在！');
                return;
            }
/*
            $sqlInsert = "insert into think_tbCodeCollectionName(collectionID,collectionName,
            collectionlevel,collectionComment) 
				values('$cid','$name','$cl','$cmt')";
*/
            $sqlInsert = "insert into \"think_tbCodeCollectionName\"(\"collectionID\",\"collectionName\",
            \"collectionlevel\",\"collectionComment\") 
				values('$cid','$name','$cl','$cmt')";
            if ($InfoCollectionNameClass->execute($sqlInsert))
            {
                echo "<script language='javascript' type='text/javascript'>";
                echo "parent.menu.location.reload()";
                echo "</script>";
                $this->redirect('Admin-Index/CollectionNameEdit', null, 1, '添加完成！正在跳转...');
            } else
            {
                $this->redirect('Admin-Index/CollectionNameEdit', null, 1, '添加失败！正在跳转...');
            }
        }
    }
    function CollectionNameEdit()
    {
        if ($this->checkRole())
        {
            $Class = M("tbCodeCollectionName");
/*
            $sql = "SELECT collectionID as cid,collectionName as name,
				collectionComment as cmt,collectionlevel as cl
				FROM think_tbCodeCollectionName";
*/
            $sql = "SELECT \"collectionID\" as \"cid\",\"collectionName\" as \"name\",
				\"collectionComment\" as \"cmt\",\"collectionlevel\" as \"cl\"
				FROM \"think_tbCodeCollectionName\"";
            $list = $Class->query($sql);
            $this->assign('codeCollectionNameList', $list);
            $this->display();
        }
    }
    //增加新项时
    public function add()
    {
        if ($this->checkRole())
        {
            $cid = $_GET['cid'];
            if (empty($cid))
            {
                //$this->redirect('Admin-Index/menu', null, 1, '添加项目出错！');
                $cid = -1;
            }
            $Class = M("tbCodeCollectionName");
/*
            $sql = "select collectionID as cid,collectionName as name,collectionlevel as cl
					from think_tbCodeCollectionName";
*/
            $sql = "select \"collectionID\" as \"cid\",\"collectionName\" as \"name\",\"collectionlevel\" as \"cl\"
					from \"think_tbCodeCollectionName\"";
            $list = $Class->query($sql);
            if (count($list) <= 0)
            {
                $this->redirect('Admin-Index/menu', null, 1, '不存在要添加的代码集！');
            }
/*
            $sqlNameList = "select collectionID as cid,
						id, name,codeComment as cmt,upnodeID
						from think_tbCodeCollection  where collectionID = '$cid'";
*/
            $sqlNameList = "select \"collectionID\" as \"cid\",
						\"id\", \"name\",\"codeComment\" as \"cmt\",\"upnodeID\"
						from \"think_tbCodeCollection\"  where \"collectionID\" = '$cid'";
            $NameList = $Class->query($sqlNameList);
            $this->assign('NameList', $NameList);
            //$this->trace('menu List ', dump($list, false));
            $this->assign('codeCollectionList', $list);
            $this->assign('selectedCid', $cid);
            $this->display();
        }
    }
    // 处理表单数据
    public function insert()
    {
        if ($this->checkRole())
        {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $cid = $_POST['cid'];
            $upnodeID = $_POST['upnodeID'];
            $comment = $_POST['comment'];
            //$this->trace('add name', $name);
            if (empty($name) || empty($cid))
            {
                $this->redirect('Admin-Index/add', null, 1, '数据填写不完全！');
            } else
            {
                $Class = M("tbCodeCollection");
/*
                $sql = "select collectionID as cid,
						id, name,codeComment as cmt
						from think_tbCodeCollection  where id = '$id' and collectionID = '$cid'";
*/
                $sql = "select \"collectionID\" as \"cid\",
						\"id\", \"name\",\"codeComment\" as \"cmt\"
						from \"think_tbCodeCollection\"  where \"id\" = '$id' and \"collectionID\" = '$cid'";
                $list = $Class->query($sql);
                if (count($list) > 0)
                {
                    $this->redirect('Admin-Index/add', null, 1, '该项目已经添加！');
                    return;
                } else
                {
/*
                    $sql = "insert into think_tbCodeCollection(collectionID,id,name,codeComment,upnodeID)
							values('$cid','$id','$name','$comment','$upnodeID') ";
*/
                    $sql = "insert into \"think_tbCodeCollection\"(\"collectionID\",\"id\",\"name\",\"codeComment\",\"upnodeID\")
							values('$cid','$id','$name','$comment','$upnodeID') ";
                    if ($Class->execute($sql))
                    {
                        $url = "Admin-Index/specialIndex/cid/$cid";
                        $this->redirect($url, null, 1, '数据添加成功！');
                        return;
                    } else
                    {
                        $this->redirect('Admin-Index/add', null, 1, '数据添加异常！');
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
            log::write("edit cid=$cid id = $id");
            if ((empty($id) || empty($cid)) && $id != '0' && $cid != '0')
            {
                log::write("edit empty cid=$cid id = $id");
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $CodeCollectionNameClass = M("tbCodeCollectionName");
/*
                $CodeCollectionlist = $CodeCollectionNameClass->query("select collectionID as cid,collectionName as name
							,collectionlevel as cl
							from think_tbCodeCollectionName");
*/
                $CodeCollectionlist = $CodeCollectionNameClass->query("select \"collectionID\" as \"cid\",\"collectionName\" as \"name\"
							,\"collectionlevel\" as \"cl\"
							from \"think_tbCodeCollectionName\"");
                $this->assign('codeCollectionlist', $CodeCollectionlist);
                //$this->trace('edit codeCollectionlist', dump($CodeCollectionlist, false));

                $CodeCollectionClass = M("tbCodeCollection");
/*
                $sql = "select collectionID as cid,
						id, name,codeComment as cmt,upnodeID
						from think_tbCodeCollection  where id = '$id' and collectionID = '$cid'";
*/
                $sql = "select \"collectionID\" as \"cid\",
						\"id\", \"name\",\"codeComment\" as \"cmt\",\"upnodeID\"
						from \"think_tbCodeCollection\"  where \"id\" = '$id' and \"collectionID\" = '$cid'";
                //log::write("edit sql: $sql");
                $list = $CodeCollectionClass->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/menu";
                    $this->redirect($url, null, 1, '编辑项不存在！');
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
/*
                $sqlNameList = "select collectionID as cid,
						id, name,codeComment as cmt,upnodeID
						from think_tbCodeCollection  where collectionID = '$cid'";
*/
                $sqlNameList = "select \"collectionID\" as \"cid\",
						\"id\", \"name\",\"codeComment\" as \"cmt\",\"upnodeID\"
						from \"think_tbCodeCollection\"  where \"collectionID\" = '$cid'";
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
            $cid = $_GET['cid']; //collection id
            $id = $_GET['id']; // code id
            log::write("delete cid=$cid id = $id");
            if (empty($id) || empty($cid))
            {
                $this->redirect('Admin-Index/menu', null, 1, '删除项目出错！');
            } else
            {
                $Class = D("tbCodeCollection");
/*
                $sql = "select collectionID,
						id, name
						from think_tbCodeCollection  where id = '$id' and collectionID = '$cid'";
*/
                $sql = "select \"collectionID\",
						\"id\", \"name\"
						from \"think_tbCodeCollection\"  where \"id\" = '$id' and \"collectionID\" = '$cid'";
                $list = $Class->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/specialIndex/cid/$cid";
                    $this->redirect($url, null, 1, '该项不存在！');
                    return;
                }
/*
                $sqlDelete = "delete from think_tbCodeCollection  where id = '$id' and collectionID = '$cid'";
*/
                $sqlDelete = "delete from \"think_tbCodeCollection\"  where \"id\" = '$id' and \"collectionID\" = '$cid'";
                $Class->execute($sqlDelete);
                $url = "Admin-Index/specialIndex/cid/$cid";
                $this->redirect($url, null, 1, '删除成功！');
            }
        }
    }

    // 更新数据
    public function update()
    {
        if ($this->checkRole())
        {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $cid = $_POST['cid'];
            $upnodeID = $_POST['upnodeID'];
            $comment = $_POST['comment'];
            //log::write("update cid=$cid id = $id name = $name");
            if (empty($id) || empty($cid) || empty($name))
            {
                log::write("update() -> 1");
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $CodeCollectionClass = M("tbCodeCollection");
/*
                $sql = "select cc.collectionID as cid
						,ccn.collectionName as cName,
						id, name,codeComment as cmt,cc.editRecordID 
						from think_tbCodeCollection as cc,
						think_tbCodeCollectionName as ccn
						where cc.collectionID = '$cid' and cc.id = '$id' and 
						cc.collectionID = ccn.collectionID";
*/
                $sql = "select \"think_tbCodeCollection\".\"collectionID\" as \"cid\"
						,\"think_tbCodeCollectionName\".\"collectionName\" as \"cName\",
						\"id\", \"name\",\"codeComment\" as \"cmt\",\"think_tbCodeCollection\".\"editRecordID\" 
						from \"think_tbCodeCollection\" ,
						\"think_tbCodeCollectionName\" 
						where \"think_tbCodeCollection\".\"collectionID\" = '$cid' and \"think_tbCodeCollection\".\"id\" = '$id' and 
						\"think_tbCodeCollection\".\"collectionID\" = \"think_tbCodeCollectionName\".\"collectionID\"";

                //				$sql = "select collectionID,
                //id, name,codeComment,editRecordID
                //from think_tbCodeCollection  where id = '$id' and collectionID = '$cid'";
                $list = $CodeCollectionClass->query($sql);
                if (count($list) <= 0)
                {
                    $url = 'Admin-Index/menu';
                    $this->redirect($url, null, 1, '编辑项不存在！');
                    return;
                }
                /*
                *	产生修改记录
                */
                //默认值
/*
                $sqlUpdate = "update think_tbCodeCollection set name='$name',codeComment = '$comment',upnodeID 
                              = '$upnodeID'
						where id = '$id'  and collectionID = '$cid'";
*/
                $sqlUpdate = "update \"think_tbCodeCollection\" set \"name\"='$name',\"codeComment\" = '$comment',\"upnodeID\" 
                              = '$upnodeID'
						where \"id\" = '$id'  and \"collectionID\" = '$cid'";
                $uniqueID = 0;

                if (empty($list[0]['editRecordID']))
                { // empty可以用来测试返回值是否为空
                    //log::write("update() -> editRecordID is null");
                    // 如果尚未有修改记录的编号
                    $uniqueID = $this->GetUniqueID();
                    log::write("update() -> 4");
/*
                    $sqlUpdate = "update think_tbCodeCollection set name='$name',codeComment = '$comment',
							editRecordID = '$uniqueID' 
							where id = '$id'  and collectionID = '$cid'";
*/
                    $sqlUpdate = "update \"think_tbCodeCollection\" set \"name\"='$name',\"codeComment\" = '$comment',
							\"editRecordID\" = '$uniqueID' 
							where \"id\" = '$id'  and \"collectionID\" = '$cid'";


                } else
                {
                    log::write("update() -> editRecordID = " . $list[0]['editRecordID']);
                    $uniqueID = $list[0]['editRecordID'];
                }
                //插入修改记录
                $changeLog = "";
                $originalCollection = $list[0];
                if ($name != $originalCollection['name'])
                {
                    $oName = $originalCollection['name'];
                    $changeLog = $changeLog . " 字段名称由 " . $oName . "  改为 " . $name;
                }
                if ($comment != $originalCollection['cmt'])
                {
                    $oComment = $originalCollection['cmt'];
                    $changeLog = $changeLog . " 说明由 " . $oComment . "  改为 " . $comment;
                }

                if ($changeLog != "")
                {
                    log::write("update() -> cName" . $originalCollection['cName']);
                    $changeLog = "将 " . $originalCollection['cName'] . " 中编号为 " . $originalCollection['id'] .
                        " 的记录的" . $changeLog;
                    log::write("update() -> " . $changeLog);
                    date_default_timezone_set("Asia/Shanghai");
                    $vTime = date("Y-m-d H:i:s");

/*
                    $sqlLog = "insert into think_tbEditRecord(vTime,vAuthor,tContent,editID)
							values('" . $vTime . "','" . $this->UserName . "','" . $changeLog . "','" .
*/
                    $sqlLog = "insert into \"think_tbEditRecord\"(\"vTime\",\"vAuthor\",\"tContent\",\"editID\")
							values('" . $vTime . "','" . $this->UserName . "','" . $changeLog . "','" .
                        $uniqueID . "')";
                    $M = new Model();
                    if (!$M->execute($sqlLog))
                    {
                        $url = "Admin-Index/specialIndex/cid/$cid";
                        $this->redirect($url, null, 1, '保存失败！');
                    }
                }

                //$sqlUpdate = "update think_tbCodeCollection set name='$name',codeComment = '$comment'
                //				where id = '$id'  and collectionID = '$cid'";
                if ($CodeCollectionClass->execute($sqlUpdate))
                {
                    $url = "Admin-Index/specialIndex/cid/$cid";
                    $this->redirect($url, null, 1, '已保存更改！');
                } else
                {
                    $url = "Admin-Index/specialIndex/cid/$cid";
                    $this->redirect($url, null, 1, '保存失败！');
                }
            }
        }
    }
    public function InfoDelete()
    {
        if ($this->checkRole())
        {
            $icid = $_GET['icid'];
            $id = $_GET['id'];

            if (empty($icid) || empty($id))
            {
                $this->redirect('Admin-Index/menu', null, 1, '不存在该子集！');
            } else
            {
                $InfoClass = M("tbInfoClass");
/*
                $sqlDelete = "delete from 
						think_tbInfoClass 
						where iClassID = '$icid' and vId = '$id'";
*/
                $sqlDelete = "delete from 
						\"think_tbInfoClass\" 
						where \"iClassID\" = '$icid' and \"vId\" = '$id'";
                if ($InfoClass->execute($sqlDelete))
                {
                    $url = "Admin-Index/InfoClassIndex/icid/$icid";
                    $this->redirect($url, null, 1, '已保存更改！');
                } else
                {
                    $url = "Admin-Index/InfoClassIndex/icid/$icid";
                    $this->redirect($url, null, 1, '保存失败！');
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
                $this->redirect('Admin-Index/menu', null, 1, '不存在该子集！');
            } else
            {
                $InfoClassNameClass = M("tbInfoClassName");
/*
                $InfoClassNameList = $InfoClassNameClass->query("SELECT iClassID as cid,vClassName as name,vClassLevel as cl
							FROM think_tbInfoClassName");
*/
                $InfoClassNameList = $InfoClassNameClass->query("SELECT \"iClassID\" as \"cid\",\"vClassName\" as \"name\",\"vClassLevel\" as \"cl\"
							FROM \"think_tbInfoClassName\"");
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
            $id = $_POST['itemId'];
            $name = $_POST['name'];
            $cName = $_POST['cName'];
            $icid = $_POST['icid'];
            $type = $_POST['type'];
            $len = $_POST['len'];
            $select = $_POST['select'];
            $scope = $_POST['scope'];
            $comment = $_POST['comment'];
            $ref = $_POST['ref'];

            log::write("InfoAdd icid=$icid id = $id name = $name");
            if (empty($id) || empty($icid))
            {
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $InfoClass = M("tbInfoClass");
/*
                $sql = "SELECT ic.iClassID as cid,ic.vId as id,
						ic.vName as name,ic.vNamechn as cName,
						ic.vType,ic.iLength as len,ic.vSelect,ic.vValueScope as scope,
						ic.tComment as cmt,ic.vRef as ref
						FROM think_tbInfoClass as ic
						where iClassID = '$icid' and vId = '$id'";
*/
                $sql = "SELECT \"iClassID\" as \"cid\",\"vId\" as \"id\",
						\"vName\" as \"name\",\"vNamechn\" as \"cName\",
						\"vType\",\"iLength\" as \"len\",\"vSelect\",\"vValueScope\" as \"scope\",
						\"tComment\" as \"cmt\",\"vRef\" as \"ref\"
						FROM \"think_tbInfoClass\" 
						where \"iClassID\" = '$icid' and \"vId\" = '$id'";
                $list = $InfoClass->query($sql);
                if (count($list) > 0)
                {
                    $url = "Admin-Index/InfoClassIndex/icid/$icid";
                    $this->redirect($url, null, 1, '编辑项已存在！');
                    return;
                }
/*
                $sqlInsert = "insert into think_tbInfoClass(iClassID,vId,vName,vNamechn,vType,iLength,
						vSelect,vValueScope,tComment,vRef )
						values('$icid','$id','$name','$cName','$type',$len,
						'$select','$scope','$comment','$ref')";
*/
                $sqlInsert = "insert into \"think_tbInfoClass\"(\"iClassID\",\"vId\",\"vName\",\"vNamechn\",\"vType\",\"iLength\",
						\"vSelect\",\"vValueScope\",\"tComment\",\"vRef\" )
						values('$icid','$id','$name','$cName','$type',$len,
						'$select','$scope','$comment','$ref')";
                if ($InfoClass->execute($sqlInsert))
                {
                    $url = "Admin-Index/InfoClassIndex/icid/$icid";
                    $this->redirect($url, null, 1, '已保存更改！');
                } else
                {
                    $url = "Admin-Index/InfoClassIndex/icid/$icid";
                    $this->redirect($url, null, 1, '保存失败！');
                }
            }
        }
    }
    public function InfoUpdate()
    {
        if ($this->checkRole())
        {
            $id = $_POST['itemId'];
            $name = $_POST['name'];
            $cName = $_POST['cName'];
            $icid = $_POST['icid'];
            $type = $_POST['type'];
            $len = $_POST['len'];
            $select = $_POST['select'];
            $scope = $_POST['scope'];
            $comment = $_POST['comment'];
            $ref = $_POST['ref'];

            log::write("InfoUpdate icid=$icid id = $id name = $name");
            if (empty($id) || empty($icid))
            {
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $InfoClass = M("tbInfoClass");
/*
                $sql = "SELECT ic.iClassID as cid,icn.vClassName as className,
						ic.vId as id,ic.vName as name,ic.vNamechn as cName,
						ic.vType,ic.iLength as len,ic.vSelect,ic.vValueScope as scope,
						ic.tComment as cmt,ic.vRef as ref,ic.editRecordID 
						FROM think_tbInfoClass as ic,think_tbInfoClassName as icn
						where ic.iClassID = '$icid' and ic.vId = '$id'
                                     and ic.iClassID = icn.iClassID";
*/
                $sql = "SELECT \"think_tbInfoClass\".\"iClassID\" as \"cid\",\"think_tbInfoClassName\".\"vClassName\" as \"className\",
						\"think_tbInfoClass\".\"vId\" as \"id\",\"think_tbInfoClass\".\"vName\" as \"name\",\"think_tbInfoClass\".\"vNamechn\" as \"cName\",
						\"think_tbInfoClass\".\"vType\",\"think_tbInfoClass\".\"iLength\" as \"len\",\"think_tbInfoClass\".\"vSelect\",\"think_tbInfoClass\".\"vValueScope\" as \"scope\",
						\"think_tbInfoClass\".\"tComment\" as \"cmt\",\"think_tbInfoClass\".\"vRef\" as \"ref\",\"think_tbInfoClass\".\"editRecordID\" 
						FROM \"think_tbInfoClass\" ,\"think_tbInfoClassName\" 
						where \"think_tbInfoClass\".\"iClassID\" = '$icid' and \"think_tbInfoClass\".\"vId\" = '$id'
                                     and \"think_tbInfoClass\".\"iClassID\" = \"think_tbInfoClassName\".\"iClassID\"";

                //$sql = "SELECT ic.iClassID as cid,ic.vId as id,
                //	ic.vName as name,ic.vNamechn as cName,
                //	ic.vType,ic.iLength as len,ic.vSelect,ic.vValueScope as scope,
                //	ic.tComment as cmt,ic.vRef as ref
                //	FROM think_tbInfoClass as ic
                //	where iClassID = '$icid' and vId = '$id'";
                $list = $InfoClass->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/menu";
                    $this->redirect($url, null, 1, '编辑项不存在！');
                    return;
                }
/*
                $sqlUpdate = "update think_tbInfoClass set vName ='$name',
						vNamechn = '$cName',vType = '$type',iLength = $len,
						vSelect = '$select',vValueScope = '$scope',tComment = '$comment',
						vRef = '$ref' 
						where vId = '$id'  and iClassID = '$icid'";
*/
                $sqlUpdate = "update \"think_tbInfoClass\" set \"vName\" ='$name',
						\"vNamechn\" = '$cName',\"vType\" = '$type',\"iLength\" = $len,
						\"vSelect\" = '$select',\"vValueScope\" = '$scope',\"tComment\" = '$comment',
						\"vRef\" = '$ref' 
						where \"vId\" = '$id'  and \"iClassID\" = '$icid'";

                $uniqueID = 0;

                if (empty($list[0]['editRecordID']))
                { // empty可以用来测试返回值是否为空
                    // 如果尚未有修改记录的编号
                    $uniqueID = $this->GetUniqueID();
/*
                    $sqlUpdate = "update think_tbInfoClass set vName ='$name',
							vNamechn = '$cName',vType = '$type',iLength = $len,
							vSelect = '$select',vValueScope = '$scope',tComment = '$comment',
							vRef = '$ref',editRecordID = '$uniqueID' 
							where vId = '$id'  and iClassID = '$icid'";
*/
                    $sqlUpdate = "update \"think_tbInfoClass\" set \"vName\" ='$name',
							\"vNamechn\" = '$cName',\"vType\" = '$type',\"iLength\" = $len,
							\"vSelect\" = '$select',\"vValueScope\" = '$scope',\"tComment\" = '$comment',
							\"vRef\" = '$ref',\"editRecordID\" = '$uniqueID' 
							where \"vId\" = '$id'  and \"iClassID\" = '$icid'";

                } else
                {
                    log::write("update() -> editRecordID = " . $list[0]['editRecordID']);
                    $uniqueID = $list[0]['editRecordID'];
                }

                //插入修改记录
                $changeLog = "";
                $originalCollection = $list[0];
                if ($name != $originalCollection['name'])
                {
                    $oName = $originalCollection['name'];
                    $changeLog = $changeLog . " 数据项名 由 " . $oName . "  改为 " . $name;
                }
                if ($cName != $originalCollection['cName'])
                {
                    $ocName = $originalCollection['cName'];
                    $changeLog = $changeLog . " 中文简称 由 " . $ocName . "  改为 " . $cName;
                }
                if ($type != $originalCollection['vType'])
                {
                    $otype = $originalCollection['vType'];
                    $changeLog = $changeLog . " 类型 由 " . $otype . "  改为 " . $type;
                }
                if ($len != $originalCollection['len'])
                {
                    $oLen = $originalCollection['len'];
                    $changeLog = $changeLog . " 长度 由 " . $oLen . "  改为 " . $len;
                }
                if ($select != $originalCollection['vSelect'])
                {
                    $osel = $originalCollection['vSelect'];
                    $changeLog = $changeLog . " 可选 由 " . $osel . "  改为 " . $select;
                }
                if ($scope != $originalCollection['scope'])
                {
                    $oscope = $originalCollection['scope'];
                    $changeLog = $changeLog . " 取值范围 由 " . $oscope . "  改为 " . $scope;
                }
                if ($comment != $originalCollection['cmt'])
                {
                    $oComment = $originalCollection['cmt'];
                    $changeLog = $changeLog . " 说明 由 " . $oComment . "  改为 " . $comment;
                }
                if ($ref != $originalCollection['ref'])
                {
                    $oref = $originalCollection['ref'];
                    $changeLog = $changeLog . " 引用编号 由 " . $oref . "  改为 " . $ref;
                }
                if ($changeLog != "")
                {
                    log::write("update() -> cName" . $originalCollection['cName']);
                    $changeLog = "将 " . $originalCollection['className'] . " 中编号为 " . $originalCollection['id'] .
                        " 的记录的" . $changeLog;
                    log::write("update() -> " . $changeLog);
                    date_default_timezone_set("Asia/Shanghai");
                    $vTime = date("Y-m-d H:i:s");
/*
                    $sqlLog = "insert into think_tbEditRecord(vTime,vAuthor,tContent,editID)
							values('" . $vTime . "','" . $this->UserName . "','" . $changeLog . "','" .
*/
                    $sqlLog = "insert into \"think_tbEditRecord\"(\"vTime\",\"vAuthor\",\"tContent\",\"editID\")
							values('" . $vTime . "','" . $this->UserName . "','" . $changeLog . "','" .
                        $uniqueID . "')";
                    $M = new Model();
                    if (!$M->execute($sqlLog))
                    {
                        $url = "Admin-Index/specialIndex/cid/$cid";
                        $this->redirect($url, null, 1, '保存失败！');
                    }
                }

                if ($InfoClass->execute($sqlUpdate))
                {
                    $url = "Admin-Index/InfoClassIndex/icid/$icid";
                    $this->redirect($url, null, 1, '已保存更改！');
                } else
                {
                    $url = "Admin-Index/InfoClassIndex/icid/$icid";
                    $this->redirect($url, null, 1, '保存失败！');
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
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $InfoClassNameClass = M("tbInfoClassName");
/*
                $InfoClassNameList = $InfoClassNameClass->query("SELECT iClassID as cid,vClassName as name,vClassLevel as cl
							FROM think_tbInfoClassName");
*/
                $InfoClassNameList = $InfoClassNameClass->query("SELECT \"iClassID\" as \"cid\",\"vClassName\" as \"name\",\"vClassLevel\" as \"cl\"
							FROM \"think_tbInfoClassName\"");
                $this->assign('InfoClassNameList', $InfoClassNameList);
                //$this->trace('edit codeCollectionlist', dump($CodeCollectionlist, false));

                $InfoClass = M("tbInfoClass");
/*
                $sql = "SELECT ic.iClassID as cid,ic.vId as id,
						ic.vName as name,ic.vNamechn as cName,
						ic.vType,ic.iLength as len,ic.vSelect,ic.vValueScope as scope,
						ic.tComment as cmt,ic.vRef as ref
						FROM think_tbInfoClass as ic
						where iClassID = '$icid' and vId = '$id'";
*/
                $sql = "SELECT \"iClassID\" as \"cid\",\"vId\" as \"id\",
						\"vName\" as \"name\",\"vNamechn\" as \"cName\",
						\"vType\",\"iLength\" as \"len\",\"vSelect\",\"vValueScope\" as \"scope\",
						\"tComment\" as \"cmt\",\"vRef\" as \"ref\"
						FROM \"think_tbInfoClass\" 
						where \"iClassID\" = '$icid' and \"vId\" = '$id'";
                //log::write("edit sql: $sql");
                $list = $InfoClass->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/menu";
                    $this->redirect($url, null, 1, '编辑项不存在！');
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
            if (!empty($icid))
            {
                //$this->trace('Admin specialIndex ' + $cnt, dump($icid, false));
                $cnt++;
            } else
            {
                //$this->trace('Admin specialIndex', 'empty input');
                $this->redirect('Admin-Index/menu', null, 1, '不存在该子集！');
                return;
            }
            $Class = M("tbInfoClass");
/*
            $sql = "SELECT ic.iClassID as cid,icn.vClassName as className,
					ic.vId as id,ic.vName as name,ic.vNamechn as cName,
					ic.vType,ic.iLength as len,ic.vSelect,ic.vValueScope as scope,
					ic.tComment as cmt,ic.vRef as ref,ic.editRecordID 
					FROM think_tbInfoClass as ic,think_tbInfoClassName as icn
					where ic.iClassID = '$icid' and ic.iClassID = icn.iClassID";
*/
            $sql = "SELECT \"think_tbInfoClass\".\"iClassID\" as \"cid\",\"think_tbInfoClassName\".\"vClassName\" as \"className\",
					\"think_tbInfoClass\".\"vId\" as \"id\",\"think_tbInfoClass\".\"vName\" as \"name\",\"think_tbInfoClass\".\"vNamechn\" as \"cName\",
					\"think_tbInfoClass\".\"vType\",\"think_tbInfoClass\".\"iLength\" as \"len\",\"think_tbInfoClass\".\"vSelect\",\"think_tbInfoClass\".\"vValueScope\" as \"scope\",
					\"think_tbInfoClass\".\"tComment\" as \"cmt\",\"think_tbInfoClass\".\"vRef\" as \"ref\",\"think_tbInfoClass\".\"editRecordID\" 
					FROM \"think_tbInfoClass\" ,\"think_tbInfoClassName\" 
					where \"think_tbInfoClass\".\"iClassID\" = '$icid' and \"think_tbInfoClass\".\"iClassID\" = \"think_tbInfoClassName\".\"iClassID\"";
            $list = $Class->query($sql);
            if (count($list) > 0)
            {
                $this->assign('list', $list);
                $this->assign('InfoClass', $list[0]);
                $this->display();
            } else
            {
                //$this->redirect('Admin-Index/menu', null, 1, '不存在该子集！');
                $this->assign('list', $list);
                $this->assign('InfoClass', $list[0]);
                $this->display();
            }
        }
    }
    public function InfoClassNameEdit()
    {
        if ($this->checkRole())
        {
            $InfoClassNameClass = M("tbInfoClassName");
/*
            $InfoClassNameList = $InfoClassNameClass->query("SELECT iClassID as cid,vClassName as name,vClassLevel as cl,tComment as cmt
					FROM think_tbInfoClassName");
*/
            $InfoClassNameList = $InfoClassNameClass->query("SELECT \"iClassID\" as \"cid\",\"vClassName\" as \"name\",\"vClassLevel\" as \"cl\",\"tComment\" as \"cmt\"
					FROM \"think_tbInfoClassName\"");
            $this->assign('InfoClassNameList', $InfoClassNameList);
            $this->display();
        }
    }
    public function InfoClassNameInsert()
    {
        if ($this->checkRole())
        {
            $cid = $_POST['cid'];
            $name = $_POST['name'];
            $cl = $_POST['level'];
            $cmt = $_POST['comment'];

            if (empty($cid))
            {
                $this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '编号未填写！');
                return;
            }
/*
            $sqlSelect = "select iClassID from think_tbInfoClassName where iClassID = '$cid' ";
*/
            $sqlSelect = "select \"iClassID\" from \"think_tbInfoClassName\" where \"iClassID\" = '$cid' ";
            $InfoClassNameClass = M("tbInfoClassName");
            $list = $InfoClassNameClass->query($sqlSelect);
            if (count($list) > 0)
            {
                $this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '编号已存在！');
                return;
            }
/*
            $sqlInsert = "insert into think_tbInfoClassName(iClassID,vClassName,vClassLevel,tComment) 
				values('$cid','$name','$cl','$cmt')";
*/
            $sqlInsert = "insert into \"think_tbInfoClassName\"(\"iClassID\",\"vClassName\",\"vClassLevel\",\"tComment\") 
				values('$cid','$name','$cl','$cmt')";
            if ($InfoClassNameClass->execute($sqlInsert))
            {
                echo "<script language='javascript' type='text/javascript'>";
                echo "parent.menu.location.reload()";
                echo "</script>";
                $this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '添加完成！正在跳转...');
            } else
            {
                $this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '添加失败！正在跳转...');
            }
        }
    }
    public function InfoClassNameDelete()
    {
        if ($this->checkRole())
        {
            $cid = $_GET['cid'];
            if (empty($cid))
            {
                $this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '该子集不存在！');
                return;
            }
/*
            $slqDelete = "delete from think_tbInfoClassName where iClassID = '$cid'";
*/
            $slqDelete = "delete from \"think_tbInfoClassName\" where \"iClassID\" = '$cid'";
            $InfoClassNameClass = M("tbInfoClassName");
            if ($InfoClassNameClass->execute($slqDelete))
            {
                $this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '删除成功！正在跳转...');
            } else
            {

                $this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '删除失败！正在跳转...');
            }
        }
    }
    public function ChangeLogIndex()
    {
        if ($this->checkRole())
        {
            $logID = $_GET['id'];
            $M = new Model();
            if (empty($logID))
            {
                //$this->redirect('Admin-Index/InfoClassNameEdit', null, 1, '该记录尚未修改过...');
/*
                $sql = "SELECT vTime as tm,vAuthor as author,tContent as cmt
					FROM think_tbEditRecord where editID = 'nothing' order by tm desc";
*/
                $sql = "SELECT \"vTime\" as \"tm\",\"vAuthor\" as \"author\",\"tContent\" as \"cmt\"
					FROM \"think_tbEditRecord\" where \"editID\" = 'nothing' order by \"tm\" desc";
            } else
                if ($logID == "all")
                {
/*
                    $sql = "SELECT vTime as tm,vAuthor as author,tContent as cmt
					FROM think_tbEditRecord order by tm desc";
*/
                    $sql = "SELECT \"vTime\" as \"tm\",\"vAuthor\" as \"author\",\"tContent\" as \"cmt\"
					FROM \"think_tbEditRecord\" order by \"tm\" desc";
                } else
                {
/*
                    $sql = "SELECT vTime as tm,vAuthor as author,tContent as cmt
					FROM think_tbEditRecord where editID = '" . $logID . "' order by tm desc";
*/
                    $sql = "SELECT \"vTime\" as \"tm\",\"vAuthor\" as \"author\",\"tContent\" as \"cmt\"
					FROM \"think_tbEditRecord\" where \"editID\" = '" . $logID . "' order by \"tm\" desc";
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
            $time = $_GET['tm'];
            $author = $_GET['author'];
            if (empty($time) || empty($author))
            {
                $this->redirect('Admin-Index/ChangeLogIndex/id/all', null, 1, '删除失败！正在跳转...');
            }
            $M = new Model();
/*
            $sql = "delete from think_tbEditRecord where vTime='" . $time .
                "' and vAuthor='" . $author . "'";
*/
            $sql = "delete from \"think_tbEditRecord\" where \"vTime\"='" . $time .
                "' and \"vAuthor\"='" . $author . "'";
            if ($M->execute($sql))
            {
                $this->redirect('Admin-Index/ChangeLogIndex/id/all', null, 1, '删除成功！正在跳转...');
            } else
            {
                $this->redirect('Admin-Index/ChangeLogIndex/id/all', null, 1, '删除失败！正在跳转...');
            }
        }
    }
    public function CustomClassItemDelete()
    {
        if ($this->checkRole())
        {
            $name = $_GET['name'];
            if (empty($name))
            {
                $this->redirect("Admin-Index/CustomClassIndex", null, 1, '条目不存在...');
            }

            $M = new Model();
/*
            $sql = "SELECT vClassName as className, vItemName as name,vItemContent as content
                  ,tComment as cmt,editRecordID 
				FROM think_tbCustomItems where vItemName = '" . $name . "'";
*/
            $sql = "SELECT \"vClassName\" as \"className\", \"vItemName\" as \"name\",\"vItemContent\" as \"content\"
                  ,\"tComment\" as \"cmt\",\"editRecordID\" 
				FROM \"think_tbCustomItems\" where \"vItemName\" = '" . $name . "'";

            $list = $M->query($sql);
            if (count($list) <= 0)
            {
                $this->redirect("Admin-Index/CustomClassIndex", null, 1, '条目不存在...');
                return;
            }
            $ClassName = $list[0]['className'];
            $sqlDelete = "delete from \"think_tbCustomItems\" where \"vItemName\"='$name'";
/*
            $sqlDelete = "delete from think_tbCustomItems where vItemName='$name'";
*/
            if ($M->execute($sqlDelete))
            {
                $this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
                    '删除成功...');
            } else
            {
                $this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
                    '删除失败...');
            }
        }
    }
    public function CustomClassItemInsert()
    {
        if ($this->checkRole())
        {
            $name = $_POST['name'];
            $content = $_POST['content'];
            $cmt = $_POST['comment'];
            $ClassName = $_POST['className'];
            if (empty($name))
            {

                $this->redirect("Admin-Index/CustomClassItemAdd/cn/" . $ClassName, null, 1,
                    '条目名称必须填写...');
            }
/*
            $sqlInsert = "insert into think_tbCustomItems(vClassName,vItemName,vItemContent,tComment) 
				values('" . $ClassName . "','" . $name . "','" . $content . "','" . $cmt .
                "')";
*/
            $sqlInsert = "insert into \"think_tbCustomItems\"(\"vClassName\",\"vItemName\",\"vItemContent\",\"tComment\") 
				values('" . $ClassName . "','" . $name . "','" . $content . "','" . $cmt .
                "')";
            //log::write("CustomClassItemInsert -> ".$sqlInsert);
            $M = new Model();
            if ($M->execute($sqlInsert))
            {
                $this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
                    '保存成功...');
            } else
            {
                $this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
                    '已经定义过该条目...');
            }
        }
    }
    public function CustomClassItemUpdate()
    {
        if ($this->checkRole())
        {
            $name = $_POST['name'];
            $content = $_POST['content'];
            $cmt = $_POST['comment'];
            $ClassName = $_POST['className'];
            if (empty($name))
            {

                $this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
                    '条目名称必须填写...');
                return;
            }

            $M = new Model();
/*
            $sql = "SELECT vClassName as className, vItemName as name,vItemContent as content
                  ,tComment as cmt,editRecordID 
				FROM think_tbCustomItems where vItemName = '" . $name . "'";
*/
            $sql = "SELECT \"vClassName\" as \"className\", \"vItemName\" as \"name\",\"vItemContent\" as \"content\"
                  ,\"tComment\" as \"cmt\",\"editRecordID\" 
				FROM \"think_tbCustomItems\" where \"vItemName\" = '" . $name . "'";

            //				$sql = "select collectionID,
            //			id, name,codeComment,editRecordID
            //		from think_tbCodeCollection  where id = '$id' and collectionID = '$cid'";
            $list = $M->query($sql);
            if (count($list) <= 0)
            {
                $this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
                    '编辑项不存在！');
                return;
            }
/*
            $sqlUpdate = "update think_tbCustomItems set vItemContent ='" . $content .
                "',tComment='" . $cmt . "' 
					where vItemName='" . $name . "'";
*/
            $sqlUpdate = "update \"think_tbCustomItems\" set \"vItemContent\" ='" . $content .
                "',\"tComment\"='" . $cmt . "' 
					where \"vItemName\"='" . $name . "'";
            //log::write("CustomClassItemUpdate: $sqlUpdate");

            /*
            *	产生修改记录
            */
            //默认值
            $uniqueID = 0;

            if (empty($list[0]['editRecordID']))
            { // empty可以用来测试返回值是否为空
                //log::write("update() -> editRecordID is null");
                // 如果尚未有修改记录的编号
                $uniqueID = $this->GetUniqueID();
/*
                $sqlUpdate = "update think_tbCustomItems set vItemContent ='" . $content .
                    "',tComment='" . $cmt . "',
					editRecordID = '$uniqueID' 
					where vItemName='" . $name . "'";
*/
                $sqlUpdate = "update \"think_tbCustomItems\" set \"vItemContent\" ='" . $content .
                    "',\"tComment\"='" . $cmt . "',
					\"editRecordID\" = '$uniqueID' 
					where \"vItemName\"='" . $name . "'";
            } else
            {
                $uniqueID = $list[0]['editRecordID'];
            }
            //插入修改记录
            $changeLog = "";
            $originalCollection = $list[0];
            if ($content != $originalCollection['content'])
            {
                $ocontent = $originalCollection['content'];
                $changeLog = $changeLog . " 内容 由 " . $ocontent . "  改为 " . $content;
            }
            if ($cmt != $originalCollection['cmt'])
            {
                $oComment = $originalCollection['cmt'];
                $changeLog = $changeLog . " 说明 由 " . $oComment . "  改为 " . $cmt;
            }

            if ($changeLog != "")
            {
                log::write("update() -> cName" . $originalCollection['cName']);
                $changeLog = "将 自定义类别 " . $ClassName . " 中名称为 " . $name . " 的记录的" . $changeLog;
                log::write("update() -> " . $changeLog);
                date_default_timezone_set("Asia/Shanghai");
                $vTime = date("Y-m-d H:i:s");
/*
                $sqlLog = "insert into think_tbEditRecord(vTime,vAuthor,tContent,editID)
					values('" . $vTime . "','" . $this->UserName . "','" . $changeLog . "','" .
*/
                $sqlLog = "insert into \"think_tbEditRecord\"(\"vTime\",\"vAuthor\",\"tContent\",\"editID\")
					values('" . $vTime . "','" . $this->UserName . "','" . $changeLog . "','" .
                    $uniqueID . "')";
                $M = new Model();
                if (!$M->execute($sqlLog))
                {
                    $url = "Admin-Index/specialIndex/cid/$cid";
                    $this->redirect($url, null, 1, '保存失败！');
                }
            }

            if ($M->execute($sqlUpdate))
            {
                $this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
                    '保存成功...');
            } else
            {
                $this->redirect("Admin-Index/CustomClassItemIndex/cn/" . $ClassName, null, 1,
                    '保存失败...');
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
                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '不存在该类...');
                return;
            }
/*
            $sql = "SELECT vClassName as className, vItemName as name,vItemContent as content
            ,tComment as cmt,editRecordID 
				FROM think_tbCustomItems where vItemName = '" . $itemName . "'";
*/
            $sql = "SELECT \"vClassName\" as \"className\", \"vItemName\" as \"name\",\"vItemContent\" as \"content\"
            ,\"tComment\" as \"cmt\",\"editRecordID\" 
				FROM \"think_tbCustomItems\" where \"vItemName\" = '" . $itemName . "'";
            log::write("CustomClassItemEdit -> " . $sql);
            $M = new Model();
            $list = $M->query($sql);
            if (count($list) > 0)
            {
                $this->assign('item', $list[0]);
                $this->assign('itemName', $list[0]['className']);
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
                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '不存在该类...');
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
                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '不存在该类...');
                return;
            }
/*
            $sql = "SELECT vClassName as className, vItemName as name,vItemContent as content
                  ,tComment as cmt,editRecordID 
				FROM think_tbCustomItems where vClassName = '" . $ClassName . "'";
*/
            $sql = "SELECT \"vClassName\" as \"className\", \"vItemName\" as \"name\",\"vItemContent\" as \"content\"
                  ,\"tComment\" as \"cmt\",\"editRecordID\" 
				FROM \"think_tbCustomItems\" where \"vClassName\" = '" . $ClassName . "'";

            log::write("CustomClassItemIndex -> " . $sql);
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
/*
            $CustomClassNameList = $M->query("SELECT vName as name,tComment as cmt
					FROM think_tbCustomClasses ");
*/
            $CustomClassNameList = $M->query("SELECT \"vName\" as \"name\",\"tComment\" as \"cmt\"
					FROM \"think_tbCustomClasses\" ");
            $this->assign('list', $CustomClassNameList);
            $this->display();
        }
    }
    public function CustomClassAdd()
    {
        if ($this->checkRole())
        {
            $name = $_POST['name'];
            $cmt = $_POST['comment'];
            if (empty($name))
            {
                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '请添加有效名称后再保存...');
                return;
            }
/*
            $sqlInsert = "insert into think_tbCustomClasses(vName,tComment) values('" . $name .
                "','" . $cmt . "')";
*/
            $sqlInsert = "insert into \"think_tbCustomClasses\"(\"vName\",\"tComment\") values('" . $name .
                "','" . $cmt . "')";
            log::write("CustomClassAdd ->  $sqlInsert");

            $M = new Model();
            if ($M->execute($sqlInsert))
            {
                echo "<script language='javascript' type='text/javascript'>";
                echo "parent.menu.location.reload()";
                echo "</script>";

                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '保存成功...');
            } else
            {
                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '保存失败...');
            }
        }
    }
    public function CustomClassDelete()
    {
        if ($this->checkRole())
        {
            $name = $_GET['name'];
            if (empty($name))
            {
                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '删除失败...');
            }
/*
            $sqlDelete = "delete from think_tbCustomClasses where vName = '" . $name . "'";
*/
            $sqlDelete = "delete from \"think_tbCustomClasses\" where \"vName\" = '" . $name . "'";

            $M = new Model();
            if ($M->execute($sqlDelete))
            {
                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '删除成功...');
            } else
            {
                $this->redirect('Admin-Index/CustomClassIndex', null, 1, '删除失败...');
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
/*
            $uniqueIDList = $M->query("select id from think_tbUniqueID where id = '$uniqueID'");
*/
            $uniqueIDList = $M->query("select \"id\" from \"think_tbUniqueID\" where \"id\" = '$uniqueID'");
            if (count($uniqueIDList) > 0)
            {
                $uniqueID = $uniqueID + 1;
            } else
            {
/*
                $sql = "insert into think_tbUniqueID(id) values('$uniqueID') ";
*/
                $sql = "insert into \"think_tbUniqueID\"(\"id\") values('$uniqueID') ";
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
