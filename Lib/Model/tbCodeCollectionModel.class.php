<?php
class tbCodeCollectionModel extends Model {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('collectionID','require','标题必须！',1),
		array('id','require','标题必须！',1),
		array('name','require','标题必须！',1),
		);
	// 自动填充设置
	protected $_auto	 =	 array(
		);

}
?>