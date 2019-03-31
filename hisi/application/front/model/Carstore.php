<?php 
namespace	app\index\model;
use	think\Model;
class Carstore extends Model
{
		public	function carslists()				
		{								
			return	$this->hasMany('Carslist','car_store_id');				
		} 
}

?>

