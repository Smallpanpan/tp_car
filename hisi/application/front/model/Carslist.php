<?php 
namespace	app\index\model;
use	think\Model;
use app\index\model\Carsystem;
class Carslist extends Model
{
	public	function carstatus()				
		{								
			return	$this->hasMany('Carstatus','car_id','id');				
		} 
		public	function carsystem() 
		{								
			return	$this->belongsTo('Carsystem');				
		}
		public function select($carsid){
			$car = array();
			foreach ($carsid as $carkey) {
			$carsys = $this->where('id',$carkey)->value('car_system_id'); //找到该信息的车辆
			$carsystem	=	Carsystem::get($carsys)->toArray();
			$carsystem['carid'] = $carkey;
			array_push($car, $carsystem);
			}
			

			return $car;
		}

}

?>