<?php

class UserManagementAction extends Action
{
	public function configMailEdit() {
		if($this->CheckRole())
		{
			$M = new Model();
			$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'email'";
			$list = $M->query($sqlSelect);
			if (count($list)>0) {
				$this->assign('email',$list[0]['VVALUE']);
			}
			else
			{
				$this->assign('email','');
			}
			$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'authen'";
			$list = $M->query($sqlSelect);
			if (count($list)>0) {
				$this->assign('authen',$list[0]['VVALUE']);
			}
			else
			{
				$this->assign('authen','');
			}
			$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'pwd'";
			$list = $M->query($sqlSelect);
			if (count($list)>0) {
				$this->assign('pwd',$list[0]['VVALUE']);
			}
			else
			{
				$this->assign('pwd','');
			}
			$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'user'";
			$list = $M->query($sqlSelect);
			if (count($list)>0) {
				$this->assign('user',$list[0]['VVALUE']);
			}
			else
			{
				$this->assign('user','');
			}
			$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'smtp'";
			$list = $M->query($sqlSelect);
			if (count($list)>0) {
				$this->assign('smtp',$list[0]['VVALUE']);
			}
			else
			{
				$this->assign('smtp','');
			}
			$this->display();
		}
	}
	public function testConfig() {
		if($this->CheckRole())
		{
			$this->display();
		}
	}
	public function sendTestMail()
	{
		$mailTo = $_POST['email'];
		
		if (empty($mailTo)) {
			return;
		}
		$mail = A('Admin.Mail');
		if(	$mail->sendMailTest($mailTo))
		{
			$this->assign('jumpUrl','/UserManagement/testConfig');
			$this->success('邮件已发送！');
		}
		else
		{
			$this->assign('jumpUrl','/UserManagement/testConfig');
			$this->success('邮件发送错误，请重新检查配置！');
			
		}
	}
	public function configMailSave() {
		if($this->CheckRole())
		{
			$email = $_POST['email'];
			$authen = $_POST['authen'];
			$pwd = $_POST['pwd'];
			$user = $_POST['user'];
			$smtp=$_POST['smtp'];
			
			//			echo "configMailSave";
			//			return;
			$sqlExcute ="";
			
			
			if (!empty($email)) {
				$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'email'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				if (count($list)>0) {
					$sqlExcute.="update T_CONFIG set VVALUE = '$email' where VKEY = 'email';";
				}
				else{
					$sqlExcute.="insert into T_CONFIG(VKEY,VVALUE) values('email','$email');";
				}
			}
			if (!empty($authen)) {
				$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'authen'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				if (count($list)>0) {
					$sqlExcute.="update T_CONFIG set VVALUE = '$authen' where VKEY = 'authen';";
				}
				else{
					$sqlExcute.="insert into T_CONFIG(VKEY,VVALUE) values('authen','$authen');";
				}
			}
			if (!empty($pwd)) {
				$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'pwd'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				if (count($list)>0) {
					$sqlExcute.="update T_CONFIG set VVALUE = '$pwd' where VKEY = 'pwd';";
				}
				else{
					$sqlExcute.="insert into T_CONFIG(VKEY,VVALUE) values('pwd','$pwd');";
				}
			}
			if (!empty($user)) {
				$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'user'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				if (count($list)>0) {
					$sqlExcute.="update T_CONFIG set VVALUE = '$user' where VKEY = 'user';";
				}
				else{
					$sqlExcute.="insert into T_CONFIG(VKEY,VVALUE) values('user','$user');";
				}
			}
			if (!empty($smtp)) {
				$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'smtp'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				if (count($list)>0) {
					$sqlExcute.="update T_CONFIG set VVALUE = '$smtp' where VKEY = 'smtp';";
				}
				else{
					$sqlExcute.="insert into T_CONFIG(VKEY,VVALUE) values('smtp','$smtp');";
				}
			}
			//			echo $sqlExcute;
			//			return;
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
						//echo $i."  ".$sqlArray[$i]. "<br>";
						///*
						$ConfigM = new Model();
						if (!$ConfigM->execute($sqlArray[$i])) {
							$state=false;
							break;
						}
						//*/
						
					}
				}					
			}
			
			//return;
			//******************************************
			
			
			
			if($state)
			//if($ConfigM->execute($sqlInsert))
			{
				$this->assign('jumpUrl','/UserManagement/configMailEdit');
				$this->success('设置保存成功！');
				//$this->redirect('UserManagement/changepwd', null, 1, '更改密码成功！');
			}else
			{
				$this->assign('jumpUrl','/UserManagement/configMailEdit');
				$this->success('设置保存失败！');
				//$this->redirect('UserManagement/changepwd', null, 1, '更改密码失败！');
			}
		}
	}
	/*	
	public function configMailSave() {
	if($this->checkLogined())
			{
	$email = $_POST['email'];
	$authen = $_POST['authen'];
	$pwd = $_POST['pwd'];
	$user = $_POST['user'];
	$smtp=$_POST['smtp'];
				
		
	$sqlInsert ="";
	$sqlUpdate ="";
		
				
	if (!empty($email)) {
		$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'email'";
		$M = new Model();
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$sqlUpdate.="update T_CONFIG set VVALUE = '$email' where VKEY = 'email';";
		}
		else{
			$sqlInsert.="insert into T_CONFIG(VKEY,VVALUE) values('email','$email');";
		}
	}
	if (!empty($authen)) {
		$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'authen'";
		$M = new Model();
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$sqlUpdate.="update T_CONFIG set VVALUE = '$authen' where VKEY = 'authen';";
		}
		else{
			$sqlInsert.="insert into T_CONFIG(VKEY,VVALUE) values('authen','$authen');";
		}
	}
	if (!empty($pwd)) {
		$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'pwd'";
		$M = new Model();
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$sqlUpdate.="update T_CONFIG set VVALUE = '$pwd' where VKEY = 'pwd';";
		}
		else{
			$sqlInsert.="insert into T_CONFIG(VKEY,VVALUE) values('pwd','$pwd');";
		}
	}
	if (!empty($user)) {
		$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'user'";
		$M = new Model();
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$sqlUpdate.="update T_CONFIG set VVALUE = '$user' where VKEY = 'user';";
		}
		else{
			$sqlInsert.="insert into T_CONFIG(VKEY,VVALUE) values('user','$user');";
		}
	}
	if (!empty($smtp)) {
		$sqlSelect = "select VKEY from T_CONFIG where VKEY = 'smtp'";
		$M = new Model();
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$sqlUpdate.="update T_CONFIG set VVALUE = '$smtp' where VKEY = 'smtp';";
		}
		else{
			$sqlInsert.="insert into T_CONFIG(VKEY,VVALUE) values('smtp','$smtp');";
		}
	}
	//***************************************
	//  将修改记录注释 oracle数据时使用这种sql写法
	if (C('DB_TYPE') == 'oracle') {
		if (!empty($sqlInsert)) {
			$sqlInsert ="begin ".$sqlInsert." end;";	
		}
		if (!empty($sqlUpdate)) {
			$sqlUpdate ="begin ".$sqlUpdate." end;";
		}
	}
	////////////////////////////////////////
		
		
	$ConfigM1 = new Model();
	$ConfigM2 = new Model();
	$state = true;
	if (!empty($sqlInsert)) {
		log::write("1  ".$sqlInsert);
		if (!$ConfigM1->execute($sqlInsert)) {
			log::write("1111  ".$sqlInsert);
			$state=false;
		}
	}
	if (!empty($sqlUpdate)) {
		log::write("2 ".$sqlUpdate);
		if (!$ConfigM2->execute($sqlUpdate)) {
			log::write($sqlUpdate);
			$state=false;
		}
	}
	echo $sqlInsert;
	echo "<br>";
	echo $sqlUpdate;
	//return;
	if($state)
		//if($ConfigM->execute($sqlInsert))
	{
		$this->assign('jumpUrl','/UserManagement/configMailEdit');
		$this->success('设置保存成功！');
		//$this->redirect('UserManagement/changepwd', null, 1, '更改密码成功！');
	}else
	{
		$this->assign('jumpUrl','/UserManagement/configMailEdit');
		$this->success('设置保存失败！');
		//$this->redirect('UserManagement/changepwd', null, 1, '更改密码失败！');
	}
			}
		}
		*/
	public function welcome()
	{
		$this->display();
	}
	public function main()
	{
		if($this->CheckRole())
		{
			$this->display();
		}
	}
	public function changepwd()
	{
		// $userAcount = $_GET['userid'];
		if($this->CheckRole())
		{	
			$this->display();
		}
	}
	public function updatepwd()
	{
		if($this->CheckRole())
		{		
			$crtUser = $_SESSION['acount'];
			$oldpwd = $_POST['oldpassword'];
			
			$pwd1 = $_POST['password1'];
			$pwd2 = $_POST['password2'];
			
			if($pwd1 != $pwd2)
			{
				$this->assign('jumpUrl','/UserManagement/changepwd');
				$this->success('两次输入的密码不一致！');
				//$this->redirect('UserManagement/changepwd', null, 1, '两次输入的密码不一致！');
				return;
			}
			$oldpwdMd5 = md5($oldpwd);
			$sql = "SELECT USERS.ACCOUNT,USERS.NICKNAME
					FROM THINK_USER USERS
					WHERE  USERS.ACCOUNT = '$crtUser' and PASSWORD = '$oldpwdMd5'";
			$M = new Model();
			$list = $M->query($sql);
			if(count($list) <=0 )
			{
				$this->assign('jumpUrl','/UserManagement/changepwd');
				$this->success('输入的当前密码错误！');
				//$this->redirect('UserManagement/changepwd', null, 1, '输入的当前密码错误！');
				return;
			}
			$newpwdMd5 = md5($pwd1);
			$sqlUpdate = "UPDATE THINK_USER SET PASSWORD = '$newpwdMd5' where ACCOUNT = '$crtUser'";
			if($M->execute($sqlUpdate))
			{
				$this->assign('jumpUrl','/UserManagement/changepwd');
				$this->success('更改密码成功！');
				//$this->redirect('UserManagement/changepwd', null, 1, '更改密码成功！');
			}else
			{
				$this->assign('jumpUrl','/UserManagement/changepwd');
				$this->success('更改密码失败！');
				//$this->redirect('UserManagement/changepwd', null, 1, '更改密码失败！');
			}
		}
	}
	public function UserIndex()
	{
		date_default_timezone_set("Asia/Shanghai");
		//echo("$strf");
		//print_r(strptime($strf,$format));
		//*************************************************************
		/*
		$date=getdate();
		$todayStart = 
			mktime(0,0,0
				,$date['mon'],$date['mday'],$date['year']);
		$vTimeStart = date("Y-m-d H:i:s",$todayStart);
		$vTimeEnd = date("Y-m-d H:i:s",strtotime('+1 day',$todayStart));
		echo "$vTimeStart <br>";		
		echo "$vTimeEnd <br>";		
		return;
		*/
		//**************************************************************
		/*
		$todayEnd = 
			mktime(0,0,0
				,$date['mon'],$date['mday'],$date['year']);
		$vTimeEnd = date("Y-m-d H:i:s",$todayEnd);
		echo "$vTimeStart <br>";		
		
		echo "$vTimeEnd <br>";	
		*/	
		//date('Y-m-d H:i:s',strtotime('+1 day'));
		//var_dump(strtotime('+1 day'));
				//echo $todayStart;

		//var_dump(strptime($vTime,$format));
		if($this->CheckRole())
		{
			log::write(" -> UserIndex");
			$M = new Model();
			$sql="";
			
			$sql = "SELECT USERS.ACCOUNT,USERS.NICKNAME,ROLEUSER.ROLE_NAME,ROLE.REMARK
					FROM THINK_USER USERS,THINK_ROLEUSER ROLEUSER,THINK_ROLE ROLE	
					WHERE  USERS.ACCOUNT = ROLEUSER.USER_ACOUNT AND USERS.ACCOUNT NOT IN ('admin')
					and ROLE.NAME = ROLEUSER.ROLE_NAME";
			
			//echo $sql;
			//return;
			$list = $M->query($sql);
			$this->assign("userList",$list);
			
			$this->display();
		}
		
	}
	public function UserAdd()
	{
		if($this->CheckRole())
		{
			$M = new Model();
		/*
			$sqlRoleIndex = "SELECT NAME,REMARK AS CNAME
					FROM THINK_ROLE WHERE NAME NOT IN ('adminer')";
					*/
			$sqlRoleIndex = "SELECT NAME,REMARK AS CNAME
					FROM THINK_ROLE ";
			$roleList = $M->query($sqlRoleIndex);
			$this->assign("roleList",$roleList);
			//var_dump($roleList);
			$this->display();
		}
	}
	public function UserInsert()
	{
		if($this->CheckRole())
		{
			$username = $_POST['username'];
			$nickname = $_POST['nickname'];
			$pwd = $_POST['password'];
			$role = $_POST['roleName'];
			$email = $_POST['email'];
			$pwdMd5 = md5($pwd);
			$M = new Model();
			$M2 = new Model();
			
			$sqlInsertUser = "INSERT INTO THINK_USER(ACCOUNT,NICKNAME,PASSWORD,EMAIL,OPERATERID,OPERATERTIME,JLZT)
					values('$username','$nickname','$pwdMd5','$email','".$_SESSION['acount']."',sysdate,'1')";
			$sqlInsertRoleUser = "insert into THINK_ROLEUSER(ROLE_NAME,USER_ACOUNT) values('$role','$username')";
			if($M->execute($sqlInsertUser) && $M->execute($sqlInsertRoleUser))
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('添加用户成功！');
				//$this->redirect('UserManagement/UserIndex', null, 1, '添加用户成功！');
			}
			else
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('添加用户失败');
				//$this->redirect('UserManagement/UserIndex', null, 1, '添加用户失败！');
			}
			return;		
		}
	}
	public function userEdit()
	{
		if($this->CheckRole())
		{
			$username = $_GET['username'];
			if (empty($username))
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('请选择要修改的用户!');
				return;
			}
			$M = new Model();
			$sql = "SELECT USERS.ACCOUNT,USERS.NICKNAME,ROLE.ROLE_NAME,USERS.EMAIL 
					FROM THINK_USER USERS,THINK_ROLEUSER ROLE 
					where USERS.ACCOUNT = ROLE.USER_ACOUNT and 
					USERS.ACCOUNT = '$username'";
			$list = $M->query($sql);
			if(count($list) <= 0)
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('请选择要修改的用户!');
				return;
			}
			$this->assign('userInfo',$list[0]);
			//	$sql = "SELECT distinct ROLE_USER.ROLE_NAME,ROLE.REMARK	FROM THINK_ROLEUSER ROLE_USER,THINK_ROLE ROLE
			//          where ROLE_USER.ROLE_NAME not in('adminer') and ROLE.NAME = ROLE_USER.ROLE_NAME";
			//$sql = "select NAME,REMARK from THINK_ROLE  WHERE NAME NOT IN ('adminer')";
			$sql = "select NAME,REMARK from THINK_ROLE";
			$roleList = $M->query($sql);
			$this->assign('roleList',$roleList);
			//订阅信息
			$sqlSelect = "select ITEM_NAME from T_ORDER_ITEM where USER_ACOUNT = '".$username."'";
			$Mcc = new Model();
			$itemList = $Mcc->query($sqlSelect);
			//var_dump($itemList);
			//return;
			$this->assign('codeCollection',0);
			$this->assign('class',0);
			$this->assign('rule',0);
			$this->assign('interface',0);
			
			for($i = 0;$i < count($itemList);$i++)
			{
				$x = $itemList[$i]['ITEM_NAME'];
				switch ($x)
				{
					case 'codeCollection':
						$this->assign('codeCollection',1);
						break;
					case 'class':
						$this->assign('class',1);
						break;
					case 'rule':
						$this->assign('rule',1);
						break;
					case 'interface':
						$this->assign('interface',1);
						break;
					
				}
			}
			
			
			$this->display();
		}
	}
	public function userUpdate()
	{
		if($this->CheckRole())
		{
			$username = $_POST['username'];
			$userRole = $_POST['rolename'];
			$nickname = $_POST['nickname'];
			$email = $_POST['email'];
			$codeCollection = $_POST['codeCollection'];
			$class = $_POST['class'];
			$rule = $_POST['rule'];
			$interface=$_POST['interfac'];
			
			/*
			if (empty($rule)) {
				echo "rule e";
			}
						
			if (empty($interface)) {
				echo "<br>interface e";
			}
			//return;
			*/			
			if (empty($username) || empty($userRole) || empty($nickname))
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('用户信息填写不完整!');
				return;
			}
			
			$sqlExcute="";
			
			$sqlExcute .= "update THINK_ROLEUSER set ROLE_NAME = '$userRole' 
					where USER_ACOUNT = '$username';";
			$sqlExcute .= "update THINK_USER set NICKNAME = '$nickname',EMAIL = '$email' where ACCOUNT = '$username';";
			
			if ($codeCollection == 'on') {
				//如果选择了
				$sqlSelect = "select * from T_ORDER_ITEM where USER_ACOUNT = '$username' and ITEM_NAME = 'codeCollection'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				//数据库不存在的话
				if (count($list)<=0) {
					$sqlExcute .= "insert into T_ORDER_ITEM(USER_ACOUNT,ITEM_NAME) values('$username','codeCollection');";
				}
			}
			else
			{
				$sqlExcute .= "delete from T_ORDER_ITEM where ITEM_NAME = 'codeCollection' and USER_ACOUNT = '$username';";
			}
			
			if ($class == 'on') {
				$sqlSelect = "select * from T_ORDER_ITEM where USER_ACOUNT = '$username' and ITEM_NAME = 'class'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				//数据库不存在的话
				if (count($list)<=0) {
					$sqlExcute .= "insert into T_ORDER_ITEM(USER_ACOUNT,ITEM_NAME) values('$username','class');";
				}
			}
			else
			{
				$sqlExcute .= "delete from T_ORDER_ITEM where ITEM_NAME = 'class' and USER_ACOUNT = '$username';";
			}
			if ($rule == 'on') {
				$sqlSelect = "select * from T_ORDER_ITEM where USER_ACOUNT = '$username' and ITEM_NAME = 'rule'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				//数据库不存在的话
				if (count($list)<=0) {
					$sqlExcute .= "insert into T_ORDER_ITEM(USER_ACOUNT,ITEM_NAME) values('$username','rule');";
				}
			}
			else
			{
				$sqlExcute .= "delete from T_ORDER_ITEM where ITEM_NAME = 'rule' and USER_ACOUNT = '$username';";
			}      
			if ($interface == 'on') {
				$sqlSelect = "select * from T_ORDER_ITEM where USER_ACOUNT = '$username' and ITEM_NAME = 'interface'";
				$M = new Model();
				$list = $M->query($sqlSelect);
				//数据库不存在的话
				if (count($list)<=0) {
					$sqlExcute .= "insert into T_ORDER_ITEM(USER_ACOUNT,ITEM_NAME) values('$username','interface');";
				}
			}
			else
			{
				$sqlExcute .= "delete from T_ORDER_ITEM where ITEM_NAME = 'interface' and USER_ACOUNT = '$username';";
			}   
			$state = true;
			//echo $sqlExcute;
			//return;
			
			
			
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
			if($state)
			{
				//订阅信息更新
				
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('用户信息修改成功!');
			}
			else
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('用户信息修改失败!');
			}
			
		}
	}	
	public function UserDelete()
	{
		if($this->CheckRole())
		{
			$username = $_GET['username'];//使用 & 号隔开的字符串
			//echo $username;
			//return;
			if (empty($username))
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('请选择要删除的用户!');
				//$this->redirect('UserManagement/UserIndex', null, 1, '请选择要删除的用户！');
				return;
			}
			$usernameA = explode('?',$username);
			//var_dump($usernameA);
			//return;
			
			$sqlExcute="";
			for($i=0;$i<count($usernameA);$i++)
			{
				//log::write("UserDelete".$usernameA[$i]);
				$usr = $usernameA[$i];
				$M = new Model();
				
				$sql = "SELECT THINK_USER.ACCOUNT,THINK_USER.NICKNAME,THINK_ROLEUSER.ROLE_NAME
						FROM THINK_USER ,THINK_ROLEUSER 
						WHERE  THINK_USER.ACCOUNT = THINK_ROLEUSER.USER_ACOUNT AND THINK_USER.ACCOUNT = '$usr'";
				$list = $M->query($sql);
				if(count($lsit)<0)
				{
					$this->assign('jumpUrl','/UserManagement/UserIndex');
					$this->success('不存在该用户！');
					//$this->redirect('UserManagement/UserIndex', null, 1, '不存在该用户！');
					return;				
				}
				$sqlExcute .= "delete from THINK_USER where ACCOUNT = '$usr';";
				$sqlExcute .= "delete from THINK_ROLEUSER where USER_ACOUNT = '$usr';";
				
			}
			//			echo $sqlExcute;
			//			return;
			
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
						//echo $i."  ".$sqlArray[$i]. "<br>";
						///*
						$ConfigM = new Model();
						if (!$ConfigM->execute($sqlArray[$i])) {
							$state=false;
							break;
						}
						//*/
						
					}
				}					
			}
			
			//return;
			//******************************************
			
			//if($M->execute($sqldeleteUser) && $M->execute($sqlDeleteUserRole))
			if($state)
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('删除成功！');
				//$this->redirect('UserManagement/UserIndex', null, 1, '删除成功！');
				return;	
			}else
			{
				$this->assign('jumpUrl','/UserManagement/UserIndex');
				$this->success('删除失败！');
				//$this->redirect('UserManagement/UserIndex', null, 1, '删除失败！');
				return;					
			}
		}
	}
	public function checkLogined()
	{
		if ($_SESSION['logined'] != 1)
		{
			$this->assign('jumpUrl','/Home/Welcome/welcome');
			$this->success('正在跳转到登录页面...');
			//$this->redirect('Home-Welcome/welcome', null, 1, '正在跳转到登录页面...');
			return false;
		}		

		return true;
	}
	public function CheckRole()
	{
		if ($_SESSION['logined'] != 1)
		{
			$this->assign('jumpUrl','/Home/Welcome/welcome');
			$this->success('正在跳转到登录页面...');
			//$this->redirect('Home-Welcome/welcome', null, 1, '正在跳转到登录页面...');
			return false;
		}
		if ($_SESSION['ROLE_NAME'] != "adminer")
		{
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Welcome/welcome'";
			echo "</script>";
			return false;
			$this->assign('jumpUrl','/Home/Welcome/welcome');
			$this->success('您没有权利登录此页面，正在跳转到登录页面...');
			//$this->redirect('Home-Welcome/welcome', null, 1, '您没有权利登录此页面，正在跳转到登录页面...');
			return false;
		}
		return true;
	}
}

?>
