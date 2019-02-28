<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: panpan 
// +----------------------------------------------------------------------

// 应用公共文件
  function base64_image_content($base64_image_content,$path,$name){
		    //匹配出图片的格式
		    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
		        $type = $result[2];
		        $new_file = $path."/".date('Ymd',time())."/";
		        if(!file_exists($new_file)){
		            //检查是否有该文件夹，如果没有就创建，并给予最高权限
		            mkdir($new_file, 0700);
		        }
		        $new_file = $new_file.$name.".{$type}";
		        $file = "http://localhost:8889/userphoto/".$name.".{$type}";
		        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
		            return  $file;
		        }else{
		            return false;
		        }
		    }else{
		        return false;
		    }
		}
		/**
 * 计算租车基础费用（不包括固定服务费）需要的价格 和租车天数明细
 * @param  integer $dcharge   	[日租价格]
 * @param  integer $charge4   	[4小时内价格]
 * @param  integer $charge8		[8小时内]
 * @param  integer  $start 		[取车时间]
   @param  integer  $end  		[还车时间]
   @return [array]   $data  	[返回数组]
   @return [array]   $data[0]    [总价格]
   @return [array]   $data[1]    [天数]
   @return [array]   $data[2]    [是否在0-4小时区间]
   @return [array]   $data[3]    [是否在4-8小时区间]

 */
		// 
		//          
        //         //4小时内价格
        //           // 
        //             //
        //  			//
        // 价格规则：在一天内：若0-4小时，按4小时梯度价格收费，4-8小时，按8小时梯度收费，超过8小时，按一天单价梯度收费；超过一天，取整数天数的为天数单价计算，余数按照以上不足一天规则计算
        
		function computeprice($dcharge,$charge4,$charge8,$start,$end)
		{
			$date = 0;
			if(($end-$start)>86400){
				$date =(int)(floor($end-$start)/86400);		//floor是往下取整，得到的是float
			 	$end = $end-$date*86400;
			}

			if(($end-$start)>28800)			//时间大于8小时
			{  	
				$date = $date+1;
			 	$price = $date*$dcharge;
			 	$data[0] = $price;
			 	$data[1] = $date;
			 	$data[2] = 0;
			 	$data[3] = 0;
			 	return $data;

			}
			if(($end-$start)>14400){	  //时间大于4小时(4-8)
					$price = $date*$dcharge+$charge8;
					$data[0] = $price;
			 		$data[1] = $date;
			 		$data[2] = 1;
			 		$data[3] = 0;
			 		return $data;
				}
			if(($end-$start)>0){			//时间区间在0-4小时内
					$price = $date*$dcharge+$charge4;
					$data[0] = $price;
			 		$data[1] = $date;
			 		$data[2] = 0;
			 		$data[3] = 1;
			 		return $data;
				}else{
					$price = $date*$dcharge;
					$data[0] = $price;
			 		$data[1] = $date;
			 		$data[2] = 0;
			 		$data[3] = 0;
			 		return $data;
				}
			


		}