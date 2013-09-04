<?php

//
// phpCAS simple client
//
class testMAction extends Action
{
	public function testCas()
	{
		
		// import phpCAS lib
		
		Vendor("CAS.CAS");
		phpCAS::setDebug();
		
		// 初始化phpcas
		//设定服务地址,端口号,CAS的访问地址
		phpCAS::client(CAS_VERSION_2_0,'211.68.70.17',6001,'sso');
		//phpCAS::client(CAS_VERSION_2_0,'211.68.70.15',7001,'sso');
		
		// 不使用SSL服务校验
		phpCAS::setNoCasServerValidation();
		
		// 访问CAS的验证
		phpCAS::forceAuthentication();
		
		//这时候就验证完毕了
		//获得用户名可以通过phpCAS::getUser()
		//phpCAS::getUser();
		$arrUserDetails = phpCAS::getUserDetails();
		$this->assign('UserDetails', $arrUserDetails);
		$this->display();
	}	
}

// for this test, simply print that the authentication was successfull
?>
