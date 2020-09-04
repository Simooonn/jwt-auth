<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth\JWT;

use Illuminate\Support\Facades\Hash;

class JWT extends Base
{

    private $model_query;//

    private $model_token;//

    private $model_guard;//

    public function __construct($module = '')
    {
        parent::__construct();
        $this->model_guard = new Guard($module);

        $provider          = $this->model_guard->get_provider();
        $this->model_query = new Model($provider);

        $guard             = $this->model_guard->get_guard();
        $this->model_token = new Token($guard, $provider);

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
        /* 判断登录数据有没有密码字段 */
        $password_key = $this->model_guard->get_password_key();
        if (!array_key_exists($password_key, $login_data)) {
            return false;
        }

        /* 查询用户并验证 */
        $s_pass      = $login_data[$password_key];
        $arr_wherein = yoo_array_remove($login_data, [$password_key]);
        $user        = $this->model_query->get_one($arr_wherein);
        if (is_null($user)) {
            return false;
        }
        $n_uid = intval($user['id']);
        if ($n_uid <= 0) {
            return false;
        }

        //验证密码
        if (Hash::check($s_pass, $user[$password_key]) !== true) {
            return false;
        }

        //设置token
        $this->user = $user;
        $token      = $this->model_token->create_token($user);
        return $token;
    }

    /**
     * 刷新token
     *
     * @return mixed
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function refresh_token()
    {
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
        $result = $this->model_token->check_token();
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
        $n_userid = $this->model_token->claim_user_id();
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
        $arr_user = $this->model_token->get_user();
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