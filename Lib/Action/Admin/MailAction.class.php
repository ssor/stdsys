<?php

class MailAction extends Action
{
	
	private $addressFrom ='';
	private $host='';
	private $userName ='';
	private $pwd='';
	private $authen = true;
		public function checkGB2312($str) {
		$cod = mb_check_encoding($str,"GB2312");
		if("GB2312" != $cod ||  empty($cod))
		{
			$str = mb_convert_encoding( $str,'GB2312','UTF-8'); 
		}
		return $str;
	}
	private function mailConfig() {
		$M = new Model();
		$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'email'";
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$this->addressFrom=$list[0]['VVALUE'];
		}
		else
		{
			return false;
		}
		$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'authen'";
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			if ($list[0]['VVALUE']=='true') {
				$this->authen=true;
			}
			
			if ($list[0]['VVALUE']== 'false') {
				$this->authen=false;
			}
		}
		else
		{
			return false;
		}
		$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'pwd'";
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$this->pwd=$list[0]['VVALUE'];
		}
		else
		{
			return false;
		}
		$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'user'";
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$this->userName=$list[0]['VVALUE'];
		}
		else
		{
			return false;
		}
		$sqlSelect = "select VKEY,VVALUE from T_CONFIG where VKEY = 'smtp'";
		$list = $M->query($sqlSelect);
		if (count($list)>0) {
			$this->host=$list[0]['VVALUE'];
		}
		else
		{
			return false;
		}
		return true;
	}
	
	public function getChangeLog() {
		require_once('changeLog.php');
		
		$interfacestr = "";
		$classstr="";
		$rulestr="";
		$codeCollectionstr="";
		$interface =array();
		$class=array();
		$rule=array();
		$codeCollection=array();
		$M = new Model();
		
		$date=getdate();
		$todayStart = 
			mktime(0,0,0
				,$date['mon'],$date['mday'],$date['year']);
		$vTimeStart = date("Y-m-d H:i:s",$todayStart);
		$vTimeEnd = date("Y-m-d H:i:s",strtotime('+1 day',$todayStart));
		
		//$sql = "SELECT VTIME,VAUTHOR,TCONTENT,EDITID FROM THINK_TBEDITRECORD where VTIME>'$vTimeStart' and VTIME < '$vTimeEnd'";
		$sql = "SELECT VTIME,VAUTHOR,TCONTENT,EDITID FROM THINK_TBEDITRECORD where VTIME>'$vTimeStart' and VTIME < '$vTimeEnd'";
		//echo $sql;
		//return;
		$logList = $M->query($sql);
		//log::write("getChangeLog -> count = ".count($logList));
		//log::write("getChangeLog ->   ".$logList[0]['EDITID']);
		
		if (count($logList)>0) {
			for($i=0;$i<count($logList);$i++)
			{
				switch($logList[$i]['EDITID']) {
					case 'codeCollection':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$codeCollectionstr.=$log->author." 于 ".$log->time." ".$log->content."<br>";
						//array_push($codeCollection,$log);
						break;
					case 'rule':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$rulestr.=$log->author." 于 ".$log->time." ".$log->content."<br>";
						//array_push($rule,$log);
						
						break;
					case 'class':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$classstr.=$log->author." 于 ".$log->time." ".$log->content."<br>";
						//array_push($class,$log);
						
						break;
					case 'interface':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$interfacestr.=$log->author." 于 ".$log->time." ".$log->content."<br>";
						//array_push($interface,$log);
						break;
				}
			}
		}
		
		//log::write("getChangeLog -> ".$codeCollectionstr);
		$Logs = array('interface'=>$interfacestr,'rule'=>$rulestr
				,'codeCollection'=>$codeCollectionstr,'class'=>$classstr);
		//var_dump($Logs);
		//echo "<br>";
		return $Logs;
	}
	
	public function sendOrderMail() {
		
		//echo "sendOrderMail->";
		//return;
		if (C('SEND_ORDER_MAIL')== '0') {
			echo "config no mail send <br>";
			return;
		}
		
		//检查另一台服务器是否已经发出邮件
		date_default_timezone_set("Asia/Shanghai");
		$date=getdate();
		$todayStart = 
			mktime(0,0,0
				,$date['mon'],$date['mday'],$date['year']);
		$vTimeStart = date("Y-m-d H:i:s",$todayStart);
		$vTimeEnd = date("Y-m-d H:i:s",strtotime('+1 day',$todayStart));
		$MCheck=new Model();
		$sqlCheck="select * from T_MAIL_LOG where MAIL_TIME > '$vTimeStart' and MAIL_TIME <'$vTimeEnd'";
		$checkResult=$MCheck->query($sqlCheck);
		if (count($checkResult)>0 ){
			echo "mail already sent";
			return;
		}
		$time = date("Y-m-d H:i:s");
		$sqlInsertCheck="insert into T_MAIL_LOG(MAIL_TIME) values('$time')";
		if (!$MCheck->execute($sqlInsertCheck)) {
			echo "insert mail log error <br>";
			return;
		}
		//echo $sqlInsertCheck."<br>";
		echo "send mail go on";
		//return;
		
		if(!$this->mailConfig())
		{
			return;
		}
		//首先获取用户列表
		$UserListM = new Model();
		$sqlGetUsers = "SELECT ACCOUNT,NICKNAME,EMAIL  FROM THINK_USER ";
		$UserList = $UserListM->query($sqlGetUsers);
		//var_dump($UserList);
		//return;
		
		//echo "<br>".$UserList[0]['ACCOUNT'];
		if (count($UserList)>0) {
			$logs = $this->getChangeLog();
			//var_dump($logs);
			//echo "<br>";
			for($i=0;$i<count($UserList);$i++)
			{
				//获取每个用户订阅的项目
				$userName = $UserList[$i]['NICKNAME'];
				$userAccount = $UserList[$i]['ACCOUNT'];
				$sqlGetUserOrder = "SELECT USER_ACOUNT,ITEM_NAME FROM T_ORDER_ITEM where USER_ACOUNT = '$userAccount' ";
				//log::write("sendOrderMail sqlGetUserOrder-> ".$sqlGetUserOrder);
				//echo $sqlGetUserOrder."<br>";
				$GetOrderItemM = new Model();
				$orderList=$GetOrderItemM->query($sqlGetUserOrder);
				//var_dump($orderList);
				if (count($orderList)>0) {
					$contentcodeCollection ="";
					$contentclass ="";
					$contentrule ="";
					$contentinterface ="";
					//将每个用户订阅的内容进行组装
					for($j=0;$j<count($orderList);$j++)
					{
						//echo "ITEM_NAME ->".$orderList[$j]['ITEM_NAME'];
						//echo "<br>";
						switch($orderList[$j]['ITEM_NAME']) {
							case 'codeCollection':
								$contentcodeCollection.=$logs['codeCollection'];
								//log::write("sendOrderMail codeCollectionstr-> ".$codeCollectionstr);
								
								break;
							case 'class':
								$contentclass.=$logs['class'];
								//echo "contentclass->".$contentclass."<br>";
								break;
							case 'rule':
								$contentrule.=$logs['rule'];
								break;
							case 'interface':
								$contentinterface.=$logs['interface'];
								break;
							
						}
					}
					
						//echo "sendOrderMail-> 111";
						//return;
					//$this->sendMail($content);
					if (!empty($contentcodeCollection)||
							!empty($contentclass)||
							!empty($contentrule)||
							!empty($contentinterface)) {
								
						
						if (!empty( $UserList[$i]['EMAIL'])) {
							date_default_timezone_set("Asia/Shanghai");
							$vTime = date("Y-m-d");
							$this->sendMailPro($userName,$vTime,
									$contentclass,$contentinterface,$contentrule,$contentcodeCollection,$UserList[$i]['EMAIL']);
						}
					}
					
				}
				/*
				$userAccount = $UserList[$i]['NICKNAME'];
				$hello = "您好";
				$content = $userAccount."<br>".$hello;
				log::write("sendOrderMail-> ".$content);
				log::write("sendOrderMail-> ".mb_detect_encoding($content));
							
				//$content = "您好";
				//$this->sendMail($content);//测试
				//$this->sendMailPro($userAccount,"2011-10-27","GB2312");
				//$this->sendMailPro($userAccount,"2011-10-29",$content,$content,$content,$content);
				$this->sendMailPro($userAccount,"2011-10-30",$content,$content,$content,$content);
				//$this->sendMailPro($userAccount,"测试");
				*/
			}
		}
		
	}
	public function sendMailPro($name,$date,$class,
		$interface,$rule,$codeCollection,$mailTo)
	//public function sendMailPro($name,$date)
	{
		
		//echo "sendMailPro->11";
		if (empty($name)) {
			return;
		}
		if (empty($mailTo)) {
			return;
		}
		//echo "sendMailPro->";
		//return;
		
		//echo "sendMail";
		require_once('class.phpmailer.php');
		require_once('class.smtp.php');	
		
		$mail=new PHPMailer();
		
		// 设置PHPMailer使用SMTP服务器发送Email
		$mail->IsSMTP();
		
		// 设置邮件的字符编码，若不指定，则为'UTF-8'
		$mail->CharSet='GB2312';//如果使用gb2312编码一定要打开
		
		// 添加收件人地址，可以多次使用来添加多个收件人
		$mail->AddAddress($mailTo);
		
		// 设置邮件正文
		//$message='用WordPress的代码发送的Email';
		//$mail->Body=$message;
		$body = file_get_contents('contentsgbk.html');
		//处理邮件内容
		$name=$this->checkGB2312($name);
		$date=$this->checkGB2312($date);
		$body=str_replace("@name",$name,$body);
		$body=str_replace("@date",$date,$body);
		
		if (empty($class) ){
			$body=str_replace("@class","",$body);
			$body=str_replace("@cla","",$body);
			
		} else {
			$className='数据子类：';
			$className = $this->checkGB2312($className);
			$class=$this->checkGB2312($class);

			$body=str_replace("@class",$className,$body);
			$body=str_replace("@cla",$class,$body);
			
		}
		if (empty($interface) ){
			$body=str_replace("@interface","",$body);
			$body=str_replace("@interf","",$body);
			
		} else {
			$interfaceName="接口规范：";
			$interfaceName = $this->checkGB2312($interfaceName);
			$interface = $this->checkGB2312($interface);
			
			$body=str_replace("@interface",$interfaceName,$body);
			$body=str_replace("@interf",$interface,$body);
			
		}
		if (empty($codeCollection) ){
			$body=str_replace("@codeCollection","",$body);
			$body=str_replace("@code","",$body);
			
		} else {
			$codeCollectionName = '代码集：';
			$codeCollectionName=$this->checkGB2312($codeCollectionName);
			$codeCollection=$this->checkGB2312($codeCollection);
			$body=str_replace("@codeCollection",$codeCollectionName,$body);
			$body=str_replace("@code",$codeCollection,$body);
			
		}
		if (empty($rule) ){
			$body=str_replace("@rule","",$body);
			$body=str_replace("@rl","",$body);
			
		} else {
			$ruleName='编码规则：';
			$ruleName=$this->checkGB2312($ruleName);
			$rule= $this->checkGB2312($rule);
			
			$body=str_replace("@rule",$ruleName,$body);
			$body=str_replace("@rl",$rule,$body);
			
		}
		//log::write($body);
		//$mail->MsgHTML(file_get_contents('contents.html'));
		$mail->MsgHTML($body);
		
		// 设置邮件头的From字段。
		// 对于网易的SMTP服务，这部分必须和你的实际账号相同，否则会验证出错。
		$mail->From=$this->addressFrom;
		
		// 设置发件人名字
		//$mail->FromName='数据标准管理系统';
		
		// 设置邮件标题
		//$mail->Subject='数据标准管理系统数据标准变更通知';
		$subject = '数据标准管理系统数据标准变更通知';
		$subject=$this->checkGB2312($subject);
		$mail->Subject=$subject;
		// 设置SMTP服务器。这里使用网易的SMTP服务器。
		$mail->Host=$this->host;
		//$mail->Host='smtp.163.com';
		
		// 设置为“需要验证”
		$mail->SMTPAuth=$this->authen;
		//$mail->SMTPAuth=true;
		
		// 设置用户名和密码，即网易邮件的用户名和密码。
		$mail->Username=$this->userName;
		$mail->Password=$this->pwd;
		
		// 发送邮件。
		$mail->Send();
	}
	public function sendMailTest($mailTo='') {
		//$mailTo = $_POST['email'];
		
		if (empty($mailTo)) {
			return;
		}
		$this->mailConfig();
		
		require_once('class.phpmailer.php');
		require_once('class.smtp.php');	
		
		$mail=new PHPMailer();
		
		// 设置PHPMailer使用SMTP服务器发送Email
		$mail->IsSMTP();
		
		// 设置邮件的字符编码，若不指定，则为'UTF-8'
		$mail->CharSet='GB2312';
		
		// 添加收件人地址，可以多次使用来添加多个收件人
		$mail->AddAddress($mailTo);
		
		// 设置邮件正文
		$message='您好，如果您能收到本邮件，说明数据标准管理系统能够给你发送邮件通知！';
		$message=$this->checkGB2312($message);
		$mail->Body=$message;		
		
		//$body = file_get_contents('contentsgbk.html');
		
		//$name = '发件人';
		//$name=$this->checkGB2312($name);
		//$body=str_replace("@name",$name,$body);
		//$mail->MsgHTML($body);

		$mail->From=$this->addressFrom;
		
		// 设置发件人名字
		//$mail->FromName='数据标准管理系统';
		
		// 设置邮件标题
		$subject = '数据标准管理系统数据标准变更通知测试';
		$subject=$this->checkGB2312($subject);
		$mail->Subject=$subject;
		
		// 设置SMTP服务器。这里使用网易的SMTP服务器。
		$mail->Host=$this->host;
		//$mail->Host='smtp.163.com';
		
		// 设置为“需要验证”
		$mail->SMTPAuth=$this->authen;
		//$mail->SMTPAuth=true;
		
		// 设置用户名和密码，即网易邮件的用户名和密码。
		$mail->Username=$this->userName;
		$mail->Password=$this->pwd;
		
		// 发送邮件。
		return $mail->Send();
		
	}	
	public function sendMail($message)
	{
		//echo "sendMail";
		require_once('class.phpmailer.php');
		require_once('class.smtp.php');	
		
		$mail=new PHPMailer();
		
		// 设置PHPMailer使用SMTP服务器发送Email
		$mail->IsSMTP();
		
		// 设置邮件的字符编码，若不指定，则为'UTF-8'
		//		$mail->CharSet='GB2312';
		
		// 添加收件人地址，可以多次使用来添加多个收件人
		$mail->AddAddress('');
		
		// 设置邮件正文
		//$message='用WordPress的代码发送的Email';
		//$mail->Body=$message;
		//$mail->MsgHTML(file_get_contents('contents.html'));
		$mail->MsgHTML($message);
		
		// 设置邮件头的From字段。
		// 对于网易的SMTP服务，这部分必须和你的实际账号相同，否则会验证出错。
		$mail->From='';
		
		// 设置发件人名字
		$mail->FromName='Wang Jinbo';
		
		// 设置邮件标题
		$mail->Subject='Test Mail';
		
		// 设置SMTP服务器。这里使用网易的SMTP服务器。
		$mail->Host='smtp.163.com';
		
		// 设置为“需要验证”
		$mail->SMTPAuth=true;
		
		// 设置用户名和密码，即网易邮件的用户名和密码。
		$mail->Username='';
		$mail->Password='';
		
		// 发送邮件。
		$mail->Send();
	}	
	
}

// for this test, simply print that the authentication was successfull
?>
