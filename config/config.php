<?php

/*
 * This file is part of jwt.
 *
 */

use App\User;

return [
  'demo'=>[
      /*列出列表，仅供下面配置选择*/
      'guards_drivers'=>[
        'session',//web session模式
        'api',//接口模式
      ],

      /*列出列表，仅供下面配置选择*/
      'providers_drivers'=>[
        'eloquent',//laravel ORM
      ],

  ],

  'token_key' => 'jwt-token',//header头部token key

  'signin_mode' => 'se',//登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)

  'secret' => env('JWT_SECRET'),//算法私钥

  'ttl' => env('JWT_TTL', 60),//token有效时间 单位小时

  'algo' => env('JWT_ALGO', 'HS256'),//token生成算法

  'required_claims' => [
    'iss',//jwt签发者              -登录路由
    'iat',//jwt的签发时间          -签发时间戳
    'exp',//jwt的签发时间          -过期时间戳
    'lft',//jwt的生存时间          -有效时间
    'sub',//jwt所面向的用户        -用户ID
  ],

  'defaults' => [
    'guard' => 'web',
  ],


  'guards' => [

      //管理后台
      'admin' => [
        'driver' => 'session',//只能从上方 demo=>guards_drivers 里选择
//        'driver' => 'api',
        'provider' => 'users',
      ],
  ],


  'providers' => [
    'users' => [
      'driver' => 'eloquent',//只能从上方 demo=>providers_drivers 里选择
      'model' => App\User::class,
      //      'ttl'=>2,//token有效时间 单位小时 设置后优先使用此值
      //      'signin_mode' => 'me',//登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment) 设置后优先使用此值
      //      'pass_key'=>'password',
    ],

    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
  ],


];