<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
/**
 * Description of Role
 *
 * @author Administrator
 */
class Role extends Base
{
    //put your code here
    public function index()
    {
        $roles = Db::table('role')->select();
        $total = Db::table('role')->count();
        
        $this->assign('roles',$roles);
        $this->assign('total',$total);
        
        return view();
    }
    
    public function add()
    {
        return view();
    }
    
    public function doadd()
    {
        // 添加入库
        if (Request::instance()->isPost()) {
            $params = Request::instance()->param();
            $name = $params['name'];
            $status = $params['status'];
            $result = Db::table('role')->where('name',$name)->find();
            if ($result) {
                dwz_ajax_do(300, '已存在', 'role');
            }
            $data = ['name' => $name,
                    'status' => $status,
                    'created_time' => time(),
                    'updated_time' => time(),
                ];
            $res = Db::table('role')->insert($data);
            if($res) {
                dwz_ajax_do(200, '添加成功', 'role');
            } else {
                dwz_ajax_do(300, '添加失败', 'role');
            }
        }
    }
    
    public function delete()
    {
        $params = Request::instance()->param();
        $id = $params['id'];
        
        // 执行删除
        $result = Db::table('role')->where('id', $id)->delete();
        if($result) {
            dwz_ajax_do(200, '删除成功', 'role', '');
        } else {
            dwz_ajax_do(300, '删除失败', 'role', '');
        }
    }
    
    public function edit()
    {
        // 添加入库
        if (Request::instance()->isPost()) {
            $params = Request::instance()->param();

            $id = $params['id'];
            $name = $params['name'];
            $status = $params['status'];
            
            $data = [
                'name' => $name,
                'status' => $status,
                'updated_time' => time(),
                ];
            $updated = Db::table('role')
                    ->where('id', $id)
                    ->update($data);

            if($updated) {     
                dwz_ajax_do(200, '修改成功', 'role');
            } else {
                dwz_ajax_do(300, '修改失败', 'role');
            }
        }
        
        $params = Request::instance()->param();
        $id = $params['id'];
        $role = Db::table('role')->where('id', $id)->find();
        
        $this->assign('role',$role);
        return view();
    }
    
    /**
     * 角色分配权限
     */
    public function addaccess()
    {
        if (Request::instance()->isGet()) {
            $params = Request::instance()->param();
//            p($params);
            $this->assign('role_id',$params['id']); // 角色名称
            $role = Db::table('role')->field('name')->where('id', $params['id'])->find();
            $this->assign('role_name',$role['name']);
            
            // 所有权限
            $list = Db::table('access')->field(['id','title','pid','status'])->select();
            $list = node_merge($list);
            // 用户权限
            $access_user = Db::table('role_access')->where('role_id', $params['id'])->select();
            $user_access_id = array_column($access_user, 'access_id');
            $this->assign('user_access_id',$user_access_id); // 用户权限ID
//            p($user_access_id);exit;
            $this->assign('list',$list);
            return $this->fetch('addaccess');
        }
        
        if (Request::instance()->isPost()) {
            $params = Request::instance()->param();
//            p($params);exit;
            $role_id = $params['role_id'];
            $access_id = empty($params['access_id']) ? array(): $params['access_id']; // 角色id
            
            // 设置用户角色关系
            $res = $this->_setRoleAccess($role_id, $access_id);
            if($res) {
                dwz_ajax_do(200, '修改成功', 'role');
            } else{
                dwz_ajax_do(300, '修改失败', 'role');
            }
        }
    }
    
    
    /**
     * 角色与权限的关系
     * @param type $role_id
     * @param type $access_id
     */
    private function _setRoleAccess($role_id, $access_id=array())
    {
        // 通过userid 查询role_id
        $res = Db::table('role_access')->where('role_id', $role_id)->select();
        $role_access_id = array_column($res, 'access_id');
        $result = array_diff($role_access_id, $access_id); // 求差集,
//        p($access_id);
//        p($role_access_id);
//        p($result);exit;
        
        // 修改用户权限信息
        if ($result) {  // 有值说明删除
            foreach ($result as $key => $value) {
                Db::table('role_access')->where(['role_id' => $role_id, 'access_id' => $value])->delete();
            }
        } else { // 无值进行添加
            foreach ($access_id as $key => $value) {
                if (!in_array($value, $role_access_id)) {
                    $data = ['role_id' => $role_id, 'access_id' => $value, 'created_time'=> time()];
                    Db::table('role_access')->insert($data);
                }
            }
        }
        return true;
    }
}
