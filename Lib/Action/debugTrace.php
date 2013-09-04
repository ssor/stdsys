<?php

/**
 * The default directory for the debug file under Unix.
 */
define('DEFAULT_DEBUG_DIR','./log.txt');
function traceStart()
{
	$dbgInfo = debug_backtrace();
	if(count($dbgInfo)>=2)
	{
		$str = "";
		$indentSpace = "";
		$indentCount = count($dbgInfo);
		for($i = 1;$i <= $indentCount;$i++)
		{
			$indentSpace .="---";
		}
		//echo "<br>";
		$str.= "\n";
		//echo $indentSpace.$dbgInfo[1]['file']." :".$dbgInfo[1]['line'];
		$str.= $indentSpace.$dbgInfo[1]['file']." :".$dbgInfo[1]['line'];
		//echo "<br>";
		$str.= "\n";
		//echo $indentSpace.">".$dbgInfo[1]['function'];
		$str.= $indentSpace.">".$dbgInfo[1]['function'];
		//echo "<br>";
		$str.= "\n";
		
		error_log($str,3,DEFAULT_DEBUG_DIR);
	}
	//var_dump($dbgInfo);
	//var_dump(debug_backtrace());
}
function traceEnd()
{
	$dbgInfo = debug_backtrace();
	if(count($dbgInfo)>=2)
	{
		$str = "";
		$indentSpace = "";
		$indentCount = count($dbgInfo);
		for($i = 1;$i <= $indentCount;$i++)
		{
			$indentSpace .="---";
		}
		//echo "<br>";
		$str.= "\n";
		//echo $indentSpace.$dbgInfo[1]['file']." :".$dbgInfo[1]['line'];
		$str.= $indentSpace.$dbgInfo[1]['file']." :".$dbgInfo[1]['line'];
		//echo "<br>";
		$str.= "\n";
		//echo "<".$indentSpace.$dbgInfo[1]['function'];
		$str.= "<".$indentSpace.$dbgInfo[1]['function'];
		//echo "<br>";
		$str.= "\n";
		
		error_log($str,3,DEFAULT_DEBUG_DIR);
	}
/*
	echo "<br>";
	echo "file-> ".$dbgInfo[0]['file']." line: ".$dbgInfo[0]['line'];
	echo "<br>";
	echo "function <- ".$dbgInfo[0]['function'];
	echo "<br>";
*/
	//var_dump(debug_backtrace());
}

?>
