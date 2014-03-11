<?php
/**
 * Created by Green Studio.
 * File: AdminBaseController.class.php
 * User: TianShuo
 * Date: 14-1-25
 * Time: 上午10:39
 */

namespace Admin\Controller;

use Common\Controller\BaseController;
use Org\Util\Rbac;

class AdminBaseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->_initialize();

        $this->_currentPostion();
        $this->_currentUser();

        $this->customConfig();

    }

    protected function _initialize()
    {
        if (!RBAC::AccessDecision('Admin')) // AccessDecision中间使用分组名
        {
            // 登录检查
            RBAC::checkLogin();
            // 提示错误信息 无权限
            $this->error(L('_VALID_ACCESS_'));

            // TODO 如何防止循环无权限
        }

    }


    private function _currentPostion()
    {

        //  echo CONTROLLER_NAME;
        //  echo ACTION_NAME;

        $cache = C('admin_big_menu');
        foreach ($cache as $big_url => $big_name) {
            if (strtolower($big_url) == strtolower(CONTROLLER_NAME)) {
                $module = $big_name;
                $module_url = U("Admin/" . "$big_url" . '/index');
            } else {
            }
        }

        $cache = C('admin_sub_menu');
        foreach ($cache as $big_url => $big_name) {
            if (strtolower($big_url) == strtolower(CONTROLLER_NAME)) {
                foreach ($big_name as $sub_url => $sub_name) {
                    $sub_true_url = explode('/', $sub_url);
                    if (!strcasecmp($sub_true_url [1], strtolower(ACTION_NAME))) {
                        $action = $sub_name;
                        $action_url = U("Admin/" . "$sub_url");
                    }
                }
            }
        }

        $this->assign('module', $module);
        $this->assign('action', $action);
        $this->assign('module_url', $module_url);
        $this->assign('action_url', $action_url);

    }

    protected function _currentUser()
    {
        $user_id = ( int )$_SESSION [C('USER_AUTH_KEY')];
        $user = D('User', 'Logic')->detail($user_id);
        $this->assign('user', $user);
    }


    protected function saveKv()
    {
        S('kv_array', null); //清空缓存

        $data = array();
        foreach ($_POST as $key => $value) {
            unset ($data ['kv_id']); // 删除上次保存配置时产生的option_id，否则无法插入下一条数据
            set_kv($key, $value);
        }


    }


    public function saveConfig()
    {
        S('customConfig', null); //清空缓存

        $options = D('Options');
        $data = array();
        foreach ($_POST as $name => $value) {
            unset ($data ['option_id']); // 删除上次保存配置时产生的option_id，否则无法插入下一条数据
            $data ['option_name'] = $name;
            $data ['option_value'] = $value;

            $find = $options->where(array(
                                         'option_name' => $name
                                    ))->select();
            if (!$find) {
                $options->data($data)->add();
            } else {
                $data ['option_id'] = $find [0] ['option_id'];
                $options->save($data);
            }
        }
    }

}