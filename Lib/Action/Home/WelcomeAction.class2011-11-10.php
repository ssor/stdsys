<?php

class WelcomeAction extends Action {
	
	//Vendor("Trace.debugTrace");
	
	public function AdminLogin()
	{
		$this->display();
	}
	public function noUser()
	{
		//$this->assign("jumpUrl",'/Welcome/welcome');
		$this->display();
	}
    // 首页
///*
        public function welcome()
        {
		$this->display();
	}
//*/
/*
    public function welcome()
    {	
        Vendor("CAS.CAS");
        phpCAS::setDebug();
        phpCAS::client(CAS_VERSION_2_0,'211.68.70.10',9001,'sso');
        phpCAS::setNoCasServerValidation();
        if(!phpCAS::isAuthenticated())
        {		
			phpCAS::forceAuthentication();
		}else
		{
			$arrUserDetails = phpCAS::getUserDetails();
			$username = $arrUserDetails['loginId'];
			$M = new Model();
			$sql = "select THINK_USER.ACCOUNT,THINK_USER
				.NICKNAME,THINK_ROLEUSER.ROLE_NAME
				from THINK_USER ,THINK_ROLEUSER
				where  THINK_USER.ACCOUNT = THINK_ROLEUSER
				.USER_ACOUNT and THINK_USER.ACCOUNT = '$username'";
			$list = $M->query($sql);
			if (count($list) <= 0)
			{

				$this->assign('jumpUrl','/Welcome/noUser');
				$this->success('用户名或者密码错误，请重新输入，正在跳转 ...！');

				return;
			}
			$user = $list[0];
			if ($user['ROLE_NAME'] == "editor")
			{
				$_SESSION['acount'] = $user['ACCOUNT'];
				$_SESSION['nickname'] = $user['NICKNAME'];
				$_SESSION['role_name'] = $user['ROLE_NAME'];
				$_SESSION['logined'] = 1;
				$_SESSION['loginStyle'] = "cas";
				
				$this->assign('jumpUrl','/Admin/Index/main');
				$this->success('登录成功，正在跳转 ...！');

				return;
			}
			if ($user['ROLE_NAME'] == "viewer")
			{
				$_SESSION['acount'] = $user['ACCOUNT'];
				$_SESSION['nickname'] = $user['NICKNAME'];
				$_SESSION['role_name'] = $user['ROLE_NAME'];
				$_SESSION['logined'] = 1;
				$this->assign('jumpUrl','/Index/main');
				$this->success('登录成功，正在跳转 ...！');

				return;
			}
			 if ($user['ROLE_NAME'] == "adminer")
			{
				$_SESSION['acount'] = $user['ACCOUNT'];
				$_SESSION['nickname'] = $user['NICKNAME'];
				$_SESSION['role_name'] = $user['ROLE_NAME'];
				$_SESSION['logined'] = 1;
				//跳转到用户管理页面
				$this->assign('jumpUrl','/UserManagement/main');
				$this->success('登录成功，正在跳转 ...！');
				return;
			}
		
			if ($_SESSION['role_name'] == "editor")
			{
				$this->redirect('Admin/Index/main', null, 1,
				 '登录成功，正在跳转 ...！');
				return;
			}
			if ($_SESSION['role_name'] == "viewer")
			{
				$this->redirect('/Index/main', null, 1,
				 '登录成功，正在跳转 ...！');
				return;
			}
		   if ($_SESSION['role_name'] == "adminer")
			{
					//跳转到用户管理页面
				$this->redirect('/UserManagement/main', null, 1,
				 '登录成功，正在跳转 ...！');
				return;
			}
		
		}
    }
*/
        public function logout()
        {
		//echo "logout->";
		//return;
/*
			Vendor("CAS.CAS");
			phpCAS::setDebug();
			phpCAS::client(CAS_VERSION_2_0,'211.68.70.10',9001,'sso');
*/
			$_SESSION['acount'] = "";
			$_SESSION['nickname'] = "";
			$_SESSION['role_name'] = "";
			$_SESSION['logined'] = 0;	
		echo "<script language='javascript' type='text/javascript'>top.window.close();</script>";
/*

		echo "<script language='javascript' type='text/javascript'>top.window.close();</script>";


			session_start();
			session_unset();
			session_destroy();
*/		
			
			/*
			$cas_url = 'http://211.68.70.10:9001/sso/logout?logoutSuccessUrl=';
			$service = 'http://211.68.70.14:9001/index.php/Welcome/welcome';
			$cas_url = $cas_url. urlencode($service); 
			//header('Location: '.$cas_url);
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = '".$cas_url."'";
			echo "</script>";
			*/
			
			/*
			 * uncomment this if no CAS
			 * */
			//$this->redirect('Welcome/welcome', null, 1, '已成功注销！');
			//$this->display();
        }
    // 检查标题是否可用
    public function checkLogin()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (empty($username))
        {
            $this->assign('jumpUrl','/Welcome/welcome');
            $this->success('请输入用户名！');
            //$this->redirect('Welcome/welcome', null, 1, '请输入用户名！');
        } else
            if (empty($password))
            {
		    $this->assign('jumpUrl','/Welcome/welcome');
		    $this->success('请输入密码！');
                //$this->redirect('Welcome/welcome', null, 1, '请输入密码！');
            }
	$pwdMd5 = md5($password);
        $M = new Model();
        $sql = "SELECT USERS.ACCOUNT,USERS.NICKNAME,ROLEUSER.ROLE_NAME
                   FROM THINK_USER USERS,THINK_ROLEUSER ROLEUSER
                   WHERE  USERS.ACCOUNT = ROLEUSER.USER_ACOUNT AND
                   USERS.ACCOUNT = '$username'
                   AND USERS.PASSWORD= '$pwdMd5'";
		//var_dump($sql);
		echo $sql;
		return;
		
        $list = $M->query($sql);
                //var_dump($list);
        if (count($list) <= 0)
        {
            //$this->assign('jumpUrl','/Welcome/welcome');
            //$this->success('用户名或者密码错误，请重新输入！');
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Welcome/welcome'";
			echo "</script>";
            //$this->redirect('Welcome/welcome', null, 1,
             //'用户名或者密码错误，请重新输入！');
            return;
        }
        $user = $list[0];
        if ($user['ROLE_NAME'] == "editor")
        {
            $_SESSION['acount'] = $user['ACCOUNT'];
            $_SESSION['nickname'] = $user['NICKNAME'];
            $_SESSION['role_name'] = $user['ROLE_NAME'];
            $_SESSION['logined'] = 1;
            $_SESSION['loginStyle'] = "local";
            //$this->assign('jumpUrl','/Admin/Index/main');
            //$this->success('登录成功，正在跳转 ...！');
            
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Admin/Index/main'";
			echo "</script>";
/*
            $this->redirect('Admin/Index/main', null, 1,
            '登录成功，正在跳转 ...！');
*/
            return;
        }
        if ($user['ROLE_NAME'] == "viewer")
        {
            $_SESSION['acount'] = $user['ACCOUNT'];
            $_SESSION['nickname'] = $user['NICKNAME'];
            $_SESSION['role_name'] = $user['ROLE_NAME'];
            $_SESSION['logined'] = 1;
            
            //$this->assign('jumpUrl','/Index/main');
            //$this->success('登录成功，正在跳转 ...！');
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Index/main'";
			echo "</script>";
/*
            $this->redirect('/Index/main', null, 1,
             '登录成功，正在跳转 ...！');
*/
            return;
        }
		//log::write(" -> checkLogin  ".$user['ROLE_NAME']);
           if ($user['ROLE_NAME'] == "adminer")
        {
            $_SESSION['acount'] = $user['ACCOUNT'];
            $_SESSION['nickname'] = $user['NICKNAME'];
            $_SESSION['role_name'] = $user['ROLE_NAME'];
            $_SESSION['logined'] = 1;
			//跳转到用户管理页面
			//$this->assign('jumpUrl','/UserManagement/main');
            //$this->success('登录成功，正在跳转 ...！');
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/UserManagement/main'";
			echo "</script>";
/*
            $this->redirect('/UserManagement/main', null, 1,
            '登录成功，正在跳转 ...！');
*/
            return;
        }
		echo "<script language='javascript' type='text/javascript'>";
		echo "top.location.href = 'http://".$_SERVER['HTTP_HOST']."/index.php/Welcome/welcome'";
		echo "</script>";
	    //$this->assign('jumpUrl','/Welcome/welcome');
	    //$this->success('用户名或者密码错误，请重新输入！');
    }

}
?>
