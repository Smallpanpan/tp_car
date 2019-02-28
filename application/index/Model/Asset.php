<?php 
namespace	app\index\model;
use	think\Model;
class Asset extends Model
{
	public function user(){
		return	$this->belongsToMany('User','assetaccess');
	}
	
}

 ?>

 

