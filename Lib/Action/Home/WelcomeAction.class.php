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
/*
        public function welcome()
        {
		$this->display();
	}
*/
	///*
    public function welcome()
    {	
        Vendor("CAS.CAS");
        phpCAS::setDebug();
        phpCAS::client(CAS_VERSION_2_0,'211.68.70.10',8002,'sso');
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
			//echo $sql;
			//return;
			
			$list = $M->query($sql);
			if (count($list) <= 0)
			{
				
				$this->assign('jumpUrl','/Welcome/noUser');
				$this->success('用户名或者密码错误，请重新输入，正在跳转 ...！');
				
				return;
			}
			//把用户的所有可能的角色都保存到session中
			for($i=0;$i<count($list);$i++)
			{
				$user = $list[$i];
				switch($user['ROLE_NAME']) {
					case 'adminer':
						//$_SESSION['role_name_adminer'] = $user['ROLE_NAME'];
						$_SESSION['role_name']['adminer'] = $user['ROLE_NAME'];
						break;
					case 'viewer':
						//$_SESSION['role_name_viewer'] = $user['ROLE_NAME'];
						$_SESSION['role_name']['viewer'] = $user['ROLE_NAME'];
						break;
					case 'editor':
						//$_SESSION['role_name_editor'] = $user['ROLE_NAME'];
						$_SESSION['role_name']['editor'] = $user['ROLE_NAME'];
						break;
				}
			}
			$user = $list[0];
			$_SESSION['acount'] = $user['ACCOUNT'];
			$_SESSION['nickname'] = $user['NICKNAME'];
			$_SESSION['logined'] = 1;
			$_SESSION['loginStyle'] = "cas";
			$_SESSION['ROLE_NAME'] = "";
			$_SESSION['ROLE_COUNT']=count($list);
			if (!empty($_SESSION['role_name']['editor']))
			{
				$_SESSION['ROLE_NAME'] = "editor";
				
				$host = $_SERVER['HTTP_HOST'];
				echo "<script language='javascript' type='text/javascript'>";
				echo "top.location.href = 'http://".$host."/index.php/Admin/Index/main'";
				echo "</script>";
				return;
			}
			if (!empty($_SESSION['role_name']['viewer']))
			{
				
				$_SESSION['ROLE_NAME'] = "viewer";
				//echo $_SESSION['ROLE_NAME'];
				//return;
				
				$host = $_SERVER['HTTP_HOST'];
				echo "<script language='javascript' type='text/javascript'>";
				echo "top.location.href = 'http://".$host."/index.php/Index/main'";
				echo "</script>";
				return;
			}
			if (!empty($_SESSION['role_name']['adminer']) && empty($_SESSION['ROLE_NAME']))
			{
				$_SESSION['ROLE_NAME'] = "adminer";
				
				//跳转到用户管理页面
				$host = $_SERVER['HTTP_HOST'];
				echo "<script language='javascript' type='text/javascript'>";
				echo "top.location.href = 'http://".$host."/index.php/UserManagement/main'";
				echo "</script>";
				return;
			}
			
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$_SERVER['HTTP_HOST']."/index.php/Welcome/welcome'";
			echo "</script>";
		}
	}
	//*/
	public function logout()
	{		
		if (empty($_SESSION['changeRole']) || $_SESSION['changeRole']!=1) {
			
			$_SESSION['acount'] = "";
			$_SESSION['nickname'] = "";
			$_SESSION['role_name'] = "";
			$_SESSION['logined'] = 0;	
			
			session_start();
			session_unset();
			session_destroy();			
			
			echo "<script language='javascript' type='text/javascript'>top.window.close();</script>";			
			
		}
		$_SESSION['changeRole']=0;
/*
			Vendor("CAS.CAS");
			phpCAS::setDebug();
			phpCAS::client(CAS_VERSION_2_0,'211.68.70.10',9001,'sso');

			
			

			$cas_url = 'http://211.68.70.10:8002/sso/logout?logoutSuccessUrl=';
			$service = 'http://211.68.70.14:8002/index.php/Welcome/welcome';
			$cas_url = $cas_url. urlencode($service); 
			//header('Location: '.$cas_url);
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = '".$cas_url."'";
			echo "</script>";

			$this->display();
*/
	}
	public function changeRole() {
		//var_dump($_SESSION['role_name']);
		//return;
		$_SESSION['changeRole']=1;
		if ($_SESSION['logined']==1) {
			
			$host = $_SERVER['HTTP_HOST'];
			$url="";
			$url.= "<script language='javascript' type='text/javascript'>";
			switch($_SESSION['ROLE_NAME']) {
				case 'adminer':
					if (!empty($_SESSION['role_name']['editor'])) {
						$_SESSION['ROLE_NAME']	=$_SESSION['role_name']['editor'];
					}
					else if(!empty($_SESSION['role_name']['viewer']))
						{
							$_SESSION['ROLE_NAME']	=$_SESSION['role_name']['viewer'];
							
						}
					break;
				case 'editor':
					if(!empty($_SESSION['role_name']['viewer']))
					{
						$_SESSION['ROLE_NAME']	=$_SESSION['role_name']['viewer'];
						
					}
					else if (!empty($_SESSION['role_name']['adminer'])) {
							$_SESSION['ROLE_NAME']	=$_SESSION['role_name']['adminer'];
						}
					break;
				case 'viewer':
					if (!empty($_SESSION['role_name']['adminer'])) {
						$_SESSION['ROLE_NAME']	=$_SESSION['role_name']['adminer'];
					}
					else if(!empty($_SESSION['role_name']['editor']))
						{
							$_SESSION['ROLE_NAME']	=$_SESSION['role_name']['editor'];
							
						}
					break;
			}
			//echo $_SESSION['ROLE_NAME'];
			//return;
			
			switch($_SESSION['ROLE_NAME']) {
				case 'adminer':
					$url.= "top.location.href = 'http://".$host."/index.php/UserManagement/main'";
					break;
				case 'editor':
					$url.= "top.location.href = 'http://".$host."/index.php/Admin/Index/main'";
					break;
				case 'viewer':
					$url.= "top.location.href = 'http://".$host."/index.php/Index/main'";
					break;
			}
			$url.= "</script>";
			
			echo $url;
			
		} 
		else
		{
			
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$_SERVER['HTTP_HOST']."/index.php/Welcome/welcome'";
			echo "</script>";
		}
	}
    // 检查标题是否可用
    public function checkLogin()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
		$_SESSION['role_name']="";
		
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
		//echo $sql;
		//return;
        $list = $M->query($sql);
                //var_dump($list);
		//return;
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
		//把用户的所有可能的角色都保存到session中
		for($i=0;$i<count($list);$i++)
		{
			$user = $list[$i];
			switch($user['ROLE_NAME']) {
				case 'adminer':
					//$_SESSION['role_name_adminer'] = $user['ROLE_NAME'];
					$_SESSION['role_name']['adminer'] = $user['ROLE_NAME'];
					break;
				case 'viewer':
					//$_SESSION['role_name_viewer'] = $user['ROLE_NAME'];
					$_SESSION['role_name']['viewer'] = $user['ROLE_NAME'];
					break;
				case 'editor':
					//$_SESSION['role_name_editor'] = $user['ROLE_NAME'];
					$_SESSION['role_name']['editor'] = $user['ROLE_NAME'];
					break;
			}
		}
		
		//var_dump($_SESSION['role_name']);
		//return;
		$user = $list[0];
		$_SESSION['acount'] = $user['ACCOUNT'];
		$_SESSION['nickname'] = $user['NICKNAME'];
		$_SESSION['logined'] = 1;
		$_SESSION['loginStyle'] = "local";
		$_SESSION['ROLE_NAME'] = "";
		$_SESSION['ROLE_COUNT']=count($list);
		
		if (!empty($_SESSION['role_name']['editor']))
		{
			$_SESSION['ROLE_NAME'] = "editor";
			
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Admin/Index/main'";
			echo "</script>";
			return;
		}
		//echo $_SESSION['role_name']['viewer']." 111<br>";
		//return;
		if (!empty($_SESSION['role_name']['viewer']))
		{

			$_SESSION['ROLE_NAME'] = "viewer";
			//echo $_SESSION['ROLE_NAME'];
			//return;
			
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/Index/main'";
			echo "</script>";
			return;
		}
		if (!empty($_SESSION['role_name']['adminer']) && empty($_SESSION['ROLE_NAME']))
		{
			$_SESSION['ROLE_NAME'] = "adminer";
			
			//跳转到用户管理页面
			$host = $_SERVER['HTTP_HOST'];
			echo "<script language='javascript' type='text/javascript'>";
			echo "top.location.href = 'http://".$host."/index.php/UserManagement/main'";
			echo "</script>";
			return;
		}
		echo "<script language='javascript' type='text/javascript'>";
		echo "top.location.href = 'http://".$_SERVER['HTTP_HOST']."/index.php/Welcome/welcome'";
		echo "</script>";
	}
	
}
?>
