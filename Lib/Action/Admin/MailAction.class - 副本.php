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
						$codeCollection.=$log->author." �� ".$log->time." ".$log->content."\r\n";
						//array_push($codeCollection,$log);
						break;
					case 'rule':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$rulestr.=$log->author." �� ".$log->time." ".$log->content."\r\n";
						//array_push($rule,$log);
						
						break;
					case 'class':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$classstr.=$log->author." �� ".$log->time." ".$log->content."\r\n";
						//array_push($class,$log);
						
						break;
					case 'interface':
						$log = new changeLog($logList[$i]['VTIME'],$logList[$i]['VAUTHOR'],$logList[$i]['TCONTENT']);
						$interfacestr.=$log->author." �� ".$log->time." ".$log->content."\r\n";
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
		//���Ȼ�ȡ�û��б�
		$UserListM = new Model();
		$sqlGetUsers = "SELECT ACCOUNT,NICKNAME,EMAIL  FROM THINK_USER ";
		$UserList = $UserListM->query($sqlGetUsers);
		if (count($UserList)>0) {
			//$logs = $this->getChangeLog();
			for($i=0;$i<count($UserList);$i++)
			{
				/*
				//��ȡÿ���û����ĵ���Ŀ
				$userAccount = $UserList[$i]['ACCOUNT'];
				$sqlGetUserOrder = "SELECT USER_ACOUNT,ITEM_NAME FROM T_ORDER_ITEM where USER_ACOUNT = '$userAcount' ";
				$GetOrderItemM = new Model();
				$orderList=$GetOrderItemM->query($sqlGetUsers);
				if (count($orderList)>0) {
					$content ="";
					//��ÿ���û����ĵ����ݽ�����װ
					for($j=0;$j<count($orderList);$j++)
					{
						switch($orderList[$j]['ITEM_NAME']) {
							case 'codeCollection':
								$content.="���뼯���棺\r\n".$logs['codeCollection'];
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
				$hello = "����";
				$hello= mb_convert_encoding( $hello,'utf-8','GB2312');
				$content = $userAccount."<br>".$hello;
				log::write("sendOrderMail-> ".$content);
				log::write("sendOrderMail-> ".mb_detect_encoding($content));
				
				//$content = "����";
				//$this->sendMail($content);//����
				//$this->sendMailPro($userAccount,"2011-10-27","GB2312");
				//$this->sendMailPro($userAccount,"2011-10-29",$content,$content,$content,$content);
				//$this->sendMailPro($userAccount,"2011-10-27",$content,$content,$content,$content);
				$this->sendMailPro($userAccount,"����");
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
		
		// ����PHPMailerʹ��SMTP����������Email
		$mail->IsSMTP();
		
		// �����ʼ����ַ����룬����ָ������Ϊ'UTF-8'
		//		$mail->CharSet='GB2312';
		
		// ����ռ��˵�ַ�����Զ��ʹ������Ӷ���ռ���
		$mail->AddAddress('ssor@qq.com');
		
		// �����ʼ�����
		//$message='��WordPress�Ĵ��뷢�͵�Email';
		//$mail->Body=$message;
		$body = file_get_contents('contents.html');
		//�����ʼ�����
		$body=str_replace("@name",$name,$body);
		$body=str_replace("@date",$date,$body);
		
		if (empty($class) ){
			$body=str_replace("@class","",$body);
			$body=str_replace("@cla","",$body);
			
		} else {
			$body=str_replace("@class","�������ࣺ",$body);
			$body=str_replace("@cla",$class,$body);
			
		}
		if (empty($interface) ){
			$body=str_replace("@interface","",$body);
			$body=str_replace("@interf","",$body);
			
		} else {
			$body=str_replace("@interface","�ӿڹ淶��",$body);
			$body=str_replace("@interf",$interface,$body);
			
		}
		if (empty($codeCollection) ){
			$body=str_replace("@codeCollection","",$body);
			$body=str_replace("@code","",$body);
			
		} else {
			$body=str_replace("@codeCollection","���뼯��",$body);
			$body=str_replace("@code",$codeCollection,$body);
			
		}
		if (empty($rule) ){
			$body=str_replace("@rule","",$body);
			$body=str_replace("@rl","",$body);
			
		} else {
			$body=str_replace("@rule","�������",$body);
			$body=str_replace("@rl",$rule,$body);
			
		}
		log::write($body);
		//$mail->MsgHTML(file_get_contents('contents.html'));
		$mail->MsgHTML($body);
		
		// �����ʼ�ͷ��From�ֶΡ�
		// �������׵�SMTP�����ⲿ�ֱ�������ʵ���˺���ͬ���������֤����
		$mail->From='zhangquanzhi110@163.com';
		
		// ���÷���������
		$mail->FromName='���ݱ�׼����ϵͳ';
		
		// �����ʼ�����
		$mail->Subject='���ݱ�׼����ϵͳ���ݱ�׼���֪ͨ';
		
		// ����SMTP������������ʹ�����׵�SMTP��������
		$mail->Host='smtp.163.com';
		
		// ����Ϊ����Ҫ��֤��
		$mail->SMTPAuth=true;
		
		// �����û��������룬�������ʼ����û��������롣
		$mail->Username='zhangquanzhi110';
		$mail->Password='0785150790';
		
		// �����ʼ���
		$mail->Send();
	}	
	public function sendMail($message)
	{
		//echo "sendMail";
		require_once('class.phpmailer.php');
		require_once('class.smtp.php');	
		
		$mail=new PHPMailer();
		
		// ����PHPMailerʹ��SMTP����������Email
		$mail->IsSMTP();
		
		// �����ʼ����ַ����룬����ָ������Ϊ'UTF-8'
		//		$mail->CharSet='GB2312';
		
		// ����ռ��˵�ַ�����Զ��ʹ������Ӷ���ռ���
		$mail->AddAddress('ssor@qq.com');
		
		// �����ʼ�����
		//$message='��WordPress�Ĵ��뷢�͵�Email';
		//$mail->Body=$message;
		//$mail->MsgHTML(file_get_contents('contents.html'));
		$mail->MsgHTML($message);
		
		// �����ʼ�ͷ��From�ֶΡ�
		// �������׵�SMTP�����ⲿ�ֱ�������ʵ���˺���ͬ���������֤����
		$mail->From='zhangquanzhi110@163.com';
		
		// ���÷���������
		$mail->FromName='Wang Jinbo';
		
		// �����ʼ�����
		$mail->Subject='Test Mail';
		
		// ����SMTP������������ʹ�����׵�SMTP��������
		$mail->Host='smtp.163.com';
		
		// ����Ϊ����Ҫ��֤��
		$mail->SMTPAuth=true;
		
		// �����û��������룬�������ʼ����û��������롣
		$mail->Username='zhangquanzhi110';
		$mail->Password='0785150790';
		
		// �����ʼ���
		$mail->Send();
	}	
	
}

// for this test, simply print that the authentication was successfull
?>
