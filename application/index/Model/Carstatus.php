<?php 
namespace	app\index\model;
use	think\Model;
class Carstatus extends Model
{
	 protected $autoWriteTimestamp = false;
	 protected $updateTime = false;

	
		public function search($list,$s,$e)				
		{	//转换时间类型
			
			$id = array();
		foreach ($list as $cid) 
		{					
		 $info = $this->where('car_id',$cid)->where('endTime','>=',$s)->where('startTime','<=',$e)->select();
		 if(!$info){
		 	array_push($id, $cid);
		 }
		}	
		return $id;						
						
		}
		// 查询一辆车是否在订单表冲突
		public function hasorderstatus($startTime,$endTime,$carId)
		{
			
			$info1 = $this->where('car_id',$carId)->where('endTime','>=',$startTime)->where('startTime','<=',$endTime)->find();
			if($info1){
				return true;
			}return false;
		}
		//删除临时表
		public function cancel($carstarusid)
		{
			$carsta = $this::get($carstarusid);					
			$carsta->delete();								
			return 1;
		}


}

?>