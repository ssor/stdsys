<?php
class WelcomeAction extends Action{
	// 首页
	public function welcome(){
		$this->display();
	}
	// 检查标题是否可用
	public function checkLogin()
      {
            $this->redirect('Admin-Index/main', null, 1, '添加项目出错！');
      }

}
?>