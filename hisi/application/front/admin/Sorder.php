<?php
// 客服控制器
namespace app\index\controller;
use	think\Controller; 
use app\index\model\Linking;
use app\index\model\Waiting;
use think\Db;
class Sorder extends Controller
{
    /*
        * 载入正在等待的带求助用户
        *@param      integer        $sid                  [客服ID] 
        *@return     array          $uid              [5个或小于5个的待查询用户列表]
        */
        public function getwait()
        {
            $sid = input('sid');
            // 计算是否有5个用户
            // waiting列表给前5个加锁(待解决)

            // 取得前五个列表的uid，写入linking内，删除这5个在wait的记录

            // 放回uid数组（5个或者5个一下的用户）
            return;
        }
        /*
        * 回复用户用户
        *@param      integer        $uid                  [用户ID] 
        *@param      integer        $sid                  [客服ID] 
        *@param      text           $chat                 [回复信息] 
        *@return     string         $message              []
        */
        public function putchat()
        {
            $uid = input('uid');
            $chat = input('chat');
            $sid = input('sid');
            // $sid = 4;
            // $uid = 2;
            // $chat = '救救您呀！！！';
            $chatdb = 'help'.$uid;
            $creat = time();
            // 更改状态
                Db::execute("update $chatdb  set test=1 where   id = 1 ");
            // 插入信息     
           Db::execute("insert into $chatdb (uid,sid,chat,sign,create_time) values($uid,$sid,'".$chat."',0,$creat)");
           $data['message'] = 'success';
           echo json_encode($data);
           return;
        }
        /*分配用户对应的客服
         *@param    integer       $sid                  [客服ID] 
         *@return     array        $uid                 [待查询的用户数组] 
         */
        public function selectuser()
        {
            $sid = input('sid');
            $sid = 10;
            $num = Db::query("select COUNT(*) from waiting");
            $nu = $num[0]['COUNT(*)'];
            if($nu == 0){
                $uid = 0;
                echo json_encode($uid);
                return;
            }
           
                $n = $nu>4?5:$nu;
                $uid = Db::query("select uid from waiting limit $n for update");    //对读取到的行上排他锁，其他事务不准对其增、删、查、改
                $u = array();

                 foreach ($uid as $key ) {
                     $wait = Waiting::get($key['uid']);
                     $wait->delete();
                     $link = new Linking;
                     $link->uid = $key['uid'];
                     $link->sid = $sid;
                     $link->save();
                 }

           
        }
        /*完成查询，终止客服服务
         *@param      integer        $uid                 [用户ID] 
         *@return     string          $message            [放回操作信息]
         */
        public function ending()
        {
            $uid = input('uid');
            $uid =7 ;
            $link= Linking::get($uid);
            $link->delete();
        }
}
/**
 * 
 */


