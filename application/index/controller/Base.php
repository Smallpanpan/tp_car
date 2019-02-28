<?php
// 这里是写控制方法的地方，前台可以访问主机地址/index/方法名  就可以访问不同的方法返回不同的参数了
namespace app\index\controller;
use	think\Controller; 
use think\Db;
use app\index\model\User;
use app\index\model\Carsystem;
use app\index\model\Carstore;
use app\index\model\Carslist;
use app\index\model\Carstatus;
use app\index\model\Poster;
use app\index\model\City;
use app\index\model\Region;

class Base extends Controller
{
    public function index()
    {
        echo " this is bases index";
        
       echo json_encode($row);
    }
    public function test()
    {   //创建数据库操作
        // $conn = mysql_connect('localhost','root','123456');//连接数据库，根据自己的数据库填写 
        // mysql_select_db('bianquan',$conn);//选着数据库表 
        // $sql="CREATE TABLE site (id INT, test CHAR(20))";//操作数据库 
        //  mysql_query($sql,$conn); //执行操作 
        // 用tp的内置类
        // Db::query('CREATE TABLE site (id INT, test CHAR(20))');
        $site = 'aa';
        // Db::query("CREATE TABLE $site (id INT, test CHAR(20))");
        // 查询
        // $resulte = Db::query("select * from $site");
        // echo json_encode($resulte);
       //     $chat = input('chat');
       //      $uid = 2;
       //      $chat = '';
       //      $chatdb = 'help'.$uid;
       // Db::execute("insert into $chatdb (uid,chat,sign) values($uid,'".$chat."',1)");
         $uid = 3;
        $chatdb = 'help'.$uid;
        Db::query("CREATE TABLE $chatdb (id INT, sid INT,uid INT,sign INT,chat TEXT, test INT)");

    }
    // 显示背景和网点选项
    public function readposter(){
        $C = Model("City");
        $R = Model("Region"); 
        $city = City::all();
        $num1 = $C->count();
        $num2 = $R->count();
        $region = array();
        $store = array();
        for($n=1;$n<=$num1;$n++)
        {
            $re = Region::where('cityid',$n)->column('name');
           $region[$n]=$re;  
        }
        for ($i=1; $i <=$num2 ; $i++) { 
            $ca = Carstore::where('region',$i)->select();
           array_push( $store, $ca); 
        }
        $data['num1'] = $num1;
        $data['num2'] = $num2;
        $data['store'] = $store;
        $data['region'] = $region;
        $data['city'] = $city;
        // $region[0 ]
        $da =Poster::all();
        $data['poster'] = $da;
        echo json_encode($data); 
        return;
    }

    /*u_id [id] u_name[用户名] u_num [账号]
        在通过模型直接从数据库插入数据
    */
    public function adduser(){
         // $userModel = model('User');
        echo "test1";
        $userModel = new User;
        $userModel->u_name = '张一山';
        $userModel->u_num = '13642599051';
        $userModel->u_pwd = '123456';
        if($userModel->save()){
            echo "添加成功";
        }else{
            echo "添加失败！";
        }
        // 查询第一天数据
        // $this->readuser();

    }
     
    public function readuser(){
        // 查询对应的数据（u_id=2，ps带下划线的要变成驼峰规范写法）
        // $userModel = User::get(['u_id'=>'2']);
        // echo $userModel;
        // 使用SQL查询构造器查询
        // $userModel = User::where('u_id','in',[2,26])->select();
        // echo $userModel[0];
        // echo $userModel[1];
        // 查询一列数据
        $list = User::all();
        foreach ($list as $user) {
            echo $user->u_name;
        }
    }
    

}


