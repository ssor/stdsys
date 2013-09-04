<?php

//
// phpCAS simple client
//

// import phpCAS lib
include_once('./CAS-1.0.1/CAS.php');

phpCAS::setDebug();

// 初始化phpcas
//设定服务地址,端口号,CAS的访问地址
phpCAS::client(CAS_VERSION_2_0,'211.68.70.15',7001,'sso');

// 不使用SSL服务校验
phpCAS::setNoCasServerValidation();

// 访问CAS的验证
phpCAS::forceAuthentication();

//这时候就验证完毕了
//获得用户名可以通过phpCAS::getUser()
//phpCAS::getUser();

//登出
if (isset($_REQUEST['logout'])) {
		phpCAS::logout();
}

// for this test, simply print that the authentication was successfull
?>
<html>
  <head>
    <title>phpCAS simple client</title>
  </head>
  <body>
    <h1>Successfull Authentication!</h1>
    <p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <p>phpCAS userRoles is <b><?php echo phpCAS::getUserRoles(); ?></b>.</p>
    <p>phpCAS userAppsyses is <b><?php echo phpCAS::getUserAppsyses(); ?></b>.</p>
    <p>
    	phpCAS userDetails is <b>
    	<?php 
    		$arrUserDetails = phpCAS::getUserDetails();
    		if(!empty($arrUserDetails)) {
  				echo 'id='.$arrUserDetails['id'].'<br />';
  				echo 'syncInfoId='.$arrUserDetails['syncInfoId'].'<br />';
  				echo 'userName='.$arrUserDetails['userName'].'<br />';
  				echo 'gender='.$arrUserDetails['gender'].'<br />';
  				echo 'userId='.$arrUserDetails['userId'].'<br />';
  				echo 'password='.$arrUserDetails['password'].'<br />';
  				echo 'idCard='.$arrUserDetails['idCard'].'<br />';
  				echo 'userType='.$arrUserDetails['userType'].'<br />';
  				echo 'domain='.$arrUserDetails['domain'].'<br />';
  				echo 'userStatus='.$arrUserDetails['userStatus'].'<br />';
  				echo 'orgCode='.$arrUserDetails['orgCode'].'<br />';
  				echo 'formerName='.$arrUserDetails['formerName'].'<br />';
  				echo 'firstName='.$arrUserDetails['firstName'].'<br />';
  				echo 'lastName='.$arrUserDetails['lastName'].'<br />';
  				echo 'nativePlace='.$arrUserDetails['nativePlace'].'<br />';
  				echo 'birthday='.$arrUserDetails['birthday'].'<br />';
  				echo 'politicalStatus='.$arrUserDetails['politicalStatus'].'<br />';
  				echo 'address='.$arrUserDetails['address'].'<br />';
  				echo 'postCode='.$arrUserDetails['postCode'].'<br />';
  				echo 'officePhone='.$arrUserDetails['officePhone'].'<br />';
  				echo 'mobilePhone='.$arrUserDetails['mobilePhone'].'<br />';
  				echo 'homePhone='.$arrUserDetails['homePhone'].'<br />';
  				echo 'fax='.$arrUserDetails['fax'].'<br />';
  				echo 'email='.$arrUserDetails['email'].'<br />';
  				echo 'initialPassword='.$arrUserDetails['initialPassword'].'<br />';
  				echo 'passwordQuestion='.$arrUserDetails['passwordQuestion'].'<br />';
  				echo 'passwordAnswer='.$arrUserDetails['passwordAnswer'].'<br />';
  				echo 'description='.$arrUserDetails['description'].'<br />';
  				echo 'operateFlag='.$arrUserDetails['operateFlag'].'<br />';
  				echo 'operaterId='.$arrUserDetails['operaterId'].'<br />';
  				echo 'operaterName='.$arrUserDetails['operaterName'].'<br />';
  				echo 'operateTime='.$arrUserDetails['operateTime'].'<br />';
				}
    	?>
    </b>.
   	</p>
    <p>phpCAS version is <b><?php echo phpCAS::getVersion(); ?></b>.</p>
    <p><a href="?logout=">Logout</a></p>
  </body>
</html>