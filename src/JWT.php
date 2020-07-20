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

    protected $jwt_model;//登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)

    protected $redis_db;//redis数据存放库
    protected $redis_token_key;//redis token-key
    protected $redis_user_key;//redis user-key
    protected $redis_user_expiretime;//用户数据保存有效期 单位小时

    protected function __construct()
    {
        $this->config = config('hashyoo-jwt');
        $this->jwt_model = $this->config['jwt_model'];
        $this->redis_db = 15;
        $this->redis_token_key = 'jwt_token_';
        $this->redis_user_key = 'user_';
        $this->redis_user_expiretime = 4;
    }

    protected function token(){
        $token_key = $this->config['token_key'];
        $token = \Illuminate\Support\Facades\Request::header($token_key);
        return $token;
    }

    /**
     * token有效期
     *
     * @return int
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    protected function get_ttl(){
        return intval($this->config['ttl'] * 60 * 60);
    }

    /**
     * 用户数据有效期
     *
     * @return int
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    protected function get_user_expire(){
        return intval($this->redis_user_expiretime * 60 * 60);
    }
 




}