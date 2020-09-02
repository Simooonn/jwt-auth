<?php

/*
 * This file is part of jwt.
 *
 */

use App\User;

return [


  'token_key' => 'jwt-token',//header头部token key

  'signin_mode' => 'se',//登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)

  'secret' => env('JWT_SECRET'),//算法私钥

  'ttl' => env('JWT_TTL', 60),//token有效时间 单位小时

  'algo' => env('JWT_ALGO', 'HS256'),//token生成算法

  'required_claims' => [
    'iss',//jwt签发者              登录路由
    'iat',//jwt的签发时间          签发时间戳
    'exp',//jwt的签发时间          过期时间戳
    'lft',//jwt的生存时间          有效时间
    'sub',//jwt所面向的用户        用户ID
  ],

  'defaults' => [
    'guard' => 'admin',
  ],


  'guards' => [

      //管理后台
      'admin' => [
        'driver'   => 'session',// 1.session - web session模式 2.api - 接口模式 2种模式，二选一
        'provider' => 'users',
      ],
  ],


  'providers' => [
    'users' => [
      'driver' => 'eloquent',// 1.eloquent - laravel ORM 目前只有这一种
      'model'  => App\User::class,
    /*  'ttl'=>2,//token有效时间 单位小时 设置后优先使用此值
      'signin_mode' => 'me',//登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment) 设置后优先使用此值
      'pass_key'=>'password',*/
    ],

    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
  ],


];
