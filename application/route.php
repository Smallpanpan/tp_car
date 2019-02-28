<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------






return [
  
    '[User]' =>[
    	'/posttest' => ['User/posttest'],
    	'/test'		=> ['User/test'],
        '/login'    => ['User/login'],
        '/loginout' => ['User/loginout'],
        '/register' => ['User/register'],
        '/add'      => ['User/add'],
        '/getUserManage' =>['User/getUserManage'],
        '/usermanage'=>['User/usermanage'],
        '/myAsset'  =>['User/myAsset'],
        
    ],
    '[Base]' =>[
    	'/readposter' =>['Base/readposter'],
    	'/test'		=> ['Base/test'],
    	'/adduser'		=> ['Base/adduser'],
    	'/readuser'		=> ['Base/readuser'],
    ],
    '[Car]' =>[
        '/selectcar' =>['Car/selectcar'],
        '/myorder'   =>['Car/myorder'],
        '/quitOrder' =>['Car/quitOrder'],

    ],
    '[Order]' =>[
        '/test' =>['Order/test'],
        '/createorder'=>['Order/createorder'],
        '/myorder' =>['Order/myorder'],
        '/canceltemporaryorder' =>['Order/canceltemporaryorder'],
        '/cancelorder' => ['Order/cancelorder'],
        '/putchat' =>['Order/putchat'],
        '/getchat'=>['Order/getchat'],

    ],
    '[Sorder]' =>[
        '/putchat' =>['Sorder/putchat'],
        '/selectuser'=>['Sorder/selectuser'],
        '/ending' =>['Sorder/ending']
    ]

];
