<?php

class IndexAction extends Action{

	// 查询数据
	public function index(){
    dump('index action');
		$Form	= M("Form");
		$list	=	$Form->limit(5)->order('id desc')->findAll();
		$this->assign('list',$list);
		$this->display();
	}
	// 写入数据
	//~ public function insert() {
        //~ dump('insert action');
		//~ if($vo = $Form->create()) {
			//~ $list=$Form->add();
			//~ if($list!==false){
				//~ $this->success('数据保存成功！');
			//~ }else{
				//~ $this->error('数据写入错误！');
			//~ }
		//~ }else{
			//~ $this->error($Form->getError());
		//~ }
	//~ }
	// 处理表单数据
	public function insert() {
    dump('insert action');
		$Form	=	D("Form");
		if($Form->create()) {
			if(false !==$Form->add()) {
    			$this->success('数据添加成功！');
            }else{
                $this->error('数据写入错误');
            }
		}else{
			header("Content-Type:text/html; charset=utf-8");
			exit($Form->getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
		}
	}
	// 更新数据
	public function update() {
		$Form	=	D("Form");
		if($vo = $Form->create()) {
			$list=$Form->save();
			if($list!==false){
				$this->success('数据更新成功！');
			}else{
				$this->error("没有更新任何数据!");
			}
		}else{
			$this->error($Form->getError());
		}
	}
	// 删除数据
	public function delete() {
		if(!empty($_POST['id'])) {
			$Form	=	M("Form");
			$result	=	$Form->delete($_POST['id']);
			if(false !== $result) {
				$this->ajaxReturn($_POST['id'],'删除成功！',1);
			}else{
				$this->error('删除出错！');
			}
		}else{
			$this->error('删除项不存在！');
		}
	}

	// 编辑数据
	public function edit() {
		if(!empty($_GET['id'])) {
			$Form	=	M("Form");
			$vo	=	$Form->getById($_GET['id']);
			if($vo) {
				$this->assign('vo',$vo);
				$this->display();
			}else{
				exit('编辑项不存在！');
			}
		}else{
			exit('编辑项不存在！');
		}
	}

}
?>