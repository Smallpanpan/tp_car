<?php 
namespace app\index\controller;
use	think\Controller; 
use	think\Db;
use	think\Session;
use app\index\model\User as  UserModel;
use app\index\model\Assetaccess;
use app\index\model\Asset;
class User extends Controller
{
    public function posttest()
    {
    	$row['name']=input('name');			//获取前台post过的的json

    	echo json_encode($row); 
    }
    public function test()
    {
    	
      session_start() ;
    	Session::set('name','啊啊啊');
    	// 获取session
    	 $mess['message'] = Session::has('name');
    	// 判断是否存在该session
    	$mess['err'] = Session::get('name');
    	


    	echo json_encode($mess);
      echo "this is test";

    }
    /*
    返回接口：status：
    接收接口{num: 账号, userPwd: "密码", checkCcookie: 是否自动登录}
    */
   	public function login()
   	{

       $user = model('User');
   		 $num=input('num');
       $pass=input('userPwd');
       $checkCcookie=input('checkCcookie');
     //   // 测试数据
     //   $num=13642599081;
     //   $pass1=123456;
     //   $pass = md5($pass1);
     // $checkCcookie=true;
       
   		$userInfo = $user->where('u_num',$num)->find();
   		if(!$userInfo){
   			$message['status'] = 0;
   			$message['err']	= 'fail';
        $message['checklogin']='-1';
   			echo json_encode($message);
        return;
   		}
      // md5($pass) 
      $time = intval(time()/10);
      $pw =$userInfo['u_pwd'].$time;
       $pwd = md5($pw);
   		if($pass==$pwd){      //登录成功
      // 写入cookie
          session_start();
          $_SESSION['name']=$userInfo['u_name'];
          $_SESSION['status']="1";
          $_SESSION['checklogin']='0';
          //根据拿到的是否记住密码，写cookie，进行自动登录
          if($checkCcookie){
            //在这里写cookie
            setcookie('num',$num,time()+60*60*24*3,'/');
            setcookie('userPwd',$userInfo['u_pwd'],time()+60*60*24*3,'/');
          }

   			$message['status'] = 1;
        $message['checklogin']='0';
        $message['name']=$userInfo['u_name'];
        $message['u_id'] =$userInfo['id'];
   			echo json_encode($message);
        return;
   		}else{
   			$message['status'] = 2;
        $message['err'] = 'fail';
        $message['checklogin']='1';

   			echo json_encode($message);
        return;
   		}
   	}

/*  退出登录，将session和cookie都删除，后台其他都不做处理
*/
    public function loginout(){
          // 删除cookie
          setcookie('num','',-1,'/');
          setcookie('userPwd','',-1,'/');
          // 删除session
          session_start();
          // 将所有的session置空
          $_SESSION = array();
          // 删除session的cookie
          if(isset($_COOKIE[session_name()])){
            setcookie(session_name(),'',-1,'/');
          }
          // 彻底销毁session
          session_destroy();
    } 
    
  
    /*
        用户注册
    */
        public function register()
        {
             $user = new UserModel;
            try {
             
                 $imge1 =input('img1');
                 $imge2 = input('img2');
                 $imge3 = input('img3');
                 $num = input('num');
                 $name = input('name');
                 $driverNum = input('driverNum');
                 $pass = input('pass');
                
          

              // 1、检查账号是否登录
              $rename = $user->where('u_num',$num)->find();
              if($rename){        //用户已经存在了
                $row['message']=0;
                echo json_encode($row);
                return;
              }else{
                // 2、先存图片到本地,存放地址千万要写绝对地址
                 $path = 'E:/phpStudy/WWW/userphoto';
                
              $name1 = $num."img1".rand(1,1000);
              $name2 = $num."img2".rand(1,1000);
              $name3 = $num."img3".rand(1,1000);

              $im1 =base64_image_content($imge1,$path,$name1);
              $im2 =base64_image_content($imge2,$path,$name2);
              $im3 =base64_image_content($imge3,$path,$name3);


              //3、 插入数据和图片  
              $user->u_name=$name;
              $user->u_pwd=$pass;
              $user->u_driver_license=$driverNum;
              $user->u_num=$num;
              $user->u_driver_photo1=$im1;
               $user->u_driver_photo2=$im2;
               $user->u_photo=$im3;

             
                if($user->save()){    //成功注册
                  // 注册成功生成用户唯一的投诉帮助数据库
                  $uid = $user->id;
                    $chatdb = 'help'.$uid;
               Db::query("CREATE TABLE $chatdb (id INT, sid INT,uid INT,sign INT,chat TEXT, test INT)");
                  $row['message']=1;
               echo json_encode($row);
               return;
                
              }else{
                $row['message']=-1;     //注册失败
                echo json_encode($row);
                }
                return;
              }
                
              } catch (Exception $e) {
                $row['error'] = $e;
                $row['message']=-2;

              }
                                
        }
       
  // 用户修改信息请求
        public function getUserManage(){
          $uid = input('uid');
          // $uid = 13642599138;
          $user = UserModel::get($uid);
          $data['u_num'] = $user->u_num;
          $data['u_status'] = $user->u_status;
          echo json_encode($data);
          return;
        }

        // 修改用户信息
        public function usermanage(){
          $uid = input('$uid');
          $newpwd = input('newpwd');
          $imge1 = input('u_driver_photo1');
          $imge2 = input('u_driver_photo2');
          $u_name = input('u_name');
          $u_num = input('u_num');
          $imge3 = input('u_photo');
          $u_pwd = input('u_pwd');
          // 判断用户是否在审核中：u_status： 1验证成功，可以修改个人信息  0信息正在验证中，不能修改个人信息 -1图片验证失败，只能修改图片信息 
          $user = UserModel::get($uid);
          $status = $user->u_status;

          if($status==-1){
             $path = 'E:/phpStudy/WWW/userphoto';
         
              $name1 = $num."img1".rand(1,1000);
              $name2 = $num."img2".rand(1,1000);
              $name3 = $num."img3".rand(1,1000);

              $im1 =base64_image_content($imge1,$path,$name1);
              $im2 =base64_image_content($imge2,$path,$name2);
              $im3 =base64_image_content($imge3,$path,$name3);

               $user->u_driver_photo1=$im1;
               $user->u_driver_photo2=$im2;
               $user->u_photo=$im3;
                if($user->save()){    //成功注册
                  $row['message']=5;
                  echo json_encode($row);
                  return;   
              }else{
                $row['message']=0;
                  echo json_encode($row);
                  return;   
              }
          }
          $pwd = $user->u_pwd;
          $time = intval(time()/10);
          $pw =$pwd.$time;
          $Upwd = md5($pw);
          
          if($Upwd==$u_pwd){
            if($u_name){
            $user->u_name=$u_name;

          }
          if($u_pwd){
            $user->u_pwd=$u_pwd;
          }
          if($u_num){
            $user->u_num=$u_num;
          }
          if($user->save()){
              $row['message']=1;
              echo json_encode($row);
              return; 
          }else{
            $row['message']=0;
              echo json_encode($row);
              return; 
          }

          }else{
            $row['message']=0;
              echo json_encode($row);
              return;  
          }

        }

        // 展示个人资产优惠券、现金券、充值卡
        // 请求时删除过期的优惠券
        public function myAsset(){
          $uid = input('uid');
          $uid =13642599138 ;
          $time = time();
          // 删除过期的优惠券
           $accessid = Assetaccess::where('endTime','<=',$time)->column('id');
          foreach ($accessid as $i) {
            $adelete = Assetaccess::get($i);
            $adelete->delete();
          }
          $arr = array();
          // cha查找时间
          $accessaid =Assetaccess::where('user_id',$uid)->select();
          foreach ($accessaid as $key ) {
            $data['startTime'] = $key['startTime'];
            $data['endTime'] = $key['endTime'];
            $asset = Asset::get($key['asset_id']);
            $data['img'] = $asset->img;
            $data['rangetext'] = $asset->rangetext;
            $data['ruletext'] = $asset->ruletext;
            $data['title'] = $asset->title;
            array_push($arr,$data);
          }
          echo json_encode($arr);
          return;
        }
}
