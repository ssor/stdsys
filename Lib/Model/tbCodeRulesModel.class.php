<?php
class tbCodeRulesModel extends Model {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('vID','require','编号必须！',1),
		array('vName','require','字段名称必须！',1),
		array('vNamechn','require','中文名称必须！',1),
		array('vType','require','类型必须！',1),
		array('nLength','require','长度必须！',1),
		array('vID','','编号已经存在',0,'unique','add'),
		);
	// 自动填充设置
	protected $_auto	 =	 array(

		);

}
?>