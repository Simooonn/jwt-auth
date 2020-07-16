<?php

/*
 * This file is part of jwt.
 *
 */

return [

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



];
