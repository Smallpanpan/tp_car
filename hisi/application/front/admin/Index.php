<?php
// 这里是写控制方法的地方，前台可以访问主机地址/index/方法名  就可以访问不同的方法返回不同的参数了
namespace app\index\controller;
use	think\Controller; 
use	think\Db;

class Index extends Controller
{
    public function index()
    {
    	// $name = Db::query('select u_id from user ');
    	 // echo $name[0]['u_id'];
        // 查询一行数据
        // $list   =   Db::table('user')             
        // ->where('u_name','pan')            
        // ->select();
        // 查询多列数据，会以每个列对应的行用冒号：隔开作为一个数组
        // test1、查询一列
        $list = Db::table('user')
        ->where('')
        ->column('u_name','u_num','u_pwd');             //在这里u_num只是作为他的下标
        $row['name']=$list;
            // return json($list);      只是放回数据，前端js要接收到输入数据（后台用echo输出来）
        // echo $list[0].'-----';
        echo json_encode($row);
    }
    public function test()
    {
    	echo "this is indexs test";
    }

}
/**
 * 
 */


