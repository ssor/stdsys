<?php

//class IndexAction extends Action{
class IndexAction extends LoginCommonAction
{
    public function top()
    {
        $this->display();
    }
    public function main()
    {
        if ($this->checkRole())
        {
            $this->display();
        }
    }

    public function menu()
    {
        if ($this->checkRole())
        {
            $Class = M("tbCodeCollectionName");
            $levelList = $Class->query("select distinct collectionlevel as cl from think_tbCodeCollectionName");
            $this->trace('menu levelList ', dump($levelList, false));
            $this->assign('levelList', $levelList);
            $list = $Class->query("select collectionID as id,collectionName as name,collectionlevel as cl from think_tbCodeCollectionName");
            $this->trace('menu List ', dump($list, false));
            $this->assign('list', $list);
            $this->display();
        }
    }
    private function checkRole()
    {
        //        $role = Cookie::get('role');
        //        if (empty($role) || $role == 'demo')
        //        {
        //            $this->redirect('Home-Welcome/welcome', null, 1, '正在跳转到登录页面...');
        //            return false;
        //        } else

        {
            return true;
        }
    }
    //查询属于某表的所有字段
    public function specialIndex()
    {
        if ($this->checkRole())
        {
            $cnt = 0;
            $tn = $_GET['tbln'];
            if (!empty($tn))
            {
                $this->trace('Admin specialIndex ' + $cnt, dump($tn, false));
                $cnt++;
            } else
            {
                $this->trace('Admin specialIndex', 'empty input');
            }
            $Class = M("Class");
            //        $condition['table_name'] = $tn;
            //        $list = $Class->where($condition)->select();
            $list = $Class->query("select id, name, table_name as tn,comment as cmt  from __TABLE__ where table_name = '$tn'");
            //$this->trace('Admin specialIndex ' + $cnt, dump($list, false));
            if (count($list) > 0)
            {
                $this->assign('list', $list);
                $this->assign('tbn', $tn);
                $this->display();
            } else
            {
                $this->redirect('Admin-Index/menu', null, 1, '不存在该表！');
            }
        }

    }
    public function specialIndexPara($tbln)
    {
        $Class = M("Class");
        $condition['table_name'] = $tbln;
        $list = $Class->where($condition)->select();
        $this->assign('list', $list);
        $this->assign('tbn', $tbln);
        $this->display();
    }
    //增加新项时
    public function add()
    {
        if ($this->checkRole())
        {
            $tn = $_GET['tbln'];
            $this->assign('tableName', $tn);
            $this->display();
        }
    }
    // 处理表单数据
    public function insert()
    {
        if ($this->checkRole())
        {
            $name = $_POST['name'];
            $tbname = $_POST['tbname'];
            $comment = $_POST['comment'];
            $this->trace('add name', $name);
            $this->trace('add table name', $tbname);
            if (empty($name) || empty($tbname))
            {
                $this->redirect('Admin-Index/add', null, 1, '数据填写不完全！');
            } else
            {
                $Class = D("Class");
                $condition = "name='$name' and table_name='$tbname'";
                $list = $Class->where($condition)->select();
                $this->trace('insert list', dump($list, false));
                if (count($list) > 0)
                {
                    $this->redirect('Admin-Index/add', null, 1, '该项目已经添加！');
                    return;
                } else
                {
                    $newClass = M("Class");
                    $sql = "insert into __TABLE__(name,table_name,comment) values('$name','$tbname','$comment') ";
                    if ($newClass->execute($sql))
                    {
                        $url = "Admin-Index/specialIndex/tbln/$tbname";
                        $this->redirect($url, null, 1, '数据添加成功！');
                        return;
                    } else
                    {
                        $this->redirect('Admin-Index/add', null, 1, '数据添加异常！');
                    }
                }

            }

        }
    }

    // 编辑数据
    public function edit()
    {
        if ($this->checkRole())
        {
            $tn = $_GET['tbln'];
            $id = $_GET['id'];
            log::write("edit tn=$tn id = $id");
            if (empty($id) || empty($tn))
            {
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $Class = D("Class");
                $sql = "select id, name, table_name as tn,comment as cmt from __TABLE__ where id = $id";
                $list = $Class->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/specialIndex/tbln/$tn";
                    $this->redirect($url, null, 1, '编辑项不存在！');
                    return;
                }
                $this->assign('vo', $list[0]);
                $this->display();

            }
        }
        //        if (!empty($_GET['id']))
        //        {
        //            $Form = M("Form");
        //            $vo = $Form->getById($_GET['id']);
        //            if ($vo)
        //            {
        //                $this->assign('vo', $vo);
        //                $this->display();
        //            } else
        //            {
        //                exit('编辑项不存在！');
        //            }
        //        } else
        //        {
        //            exit('编辑项不存在！');
        //        }
    }

    // 删除数据
    public function delete()
    {
        if ($this->checkRole())
        {
            $id = $_GET['id'];
            $tn = $_GET['tbln'];
            log::write("delete tn=$tn id = $id");
            if (empty($id) || empty($tn))
            {
                $this->redirect('Admin-Index/menu', null, 1, '删除项目出错！');
            } else
            {
                $Class = D("Class");
                $sql = "select id, name, table_name as tn,comment as cmt from __TABLE__ where id = $id";
                $list = $Class->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/specialIndex/tbln/$tn";
                    $this->redirect($url, null, 1, '该项不存在！');
                    return;
                }
                $sqlDelete = "delete from __TABLE__ where id = $id";
                $Class->execute($sqlDelete);
                $url = "Admin-Index/specialIndex/tbln/$tn";
                $this->redirect($url, null, 1, '删除成功！');
            }
        }

        //        if (!empty($_POST['id']))
        //        {
        //            $Form = M("Form");
        //            $result = $Form->delete($_POST['id']);
        //            if (false !== $result)
        //            {
        //                $this->ajaxReturn($_POST['id'], '删除成功！', 1);
        //            } else
        //            {
        //                $this->error('删除出错！');
        //            }
        //        } else
        //        {
        //            $this->error('删除项不存在！');
        //        }
    }

    // 更新数据
    public function update()
    {
        if ($this->checkRole())
        {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $tn = $_POST['tbname'];
            $comment = $_POST['comment'];
            log::write("update tn=$tn id = $id name = $name");
            if (empty($id) || empty($tn) || empty($name))
            {
                $this->redirect('Admin-Index/menu', null, 1, '编辑项目出错！');
            } else
            {
                $Class = D("Class");
                $sql = "select name, table_name as tn,comment as cmt from __TABLE__ where id = $id";
                $list = $Class->query($sql);
                if (count($list) <= 0)
                {
                    $url = "Admin-Index/specialIndex/tbln/$tn";
                    $this->redirect($url, null, 1, '编辑项不存在！');
                    return;
                }
                $sqlUpdate = "update __TABLE__ set name='$name',table_name = '$tn',comment = '$comment' where id = $id";
                if ($Class->execute($sqlUpdate))
                {
                    $url = "Admin-Index/specialIndex/tbln/$tn";
                    $this->redirect($url, null, 1, '已保存更改！');
                } else
                {
                    $url = "Admin-Index/specialIndex/tbln/$tn";
                    $this->redirect($url, null, 1, '保存失败！');
                }
            }
        }
        //        $Form = D("Form");
        //        if ($vo = $Form->create())
        //        {
        //            $list = $Form->save();
        //            if ($list !== false)
        //            {
        //                $this->success('数据更新成功！');
        //            } else
        //            {
        //                $this->error("没有更新任何数据!");
        //            }
        //        } else
        //        {
        //            $this->error($Form->getError());
        //        }
    }


}

?>