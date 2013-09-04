<?php

class LoginCommonAction extends Action{

    public function _initialize()
    {
        
        //~ if(Cookie::is_set('role'))
        //~ {
            //~ if(Cookie::get('role') == 'admin' ||
                //~ Cookie::get('role') == 'demo')
                //~ {}
                //~ else
                //~ {
                    //~ //$login = A('Home.Welcome');
                    //~ //$login->login();
                    //~ $this->redirect('Home-Welcome/welcome',null,1,'正在跳转到登录页面...');
                //~ }
        //~ } 
        //~ else
        //~ {
            //~ $this->redirect('Welcome/welcome',null,1,'正在跳转到登录页面...');
        //~ }
    }

}
?>