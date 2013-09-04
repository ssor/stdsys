<?php
    $file_path = "./runtime.php";
    if (!file_exists($file_path)) {
        # code...
        $fp=fopen($file_path, "w+");
        chmod($file_path,0777);
        // fwrite($fp,"Hello World. Testing!");
        fclose($fp);
    }
    else
    {
        $fp=fopen($file_path, "w+");
        // chmod($file_path,0777);
        fwrite($fp,"Hello World. Testing!");
        fclose($fp);
        // echo file_put_contents($file_path,"file_put_contents ");
    }
// echo file_put_contents($file_path,"111");
// $fp = fopen($file_path,"w");
// var_dump($fp);
// echo "<br>";
// $w = fwrite($fp,"aaa");
// echo $w;
// echo "<br>";
// fclose($fp);
//echo $_SERVER['HTTP_ACCEPT_CHARSET'];
// echo "111";
echo "<br>";
return;
$agent = $_SERVER['HTTP_USER_AGENT'];
 if(strpos($agent,"MSIE"))
{
	echo "IE";
	}
	else
	{
		echo "not IE";
	}
//Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; InfoPath.3; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729) 
//Mozilla/5.0 (X11; Linux i686) AppleWebKit/534.30 (KHTML, like Gecko) Ubuntu/11.04 Chromium/12.0.742.112 Chrome/12.0.742.112 Safari/534.30
/*
作用：取得客户端信息
参数：
返回：指定的资料
使用：
    $code = new clientGetObj;
    1、浏览器：$str = $code->getBrowse();
    2、IP地址：$str = $code->getIP();
    4、操作系统：$str = $code->getOS();
*/
/*
class clientGetObj
{
    function getBrowse()
    {
        global $_SERVER;
        $Agent = $_SERVER['HTTP_USER_AGENT'];
        echo $Agent;
        $browser = '';
        $browserver = '';
        $Browser = array('Lynx', 'MOSAIC', 'AOL', 'Opera', 'JAVA', 'MacWeb', 'WebExplorer', 'OmniWeb');
        for($i = 0; $i <= 7; $i ++){
            if(strpos($Agent, $Browsers[$i])){
                $browser = $Browsers[$i];
                $browserver = '';
            }
        }
        if(ereg('Mozilla', $Agent) && !ereg('MSIE', $Agent)){
            $temp = explode('(', $Agent);
            $Part = $temp[0];
            $temp = explode('/', $Part);
            $browserver = $temp[1];
            $temp = explode(' ', $browserver);
            $browserver = $temp[0];
            $browserver = preg_replace('/([d.]+)/', '\1', $browserver);
            $browserver = $browserver;
            $browser = 'Netscape Navigator';
        }
        if(ereg('Mozilla', $Agent) && ereg('Opera', $Agent)) {
            $temp = explode('(', $Agent);
            $Part = $temp[1];
            $temp = explode(')', $Part);
            $browserver = $temp[1];
            $temp = explode(' ', $browserver);
            $browserver = $temp[2];
            $browserver = preg_replace('/([d.]+)/', '\1', $browserver);
            $browserver = $browserver;
            $browser = 'Opera';
        }
        if(ereg('Mozilla', $Agent) && ereg('MSIE', $Agent)){
            $temp = explode('(', $Agent);
            $Part = $temp[1];
            $temp = explode(';', $Part);
            $Part = $temp[1];
            $temp = explode(' ', $Part);
            $browserver = $temp[2];
            $browserver = preg_replace('/([d.]+)/','\1',$browserver);
            $browserver = $browserver;
            $browser = 'Internet Explorer';
        }
        if($browser != ''){
            $browseinfo = $browser.' '.$browserver;
        } else {
            $browseinfo = false;
        }
        return $browseinfo;
    }

    function getIP ()
    {
        global $_SERVER;
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    function getOS ()
    {
        global $_SERVER;
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $os = false;
        if (eregi('win', $agent) && strpos($agent, '95')){
            $os = 'Windows 95';
        }
        else if (eregi('win 9x', $agent) && strpos($agent, '4.90')){
            $os = 'Windows ME';
        }
        else if (eregi('win', $agent) && ereg('98', $agent)){
            $os = 'Windows 98';
        }
        else if (eregi('win', $agent) && eregi('nt 5.1', $agent)){
            $os = 'Windows XP';
        }
        else if (eregi('win', $agent) && eregi('nt 5', $agent)){
            $os = 'Windows 2000';
        }
        else if (eregi('win', $agent) && eregi('nt', $agent)){
            $os = 'Windows NT';
        }
        else if (eregi('win', $agent) && ereg('32', $agent)){
            $os = 'Windows 32';
        }
        else if (eregi('linux', $agent)){
            $os = 'Linux';
        }
        else if (eregi('unix', $agent)){
            $os = 'Unix';
        }
        else if (eregi('sun', $agent) && eregi('os', $agent)){
            $os = 'SunOS';
        }
        else if (eregi('ibm', $agent) && eregi('os', $agent)){
            $os = 'IBM OS/2';
        }
        else if (eregi('Mac', $agent) && eregi('PC', $agent)){
            $os = 'Macintosh';
        }
        else if (eregi('PowerPC', $agent)){
            $os = 'PowerPC';
        }
        else if (eregi('AIX', $agent)){
            $os = 'AIX';
        }
        else if (eregi('HPUX', $agent)){
            $os = 'HPUX';
        }
        else if (eregi('NetBSD', $agent)){
            $os = 'NetBSD';
        }
        else if (eregi('BSD', $agent)){
            $os = 'BSD';
        }
        else if (ereg('OSF1', $agent)){
            $os = 'OSF1';
        }
        else if (ereg('IRIX', $agent)){
            $os = 'IRIX';
        }
        else if (eregi('FreeBSD', $agent)){
            $os = 'FreeBSD';
        }
        else if (eregi('teleport', $agent)){
            $os = 'teleport';
        }
        else if (eregi('flashget', $agent)){
            $os = 'flashget';
        }
        else if (eregi('webzip', $agent)){
            $os = 'webzip';
        }
        else if (eregi('offline', $agent)){
            $os = 'offline';
        }
        else {
             $os = 'Unknown';
        }
        return $os;
    }
}
//$tmp = new clientGetObj();

echo "browser ";
//echo $tmp->getBrowser();

//echo $tmp->getIP();
//echo $tmp->getOS();
*/
//fopen("http://localhost/index.php/SynUser/startSyn","r");
//include_once(dirname(__FILE__).'/debugTrace.php');
 
/*
//$_SESSION['phpCAS']['user'] = "cas";
	//echo("<br>");
//echo("start ->".$_SESSION['phpCAS']['user']);
//echo (session_start());
		//session_unset();
	//echo("<br>");
//echo("after unset ->".$_SESSION['phpCAS']['user']);		
		//session_destroy();
	//echo("<br>");
echo("after destroy ->".$_SESSION['phpCAS']['user']);	
	echo("<br>");
*/
	
	
/*
$msg = "系统将在秒之后自动跳转到！";
$charset = $_SERVER['HTTP_ACCEPT_CHARSET'];
$msgConverted = $msg;
var_dump($msg);
if(mb_substr(mb_strtoupper($charset),0,2) == "GB")
{
	echo("***********************");
	echo("<br>");
	$msgConverted = mb_convert_encoding($msg,"gb2312","UTF-8");
	var_dump($msgConverted);
	echo("***********************");
	$msgUnicode = mb_convert_encoding($msg,"UTF-16LE","UTF-8");
	echo("<br>");
	var_dump($msgUnicode);
	echo("***********************");
}
var_dump($_SERVER);
*/
phpinfo();
?>
