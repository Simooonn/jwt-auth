<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth;

class JWT
{

    protected $config;
    protected $redis_token_key = 'jwt_token_user_';
    protected $redis_user_key = 'user_';
    protected $redis_db = 15;
    protected $jwt_model;
//    protected $redis_user_expiretime = 4;//用户数据保存有效期 单位小时

    protected function __construct()
    {
        $this->config = config('hashyoo-jwt');
        $this->jwt_model = $this->config['jwt_model'];
        $this->jwt_model = $this->config['jwt_model'];
        $this->jwt_model = $this->config['jwt_model'];
        $this->jwt_model = $this->config['jwt_model'];
        $this->jwt_model = $this->config['jwt_model'];
    }

    protected function get_ttl(){
        $ttl_time        = $this->config['ttl'] * 60 * 60;
        return $ttl_time;
    }

    protected function get_user_expire(){
        $user_expire_time = $this->redis_user_expiretime * 60 * 60;
    }


}