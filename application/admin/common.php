<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function p($arr)
{
    echo "<pre>";
    print_r($arr);
}

/**
 *  权限节点
 * @param type $node
 * @param type $pid
 * @return type
 */
function node_merge($node, $pid=0)
{
  
    $arr = array();
    foreach($node as $v) {
        if ($pid == $v['pid']) {
            $v['child'] = node_merge($node, $v['id']);
            $arr[] = $v;
        }
    }
    
    return $arr;
}

/**
 * dwz ajax 数据返回
 * @param type $statusCode ok:200, error:300, timeout:301
 * @param type $message
 * @param type $navTabId
 * @param type $callbackType
 * @param type $forwardUrl
 */
function dwz_ajax_do($statusCode, $message, $navTabId='', $callbackType='closeCurrent', $forwardUrl='')
{
    $data = array(
        'statusCode' => $statusCode,
        'message' => $message,
        'navTabId' => $navTabId,
        'callbackType' => $callbackType,
        'forwardUrl' => $forwardUrl,
        
    );
    echo json_encode($data);exit;
}
