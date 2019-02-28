<?php
// 关于汽车信息查询
namespace app\index\controller;
use	think\Controller; 
use app\index\model\Carsystem;
use app\index\model\Carstore;
use app\index\model\Carslist;
use app\index\model\Carstatus;
use app\index\model\Carsready;
use app\index\model\User;
use app\index\model\Secure;

class Car extends Controller
{
	/*
	正确返回：
  ["system_id"] => 车系
  ["id"] => 车系ID
  ["car_color"] => 车颜色
  ["car_production_time"] => 出厂时间
  ["car_photo_url"] => 图片URL
  ["car_type"] => 汽车类型
  ["car_seat"] => 汽车座位
  ["car_fuel"] => 汽车燃油
  ["car_displacement"] => 汽车功率
  ["car_daily_price"] => 日租价格
  ["car_monthly_price"] => 月租价格
  ["car_volume"] =>续航能力
  ["car_drive"] => 驱动类型
  ["car_Airbag"] => 安全气囊
  ["carid"] => 汽车唯一ID
  ["$carstatus_id"] =>汽车时间表ID
  查找失败返回参数：
  ['status'] = >   1该时段的车已经租完了	0连接数据库出错

	*/
	public function selectcar()
	{
		 $carstatusModel = model('Carstatus');
		 $carslistModel = model('Carslist');
		 $s=input('startTime')/1000;
		 $e=input('endTime')/1000;
		 $i=input('pickUp');
		 
		// $s = 1540857600;
		// $e = 1540947600;
		// $i = 1003;

		// endTime: 1538182800000
		// pickUp: "韶关市韶关学院店"
		// startTime: 1538092800000
		try {
		$scar = Carstore::get($i); 
		$list = $scar->carslists()->column('id'); 	//$list就是数组函数,找出网点的所有汽车ID
		$carsid = $carstatusModel->search($list,$s,$e);    //检查每辆汽车是否在规定时间空闲
		if(empty($carsid)){
			$data['status'] = 1;
			echo json_encode($data); 
			return;
		}
		else{
			$carlist = $carslistModel->select($carsid);		//获取空闲车辆信息
			echo json_encode($carlist);
		}
		} catch (Exception $e) {
			echo json_encode($data); 
			return;
		}
		

	}
	/*输入：id:汽车ID，startTime：起租时间，endTime：还车时间	
	返回：message：是否预订成功，order[]:成功返回汽车信息数组

	*/
	public function myorder()
	{
		$startTime=input('startTime')/1000;
		$endTime=input('endTime')/1000;
		$carId=input('carId');
		$uid=input('uid');
		$stid = input('stid');
		// 测试数据
	  // $startTime=1300000000;
	  // $endTime=1600000000;
	  // $carId=104;
	  // $uid=1;
		// 测试用户的合法性
		 $user = User::get($uid);
		 $userpower = $user->u_status;
		 if($userpower==0){
		 $data['message'] = '-5';	//用户审核还没有通过，不能预订
	  	echo json_encode($data);
	  	return;
		 }
	  // 先插入临时表

	  $carsready = new Carsready;
	  $carsready->endTime =$endTime ;
	  $carsready->carId =$carId ;
	  $carsready->startTime =$startTime ;
	  if(!$carsready->save())
	  {
	  	$data['message'] = '-3';	//插入出错，无法预订
	  	echo json_encode($data);
	  	return;
	  }
	  $id = $carsready->id;		//插入成功的id
	 
	  	
	  	
	  // 查询是否有冲突的临时预订
	  $same = $carsready->hasorder($startTime,$endTime,$carId,$id);
	  if($same){
	  	$cardelete = Carsready::get($id);
	  	$cardelete->delete();			//删除临时数据
	  	$data['message'] = '0';	//在同一时间跟别人的预订有冲突，排序比较后的用户，无法预订
	  	echo json_encode($data);
	  	return;
	  }
	  // 查询订单表是否有冲突
	  $carstatusModel = new Carstatus;
	  $same1 = $carstatusModel->hasorderstatus($startTime,$endTime,$carId);
	  if($same1){
	  	$cardelete = Carsready::get($id);
	  	$cardelete->delete();			//删除临时数据
	  	$data['message'] = '-1';	//订单表在之前已经有别人的预订了，无法预订
	  	echo json_encode($data);
	  	return;
	  }
	  // 如果都不冲突的话，就写订单表，确认订单
	  $cardelete = Carsready::get($id);     //删除临时数据
	  $cardelete->delete();
	  $carstatusModel->car_id = $carId;
	  $carstatusModel->startTime =$startTime;
	  $carstatusModel->endTime = $endTime;
	  if(!$carstatusModel->save()){		         
	  	$data['message'] = '-2';	//无法插入，无法预订
	  	echo json_encode($data);
	  	return;
	  }
	  	// 返回网点信息
	  $store = Carstore::get($stid);
	  // 确认订单，返回汽车信息
	  	$carlist = Carslist::get($carId);
	  	$carsystemid = $carlist->car_system_id;
	  	$carsystem = Carsystem::get($carsystemid);
	  	$prcie_4 =$carsystem->car_4_price;
	  	$prcie_8 =$carsystem->car_8_price;
	  	$daily_price = $carsystem->car_daily_price;
	  	$price1 = computeprice($daily_price,$prcie_4,$prcie_8,$startTime,$endTime);  
        $price =$price1[0];             //常规费用（就是不含服务费哦）
        $date = $price1[1];             //天数
        $date_4 = $price1[3];           //是否在0-4小时区间
        $date_8 = $price1[2];           //是否在4-8小时区间
    	$asset = $user->asset;			//优惠券类型二维数组
    	$secure = Secure::all();						//保险类型列表
	   $data['carstatusid'] = $carstatusModel->id; 		//临时表ID
	   $data['date_8']	=$date_8;
	   $data['date_4']	=$date_4;
	   $data['date']	=$date;
	   $data['price_4']	=$prcie_4;
	   $data['price_8']	=$prcie_8;
	   $data['all_price']	=$price;

	   $data['message'] = '1';					//插入状态
	   $data['car_id'] =$carId ;				//汽车ID
	   $data['car_number'] = $carlist->car_number ;		//汽车号码
	   $data['car_deposit'] = $carsystem->car_deposit ;	 
	   $data['car_servuce'] = $carsystem->car_servuce ;
	   $data['car_daily_price'] = $carsystem->car_daily_price ;
	   $data['car_fuel'] = $carsystem->car_fuel ;
	   $data['car_seat'] = $carsystem->car_seat ;
	   $data['car_type'] = $carsystem->car_type ;
	   $data['system_id'] = $carsystem->system_id ;
	   $data['car_photo_url'] = $carsystem->car_photo_url ;
	   $data['u_num'] = $user->u_num;
	   $data['u_name'] = $user->u_name;
	   $data['u_driver_license'] = $user->u_driver_license ;
	   $data['u_id'] = $uid ;
	   // 网点信息   
       $data['location'] =  $store->car_store_site;
	   $data['lng'] =  $store->lng;
	   $data['lat'] =  $store->lat ;

	   $arr['data'] = $data;
	   $arr['asset'] = $asset;
	   $arr['secure'] = $secure;
	  	echo json_encode($arr);	 
	}
// 退出订单接口
	public function quitOrder(){
		$startTime=input('startTime')/1000;
		$carId=input('carId');
		$carstatus = model('Carstatus');
		$cardelete = $carstatus
		->where('car_id',$carId)->where('startTime',$startTime)
		->find();
		$cardelete->delete();
	}	
	


}