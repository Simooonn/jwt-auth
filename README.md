# jwt-auth 登录认证
## 概述
>Json web token，登录认证（接口登录和web session登录），支持单社保和多设备登录，需要redis支持。目前仅适用于laravel

## 兼容框架
目前仅支持laravel5.5+

## 用法

> ### 1、引入composer

```
composer require hashyoo/jwt-auth
```

> ### 2、添加config文件

```
php artisan vendor:publish --provider="HashyooJWTAuth\Providers\JWTAuthProvider"
```

## config文件说明

### 文件名
> hashyoo-jwt.php

### ttl 和 signin_mode
> providers里的ttl和signin_mode优先级要高于数组里一级ttl和signin_mode

> signin_mode：登录模式 se-单设备登录(Single equipment)只有最新的一个token可用， me-多设备登录(More equipment)，只要token不过期，都能使用

### pass_key
> providers里的pass_key可以自定义设置，若不设置，则默认登录表里的密码字段是password

### 接口和web session
> 接口形式：在guards里driver设置成api,在header里传递参数，参数名可自行在配置文件里token_key自定义

> web session形式：在guards里driver设置成session

