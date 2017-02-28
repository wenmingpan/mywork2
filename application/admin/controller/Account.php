<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
/**
 * Description of Account
 *
 * @author Administrator
 */
class Account extends Base
{
    //put your code here
    public function index()
    {
        $users = Db::table('user')->order('sort desc')->select();
        $total = Db::table('user')->count();
        
        $this->assign('users',$users);
        $this->assign('total',$total);
        return view();
    }
    
    public function add()
    {
        // 角色
        $role = Db::table('role')->select();
        $this->assign('role',$role);
        
        return view();
    }
    
    public function doadd()
    {
        // 添加入库
        if (Request::instance()->isPost()) {
            $params = Request::instance()->param();

            $name = $params['name'];
            $email = $params['email'];
            $password = $params['password'];
            $status = $params['status'];
            $role_ids = empty($params['role_id']) ? array(): $params['role_id']; // 角色id
          
            $data = [
                'name' => $name,
                'email' => $email,
                'password' => md5(md5($password.'admin').'admin'),
                'status' => $status,
                'created_time' => time(),
                'updated_time' => time(),
                ];
            $res = Db::table('user')->insert($data);
            $userid = Db::table('user')->getLastInsID();
            if($res) {
                $this->_setUserRole($userid, $role_ids);
                dwz_ajax_do(200, '添加成功', 'account');
            } else {
                dwz_ajax_do(300, '添加失败', 'account');
            }
        }
    }
    
    public function delete()
    {
        $params = Request::instance()->param();
        $id = $params['id'];
        
        // 执行删除
        $result = Db::table('user')->where('id', $id)->delete();
        if($result) {
            dwz_ajax_do(200, '删除成功', 'account', '');
        } else {
            dwz_ajax_do(300, '删除失败', 'account', '');
        }
    }
    
    public function edit()
    {
        // 添加入库
        if (Request::instance()->isPost()) {
            $params = Request::instance()->param();

            $id = $params['id']; // 用户ID
            $name = $params['name'];
            $email = $params['email'];
            $status = $params['status'];
            $password = $params['password'];
            $role_ids = empty($params['role_id']) ? array(): $params['role_id']; // 角色id
            
            $data = [
                'name' => $name,
                'email' => $email,
                'status' => $status,
                'updated_time' => time(),
                ];
            if ($password) {
                $data['password'] = md5(md5($password.'admin').'admin');
            }
            
            $updated = Db::table('user')
                    ->where('id', $id)
                    ->update($data);
            // 设置用户角色关系
            $this->_setUserRole($id, $role_ids);

            if($updated) {     
                dwz_ajax_do(200, '修改成功', 'account');
            } else {
                dwz_ajax_do(300, '修改失败', 'account');
            }
        }
        
        // 进入列表
        if (Request::instance()->isGet()) {
            $params = Request::instance()->param();
            
            $id = $params['id'];
            $user = Db::table('user')->where('id', $id)->find();
            // 角色
            $role = Db::table('role')->select();
            // 通过userid 查询role_id
            $role_ids = Db::table('user_role')->where('uid', $user['id'])->select();
            $user_roleid = array_column($role_ids, 'role_id');
            
            $this->assign('user',$user);
            $this->assign('role',$role);
            $this->assign('role_id',$user_roleid);
            return view();
        }
    }
    
    /**
     * 批量删除
     */
    public function mutldelete()
    {
        $params = Request::instance()->param();
        $ids = $params['ids'];
        if ($ids) {
            // 删除用户
            $result = Db::table('user')->where('id', 'in', $ids)->delete();
            if ($result) {
                // 删除用户的角色
                $result = Db::table('user_role')->where('uid', 'in', $ids)->delete();
                dwz_ajax_do(200, '删除成功', 'account', '');
            }
            dwz_ajax_do(300, '删除失败', 'account');
        }
        dwz_ajax_do(300, '删除失败1', 'account');
    }


    /**
     * 设置用户与角色之间的关联关系
     * 
     * @param type $userid
     * @param type $role_ids
     */
    private function _setUserRole($userid, $role_ids=array())
    {
        // 通过userid 查询role_id
        $res = Db::table('user_role')->where('uid', $userid)->select();
        $user_roleid = array_column($res, 'role_id');
        $result = array_diff($user_roleid, $role_ids); // 求差集,

        // 修改用户权限信息
        if ($result) {  // 有值说明删除
            foreach ($result as $key => $value) {
                Db::table('user_role')->where(['uid' => $userid, 'role_id' => $value])->delete();
            }
        } else { // 无值进行添加
            foreach ($role_ids as $key => $value) {
                if (!in_array($value, $user_roleid)) {
                    $data = ['uid' => $userid, 'role_id' => $value, 'created_time'=> time()];
                    Db::table('user_role')->insert($data);
                }
            }
        }
    }
}
