<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth;

use HashyooJWTAuth\JWT\Guard;
use HashyooJWTAuth\JWT\JWT;

class JWTAuth
{

    private $model_jwt;

    public function __construct()
    {
        $this->model_jwt = new JWT();
    }

    public function guard($module = ''){
        $this->model_jwt = new JWT($module);
        return $this;
        //        dd($this);
    }

    /**
     * 尝试登录
     *
     * @param array $login_data
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function attempt($login_data = [])
    {
        $result = $this->model_jwt->attempt($login_data);


        dd($result);
    }

    /**
     * 刷新token
     *
     * @return mixed
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function refresh_token()
    {


    }

    /**
     * 检测登录状态
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function check()
    {

    }

    /**
     * 获取用户id
     *
     * @return int|null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function user_id()
    {

    }

    /**
     * 获取用户信息
     *
     * @return mixed|null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function user()
    {

    }

    /**
     * 用户退出登录
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function loginout()
    {

    }

}