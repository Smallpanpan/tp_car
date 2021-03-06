<?php

namespace app\index\controller;
use	think\Controller; 
use app\index\model\Carsystem;
use app\index\model\Carstore;
use app\index\model\Carslist;
use app\index\model\Carstatus;
use app\index\model\Carsready;
use app\index\model\User;  
use app\index\model\Asset;
use app\index\model\Order as ModelOrder;
use app\index\model\Secure;
use app\index\model\Linking;
use app\index\model\Waiting;
use think\Db;
class Order extends Controller
{
    public function test(){
        $orderId  = input('orderId');
    $carorder = ModelOrder::get($orderId);
    $data['progress']= $carorder->o_status;
    $data['all_price']=$carorder-> o_all_price;
    echo json_encode($data);
    }
//    选取正在进行的订单
//public function gethisorder(){
//    $orderId = input('orderId');
//    $carorder = Order::get($orderId);
//    $data['progress']= $carorder->o_status;
//    $data['all_price']=$carorder-> o_all_price;
//    echo json_encode($data);
//    $now = time();
//    echo $now;
//}
    // 展示我的订单信息
    public function myorder(){
        $arr = Secure::all();
        echo json_encode($arr);
    //  $user = User::get(1,'asset');
    //  $arr = $user->asset;
    // echo  $arr['0']['img'];
    }
    /*
    *写入租车订单表，生成订单，返回订单信息
    *@param      integer        $user_id             [用户id] 
    *@param      integer        $carstatus_id        [临时订单编号] 
    *@param      integer        $status              [支付状态] 
    *@param      integer        $insurance_id        [保险ID] 
    *@param      integer        $site_id             [网点ID] 
    *@param      integer        $asset_id            [优惠券ID] 
    *
    *@return
     *
     *
     *
     *
    */
    public function createorder()
    {

          // 输入测试
//        $user_id = 5;                //用户ID
//        $carstatus_id =43 ;      //临时订单编号
//        $status =1;                  //支付状态
//        $insurance_id =1;      //保险ID
//        $site_id =1003;                //网点ID
//        $asset_id =1;              //优惠券ID
//        asset_id: 1



        $user_id = input('userid');                //用户ID
        $carstatus_id = input('carstatus_id');      //临时订单编号
        $status = input('status');                  //支付状态
        $insurance_id = input('insurance_id');      //保险ID
        $site_id = input('site_id');                //网点ID
        $asset_id = input('asset_id');              //优惠券ID



        // 获得汽车的临时时间表：汽车ID   获取时间
        $carstatus = Carstatus::get($carstatus_id);
        $start = $carstatus->startTime;          //起租时间
        $end = $carstatus->endTime;              //起租时间
        $carlistid = $carstatus->car_id;     //获取汽车ID
        // 计算常规费用
        // 由汽车ID得出汽车基础服务费，单价
        $carlist = Carslist::get($carlistid);
        $carsystemid = $carlist->car_system_id;
        $carsystem = Carsystem::get($carsystemid);
        $bcharge =$carsystem->car_servuce;          //基础服务费
        $dcharge =$carsystem->car_daily_price ;      //日租价格
        $charge4 =$carsystem->car_4_price ;          //4小时内价格
        $charge8 =$carsystem->car_8_price ;          //8小时内
        $price1 = computeprice($dcharge,$charge4,$charge8,$start,$end);  
        $price =$price1[0];             //常规费用（就是不含服务费哦）
        $date = $price1[1];             //天数
        $date_4 = $price1[3];           //是否在0-4小时区间
        $date_8 = $price1[2];           //是否在4-8小时区间
        // 寻找优惠券
        $coupon = Asset::get($asset_id);
        $coupon_price = $coupon->a_price;       //优惠价格
        // 删除用户的优惠券

        // 保险
        $insurance_price = Secure::get($insurance_id)->price;   
        $all_price = $bcharge+$price-$coupon_price+$insurance_price; //总价格
        
        //写入数据库正式生成订单
        $myorder = new ModelOrder;
        $myorder->o_user_id = $user_id;
        $myorder->o_car_id  = $carlistid;
        $myorder->o_insurance_id  = $insurance_id;
        $myorder->o_service  = $bcharge;
        $myorder->o_site_id  = $site_id;
        $myorder->o_start  = $start;
        $myorder->o_end  = $end;
        $myorder->o_daily_price  = $dcharge;
        $myorder->o_4_price  = $charge4;
        $myorder->o_8_price  = $charge8;
        $myorder->o_date  = $date;
        $myorder->o_date_4  = $date_4;
        $myorder->o_date_8  = $date_8;
        $myorder->o_coupon_price  = $coupon_price;
        $myorder->o_all_price  = $all_price;
        $myorder->o_carstatusid = $carstatus_id;
        $myorder->	o_status = 1;

//返回真在进行订单参数

        if($myorder->save()){
           $data['order_id'] = $myorder->id;
           $data['start']  =time();
            echo json_encode($data);
            return;
        }else{
            $message = "下单失败！" ;
            echo json_encode($message);
            return;
        }



    }

 /*当用户还没有付款，取消临时订单，删除对应的临时订单状态表
        *@param      integer        $carstarusid             [临时订单ID] 
         @return     string         $message                  [返回操作信息]
        */
     public function canceltemporaryorder()
         {
            $carstarusid = input('carstarusid');
             // $carstarusid = 29;         //测试数据
            $cancelstatus = new Carstatus;  
            $data['message'] = $cancelstatus->cancel($carstarusid);             //封装删除临时表方法
            echo json_encode($data);
         }

       /*当用户付款了，取消订单，删除对应的临时订单状态表，修改订单，进行退款
       *修改规则为：24h外取消，收取10%手续费，24h内取消，收取20%手续费，超过取车时间取消或者不取车，收50%手续费（超过取车时间的）
        *@param      integer        $carstarusid             [临时订单ID] 
         @return     string         $message                  [返回操作信息]
        */
        public function cancelorder()
        {
            $orderid = input('orderid');
             // $orderid =4;         //测试数据
            $myorder = ModelOrder::get($orderid);
            $cancelstatus = new Carstatus; 
            $carstarusid = $myorder->o_carstatusid;
            $cancelstatus->cancel($carstarusid);
            $dis = time()-$myorder->o_start;
            if($dis>86400){
                $per=0.1;
            }else if($dis>0){
                $per=0.2;
            }else{
                $per=0.5;
            }
            $returnprice=$myorder->o_all_price*$per;
            $myorder->o_status=6;
            $myorder->o_date =0 ;
            $myorder->o_date_4  =0;
            $myorder->o_date_8  =0;
            $myorder->o_all_price =$returnprice;
            $myorder->save();
            $data['message'] = 1;
            $data['price'] = $returnprice;
            echo json_encode($data);
            return;
        }

        /*
        *求助投诉，网页加载时候（action）更新加载该用户的历史信息，未读设置特殊标志
        *@param      integer        $userid             [用户ID] 
        *@return     array          $message            [历史聊天记录]
        */
        public function searchhelp()
        {
            $uid = input('uid');
            
        }
        /*
        *写入请求内容，并申请查询
        *@param      integer        $uid                  [用户ID] 
        *@param      text           $chat                 [求助信息] 
        *@return     string         $message              [返回操作信息]
        */
        public function putchat()
        {
            $uid = input('uid');
            $chat = input('chat');
            $uid = 2;
            $chat = '救救我呀！！！';
            $chatdb = 'help'.$uid;
            $creat = time();
            // 寻找业务员
           // 遍历正在沟通表是否有自己的ID
           $isuser = Linking::getByUid($uid);
           if($isuser)
           {
           $sid = $isuser->sid;
           }else{
            
             $iswait = Waiting::getByUid($uid);
             if(!$iswait)
             { 
                // 将用户登记在待查表
                $wait = new Waiting;
                $wait->uid = $uid;
                $wait->save();
             }
            
            $sid = 0;
           }
            // 插入信息   
           // 1为用户未读，0为用户已读
        
           Db::execute("insert into $chatdb (uid,sid,chat,sign,create_time) values($uid,$sid,'".$chat."',1,$creat)");
           $data['message'] = 'success';
           echo json_encode($data);
           return;
        }
        /*
        * 放回历史或者客服回复的全部信息（更新操作）
        *@param      integer        $uid                  [用户ID] 
        *@return     array          $message              [历史记录及回复信息]
        */
        
        public function getchat()
        {
            $uid = input('uid');
             // $uid =2;
            $chatdb = 'help'.$uid;
            // 查找第一列数据，作为标志
            $data['sign'] = Db::query("select sign  from $chatdb where id=1");
            $data['message'] = Db::query("select *  from $chatdb");
             Db::execute("update $chatdb  set test=0 where   id = 1 ");
            echo json_encode($data);
        }
}


