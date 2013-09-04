<?php

class MailAction extends Action
{
	
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
		$sql = "SELECT VTIME,VAUTHOR,TCONTENT,EDITID FROM THINK_TBEDITRECORD";
		$logList = $M->query($sql);
		if (count($logList)>0) {
			for($i=0;$i<count($logList);$i++)
			{
				switch($logList[$i]['EDITID']) {
					case 'codeCollection':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$codeCollection.=$log->author." 于 ".$log->time." ".$log->content."\r\n";
						//array_push($codeCollection,$log);
						break;
					case 'rule':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$rulestr.=$log->author." 于 ".$log->time." ".$log->content."\r\n";
						//array_push($rule,$log);
						
						break;
					case 'class':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$classstr.=$log->author." 于 ".$log->time." ".$log->content."\r\n";
						//array_push($class,$log);
						
						break;
					case 'interface':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$interfacestr.=$log->author." 于 ".$log->time." ".$log->content."\r\n";
						//array_push($interface,$log);
						break;
				}
			}
		}
		
		
		$Logs = array('interface'=>$interfacestr,'rule'=>$rulestr
				,'codeCollection'=>$codeCollectionstr,'class'=>$classstr);
		return $logs;
	}
	
	public function sendOrderMail() {
		//首先获取用户列表
		$UserListM = new Model();
		$sqlGetUsers = "SELECT ACCOUNT,NICKNAME,EMAIL  FROM THINK_USER ";
		$UserList = $UserListM->query($sqlGetUsers);
		if (count($UserList)>0) {
			//$logs = $this->getChangeLog();
			for($i=0;$i<count($UserList);$i++)
			{
				/*
				//获取每个用户订阅的项目
				$userAccount = $UserList[$i]['ACCOUNT'];
				$sqlGetUserOrder = "SELECT USER_ACOUNT,ITEM_NAME FROM T_ORDER_ITEM where USER_ACOUNT = '$userAcount' ";
				$GetOrderItemM = new Model();
				$orderList=$GetOrderItemM->query($sqlGetUsers);
				if (count($orderList)>0) {
					$content ="";
					//将每个用户订阅的内容进行组装
					for($j=0;$j<count($orderList);$j++)
					{
						switch($orderList[$j]['ITEM_NAME']) {
							case 'codeCollection':
								$content.="代码集方面：\r\n".$logs['codeCollection'];
								break;
							case 'class':
								break;
							case 'rule':
								break;
							case 'interface':
								break;
							
						}
					}
					//$this->sendMail($content);
					
				}
				*/
				$userAccount = $UserList[$i]['NICKNAME'];
				$hello = "您好";
				$hello= mb_convert_encoding( $hello,'utf-8','GB2312');
				$content = $userAccount."<br>".$hello;
				log::write("sendOrderMail-> ".$content);
				log::write("sendOrderMail-> ".mb_detect_encoding($content));
				
				//$content = "您好";
				//$this->sendMail($content);//测试
				//$this->sendMailPro($userAccount,"2011-10-27","GB2312");
				//$this->sendMailPro($userAccount,"2011-10-29",$content,$content,$content,$content);
				//$this->sendMailPro($userAccount,"2011-10-27",$content,$content,$content,$content);
				$this->sendMailPro($userAccount,"测试");
			}
		}
		
	}
	//public function sendMailPro($name,$date,$class,
	//	$interface,$rule,$codeCollection)
	public function sendMailPro($name,$date)
	{
		/*
		if (empty($name)) {
			return;
		}
		 */	
		//echo "sendMail";
		require_once('class.phpmailer.php');
		require_once('class.smtp.php');	
		
		$mail=new PHPMailer();
		
		// 设置PHPMailer使用SMTP服务器发送Email
		$mail->IsSMTP();
		
		// 设置邮件的字符编码，若不指定，则为'UTF-8'
		//		$mail->CharSet='GB2312';
		
		// 添加收件人地址，可以多次使用来添加多个收件人
		$mail->AddAddress('ssor@qq.com');
		
		// 设置邮件正文
		//$message='用WordPress的代码发送的Email';
		//$mail->Body=$message;
		$body = file_get_contents('contents.html');
		//处理邮件内容
		$body=str_replace("@name",$name,$body);
		$body=str_replace("@date",$date,$body);
		
		if (empty($class) ){
			$body=str_replace("@class","",$body);
			$body=str_replace("@cla","",$body);
			
		} else {
			$body=str_replace("@class","数据子类：",$body);
			$body=str_replace("@cla",$class,$body);
			
		}
		if (empty($interface) ){
			$body=str_replace("@interface","",$body);
			$body=str_replace("@interf","",$body);
			
		} else {
			$body=str_replace("@interface","接口规范：",$body);
			$body=str_replace("@interf",$interface,$body);
			
		}
		if (empty($codeCollection) ){
			$body=str_replace("@codeCollection","",$body);
			$body=str_replace("@code","",$body);
			
		} else {
			$body=str_replace("@codeCollection","代码集：",$body);
			$body=str_replace("@code",$codeCollection,$body);
			
		}
		if (empty($rule) ){
			$body=str_replace("@rule","",$body);
			$body=str_replace("@rl","",$body);
			
		} else {
			$body=str_replace("@rule","编码规则：",$body);
			$body=str_replace("@rl",$rule,$body);
			
		}
		log::write($body);
		//$mail->MsgHTML(file_get_contents('contents.html'));
		$mail->MsgHTML($body);
		
		// 设置邮件头的From字段。
		// 对于网易的SMTP服务，这部分必须和你的实际账号相同，否则会验证出错。
		$mail->From='zhangquanzhi110@163.com';
		
		// 设置发件人名字
		$mail->FromName='数据标准管理系统';
		
		// 设置邮件标题
		$mail->Subject='数据标准管理系统数据标准变更通知';
		
		// 设置SMTP服务器。这里使用网易的SMTP服务器。
		$mail->Host='smtp.163.com';
		
		// 设置为“需要验证”
		$mail->SMTPAuth=true;
		
		// 设置用户名和密码，即网易邮件的用户名和密码。
		$mail->Username='zhangquanzhi110';
		$mail->Password='0785150790';
		
		// 发送邮件。
		$mail->Send();
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
		$mail->AddAddress('ssor@qq.com');
		
		// 设置邮件正文
		//$message='用WordPress的代码发送的Email';
		//$mail->Body=$message;
		//$mail->MsgHTML(file_get_contents('contents.html'));
		$mail->MsgHTML($message);
		
		// 设置邮件头的From字段。
		// 对于网易的SMTP服务，这部分必须和你的实际账号相同，否则会验证出错。
		$mail->From='zhangquanzhi110@163.com';
		
		// 设置发件人名字
		$mail->FromName='Wang Jinbo';
		
		// 设置邮件标题
		$mail->Subject='Test Mail';
		
		// 设置SMTP服务器。这里使用网易的SMTP服务器。
		$mail->Host='smtp.163.com';
		
		// 设置为“需要验证”
		$mail->SMTPAuth=true;
		
		// 设置用户名和密码，即网易邮件的用户名和密码。
		$mail->Username='zhangquanzhi110';
		$mail->Password='0785150790';
		
		// 发送邮件。
		$mail->Send();
	}	
	
}

// for this test, simply print that the authentication was successfull
?>
