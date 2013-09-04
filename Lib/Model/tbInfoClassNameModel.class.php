<?php
class tbInfoClassNameModel extends Model {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('iClassID','require','所属数据类必须！',1),
		array('vClassName','require','名称必须！',1),
		array('vClasslevel','require','所属数据子集必须！',1),
		array('iClassID','','该ID已经存在',0,'unique','add'),
		);
	// 自动填充设置
	protected $_auto	 =	 array(
		);

}
?>