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
		
		// ��ʼ��phpcas
		//�趨�����ַ,�˿ں�,CAS�ķ��ʵ�ַ
		phpCAS::client(CAS_VERSION_2_0,'211.68.70.17',6001,'sso');
		//phpCAS::client(CAS_VERSION_2_0,'211.68.70.15',7001,'sso');
		
		// ��ʹ��SSL����У��
		phpCAS::setNoCasServerValidation();
		
		// ����CAS����֤
		phpCAS::forceAuthentication();
		
		//��ʱ�����֤�����
		//����û�������ͨ��phpCAS::getUser()
		//phpCAS::getUser();
		$arrUserDetails = phpCAS::getUserDetails();
		$this->assign('UserDetails', $arrUserDetails);
		$this->display();
	}	
}

// for this test, simply print that the authentication was successfull
?>
