<?php 
namespace	app\index\model;
use	think\Model;
class Carsready extends Model
{
	protected $autoWriteTimestamp = false;
	 protected $updateTime = false;
	 // 查找临时订单是否存在重复订单
	public function hasorder($startTime,$endTime,$carId,$id)
	{
		$info = $this->where('id','<',$id)->where('carId',$carId)->where('endTime','>=',$startTime)->where('startTime','<=',$endTime)->find();
		if($info){
			return true;
		}else{
			return false;
		}
	}
}

 ?>
