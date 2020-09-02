<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth\JWT;

class Base
{

    private $config;

    protected $defaults_guard;
    protected $guards;
    protected $providers;

    
    
//    protected $guard_list;
//
//    protected $signin_mode;//登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)
//
//    protected $redis_db;//redis数据存放库
//
//    protected $redis_token_prefix;//redis token-key token存储redis前缀
//
//    protected $redis_user_prefix;//redis user-key 用户存储redis前缀
//
//    protected $redis_user_expiretime;//用户数据保存有效期 单位小时

    protected function __construct()
    {
        /* 配置 */
        $config = config('hashyoo-jwt');

        $this->config          = $config;
        $this->defaults_guard       = $config['defaults']['guard'];
        $this->guards       = $config['guards'];
        $this->providers       = $config['providers'];

        if (is_null($this->defaults_guard)) {
//            return ['aa'];
            throw new \Exception('没有设置默认的guard');
        }
//        dd($this);
//        $this->token_key       = $config['token_key'];
//        $this->signin_mode     = $config['signin_mode'];
//        $this->secret          = $config['secret'];
//        $this->ttl             = $config['ttl'];
//        $this->algo            = $config['algo'];
//        $this->required_claims = $config['required_claims'];
//        $this->defaults        = $config['defaults'];
//        $this->guards          = $config['guards'];
//        $this->providers       = $config['providers'];
//
//        $this->signin_mode   = empty($config['signin_mode']) ? 'se' : $config['signin_mode'];
//        $this->guard_list    = $config['guards'];
//        $this->provider_list = $config['providers'];

        $this->redis_db              = 15;
        $this->redis_token_prefix    = 'jwt_token_';
        $this->redis_user_prefix     = 'user_';
        $this->redis_user_expiretime = 4;
    }


    /**
     * token有效期
     *
     * @return int
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    protected function get_ttl()
    {
        return intval($this->config['ttl'] * 60 * 60);
    }

    /**
     * 用户数据有效期
     *
     * @return int
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    protected function get_user_expire()
    {
        return intval($this->redis_user_expiretime * 60 * 60);
    }


}