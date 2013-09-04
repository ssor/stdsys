<?php
return array(
	//'配置项'=>'配置值'
    'URL_MODEL'=>3, // 如果你的环境不支持PATHINFO 请设置为3
	//'DB_TYPE'=>'mysql',
	'DB_TYPE'=>'pdo',
	// 连接本地
	//'DB_HOST'=>'localhost',
	'DB_DSN' => 'sqlite:'.dirname(__FILE__).'./test.db3', //相对于config目录的路径
	// 远程连接
	//'DB_HOST'=>'211.68.70.13',
	// 连接本地
	//'DB_NAME'=>'localhost:1521/xe',
	//'DB_NAME'=>'test',
	'DB_NAME'=>'test.db3',
	// 远程连接
	//'DB_NAME'=>'211.68.70.13/szhxy',
	//'DB_USER'=>'ssor',
	//'DB_USER'=>'BZGF',
	'DB_USER'=>'root',
	'DB_PWD'=>'078515',
	//'DB_PWD'=>'BZGF',
	//'DB_PORT'=>'1521',
	'DB_PREFIX'=>'think_',
    'APP_DEBUG' => 0,
    'HTML_FILE_SUFFIX' => '.html',
    'APP_GROUP_LIST' => 'Admin,Home',
    'DEFAULT_GROUP' => 'Home',
    'DEFAULT_MODULE' => 'Welcome',
    'DEFAULT_ACTION' => 'welcome',
	
	'DB_CHARSET' => 'utf8',
);
?>
