<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
/**
 * Description of Access
 *
 * @author Administrator
 */
//class Access extends Controller {
class Access extends Base {
    //put your code here
    public function index()
    {
        $list = Db::table('access')->field(['id','title','pid','status'])->select();
        $list = node_merge($list);
//        p($list);exit;
        $this->assign('list',$list);
        
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
//            p($params);exit;
            $title = $params['title'];
            $status = $params['status'];
            $pid = isset($params['pid']) ? $params['pid']:0;
            $urls = isset($params['urls']) ? $params['urls']:'';
            if($urls){
                $urls = explode("\r\n", $urls);
                $urls =  json_encode($urls);
            }
            
            
            $res = Db::table('access')->where('title',$title)->find();
            if ($res) {
               dwz_ajax_do(300, '已存在', 'access');
            }
            $data = ['title' => $title,
                    'status' => $status,
                    'urls' => $urls,
                    'pid' => $pid,
                    'created_time' => time(),
                    'updated_time' => time(),
                ];
            $res = Db::table('access')->insert($data);
            
            if($res) {
                dwz_ajax_do(200, '添加成功', 'access');
            } else {
                dwz_ajax_do(300, '添加失败', 'access');
            }
        }
    }
    
    /**
     * 添加操作
     */
    public function addaction()
    {
        $params = Request::instance()->param();
        $pid = $params['pid'];
        $this->assign('pid',$pid);
        return view();
    }
    
    /**
     * 删除
     */
    public function delete()
    {
        $params = Request::instance()->param();
        $id = $params['id'];
        
        // 查询是否还存在子id
        $res = Db::table('access')->where('pid',$id)->select();
        if($res) {
            dwz_ajax_do(300, '存在子类，删除失败', 'access');
        }
        
        // 执行删除
        $result = Db::table('access')->where('id', $id)->delete();
        if($result) {
            dwz_ajax_do(200, '删除成功', 'access', '');
        } else {
            dwz_ajax_do(300, '删除失败', 'access', '');
        }
    }
    
    public function edit()
    {
        
        // 进行修改
        if (Request::instance()->isPost()) {
            $params = Request::instance()->param();
            $id = $params['id'];
            $title = $params['title'];
            $status = $params['status'];
            $pid = isset($params['pid']) ? $params['pid']:0;
            $urls = isset($params['urls']) ? $params['urls']:'';
   
            if($urls){
                $urls = explode("\r\n", $urls);
                $urls =  json_encode($urls);
            }
            
            $data = ['title' => $title,
                    'status' => $status,
                    'urls' => $urls,
                    'pid' => $pid,
                    'updated_time' => time(),
                ];
            $updated = Db::table('access')
                    ->where('id', $id)
                    ->update($data);
            
            if($updated) {
                dwz_ajax_do(200, '修改成功', 'access');
            } else {
                dwz_ajax_do(300, '修改失败', 'access');
            }
        }
        
        $params = Request::instance()->param();
        $id = $params['id'];
        $access = $this->_getAccess($id);
        
        $this->assign('access',$access);
        return view();
    }
    
    public function editaction()
    {
        
        $params = Request::instance()->param();
        $id = $params['id'];
        $access = $this->_getAccess($id);

        $this->assign('access',$access);
        return view();
    }
    
    private function _getAccess($id)
    {
        return Db::table('access')->where('id',$id)->find();
        
    }
}
