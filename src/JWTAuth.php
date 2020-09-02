<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth;

use Illuminate\Support\Facades\Hash;

class JWTAuth extends JWT
{

    private $defaults;//默认guard

    private $guard_list;//guard列表

    private $provider_list;//provider列表

    private $guard;//当前使用的guard

    private $provider;//当前使用的provider

    private $user;//用户信息

    private $token;//用户token

    private $new_jwt_model;//JwtModel实例化

    private $new_token;//Token实例化

    private $redis_key_user;//用户redis前缀

    private $redis_key_token;//token redis前缀

    public function __construct($module = '')
    {
        parent::__construct();
        $this->init($module);
    }

    private function init($module)
    {
        $this->defaults      = $this->config['defaults'];
        $this->guard_list    = $this->config['guards'];
        $this->provider_list = $this->config['providers'];
        $this->set_guard_provider($module);

        $this->new_jwt_model = new JwtModel($this->provider);
        $this->new_token     = new Token($this->guard);

        $this->redis_key_user  = $this->redis_user_prefix . $this->guard['provider'] . '_';
        $this->redis_key_token = $this->redis_token_prefix . $this->guard['provider'] . '_';
    }

    /**
     * 设置guard和provider
     *
     * @param string $module
     *
     * @throws \Exception
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function set_guard_provider($module = '')
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

    /**
     * 设置token
     *
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function set_token()
    {
        $token       = $this->new_token->create_token($this->user['id']);
        $this->token = $token;
    }

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
        $user        = $this->new_jwt_model->get_one($arr_wherein);
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
        $this->redis_set_user($user['id']);
        return true;
    }

    /**
     * redis存储用户
     *
     * @param $n_user_id
     *
     * @return |null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function redis_set_user($n_user_id)
    {
        $redis_key    = $this->redis_key_user . $n_user_id;
        $n_redis_db   = $this->redis_db;
        $arr_user     = $this->new_jwt_model->find($n_user_id);
        $n_expiretime = $this->get_user_expire();
        predis_str_set($redis_key, $arr_user, $n_expiretime, $n_redis_db);

        return $arr_user;
    }

    /**
     * 获取用户信息
     *
     * @return mixed|null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function get_user()
    {
        $n_user_id = $this->user_id();
        if ($n_user_id === null) {
            return null;
        }

        $redis_key  = $this->redis_key_user . $n_user_id;
        $n_redis_db = $this->redis_db;
        $arr_user   = predis_str_get($redis_key, $n_redis_db);
        if (is_null($arr_user)) {
            $arr_user     = $this->new_jwt_model->find($n_user_id);
            $n_expiretime = $this->get_user_expire();
            predis_str_set($redis_key, $arr_user, $n_expiretime, $n_redis_db);
        }

        return $arr_user;
    }

    /**
     * 尝试登录
     *
     * @param array $login_data
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function attempt(array $login_data)
    {
        if ($this->attempt_login($login_data) !== true) {
            return false;
        }

        return $this->token;
    }

    /**
     * 刷新token
     *
     * @return mixed
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function refresh_token()
    {
        $n_user_id        = $this->user_id();
        $this->user['id'] = $n_user_id;
        $this->set_token();
        $this->redis_set_user($n_user_id);
        return $this->token;

    }

    /**
     * 检测登录状态
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function check()
    {
        $result = $this->new_token->check_token();
        return $result;
    }

    /**
     * 获取用户id
     *
     * @return int|null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function user_id()
    {
        if ($this->check() !== true) {
            return null;
        }
        $n_userid = $this->new_token->get_user_id();
        return $n_userid;
    }

    /**
     * 获取用户信息
     *
     * @return mixed|null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function user()
    {
        $arr_user = $this->get_user();
        return $arr_user;
    }

    /**
     * 用户退出登录
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function loginout()
    {
        $n_userid = $this->user_id();
        if ($n_userid === null) {
            return true;
        }

        $redis_key = $this->redis_key_token . $n_userid;
        $n_db      = $this->redis_db;
        $result    = predis_str_del($redis_key, $n_db);
        if ($result) {
            return true;
        }
        else {
            return false;
        }
    }

}