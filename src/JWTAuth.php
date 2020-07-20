<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JWTAuth extends JWT
{

    private $defaults;

    private $guard_list;

    private $provider_list;

    private $guard;

    private $provider;

    private $user;

    private $token;

    private $new_model;

    private $new_token;

    public function __construct($module = '')
    {
        parent::__construct();
        $this->defaults      = $this->config['defaults'];
        $this->guard_list    = $this->config['guards'];
        $this->provider_list = $this->config['providers'];
        $this->set_guard_provider($module);

        $this->new_model = new JwtModel($this->provider);
        $this->new_token = new Token($this->guard);
    }

    private function set_guard_provider($module)
    {
        /*设置guard和provider*/
        $module   = $module === '' ? $this->defaults['guard'] : $module;
        $guard    = $this->guard_list[$module];
        $provider = $this->provider_list[$guard['provider']];

        if (is_null($module)) {
            throw new \Exception('没有设置默认的guard');
        }
        if (is_null($guard)) {
            throw new \Exception('没有找到对应的guard');
        }
        if (is_null($provider)) {
            throw new \Exception('没有找到对应的provider');
        }

        $this->guard    = $guard;
        $this->provider = $provider;
    }

    private function get_user_id()
    {
        return $this->user['id'];
    }

    /**
     * 设置token
     *
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function set_token()
    {
        $token       = $this->new_token->create_token($this->get_user_id());
        $this->token = $token;
    }

    /*    private function get_token(){
            $token = is_null($this->token) ? $this->token():$this->token;
            return $token;
        }*/

    /**
     * 验证凭证
     *
     * @param array $login_data
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function attempt_login($login_data = [])
    {
        /* 判断登录数据有没有密码字段 */
        $password_key = $this->provider['pass_key'] ? $this->provider['pass_key'] : 'password';
        if (!array_key_exists($password_key, $login_data)) {
            return false;
        }

        /* 查询用户并验证 */
        $s_pass      = $login_data[$password_key];
        $arr_wherein = yoo_array_remove($login_data, [$password_key]);
        $user        = $this->new_model->get_one($arr_wherein);
        if (is_null($user)) {
            return false;
        }
        $s_password = $user[$password_key];

        //验证密码
        if (Hash::check($s_pass, $s_password) !== true) {
            return false;
        }

        //设置token
        $this->user = $user;
        $this->set_token();
        return true;
    }


    //尝试登录
    public function attempt(array $login_data)
    {
        if ($this->attempt_login($login_data) !== true) {
            return false;
        }

        return $this->token;
    }

    /**
     * 检测登录状态
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function check(){
        $result = $this->new_token->check_token();
        return $result;
    }

    public function user_id(){
        if($this->check() !== true){
            return null;
        }
        $n_userid = $this->new_token->get_user_id();
        return $n_userid;
    }

    public function user(){
        $n_user_id = $this->user_id();
        if($n_user_id === null){
            return null;
        }

        $redis_key = $this->redis_user_key.$this->guard['provider'].'_'.$n_user_id;
        $n_redis_db = $this->redis_db;
        $arr_user = predis_str_get($redis_key,$n_redis_db);
        if(is_null($arr_user)){
            $provider_driver = $this->provider['driver'];
            //        $this->provider_driver = $provider_driver;

            switch ($provider_driver) {
                case 'eloquent':
                    $arr_user = $this->model->find($n_user_id);
                    if(is_null($arr_user)){
                        return null;
                    }
                    $arr_user = $arr_user->toarray();
                    break;
                default:

            }

            $n_expiretime = $this->get_user_expire();
            predis_str_set($redis_key,$arr_user,$n_expiretime,$n_redis_db);

        }

        return $arr_user;
    }


}