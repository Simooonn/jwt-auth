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

    protected $config;

    protected $signin_mode = 'se';//登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)

    protected $redis_db = 15;//redis数据存放库

    protected $redis_tt = 'jwt_tt_';//
    protected $redis_token_prefix = 'jwt_token_';//redis token-key token存储redis前缀

    protected $redis_user_prefix = 'jwt_user_';//redis user-key 用户存储redis前缀

    protected $redis_user_expiretime = 4;//用户数据保存有效期 单位小时

    protected function __construct()
    {
        /* 配置 */
        $config       = config('hashyoo-jwt');
        $this->config = $config;
    }

}