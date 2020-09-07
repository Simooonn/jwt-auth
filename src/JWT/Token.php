<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth\JWT;

class Token extends Base
{

    private $new_payload;

    private $new_sign;

    private $guard;

    private $token;

    private $user;

    private $model_query;

    private $provider_signin_mode;

    public function __construct($guard, $provider)
    {
        parent::__construct();
        $this->new_sign             = new Sign();
        $this->guard                = $guard;
        $this->new_payload          = new Payload($guard, $provider);
        $this->model_query          = new Model($provider);
        $this->provider_signin_mode = $provider['signin_mode'];
    }

    private function get_redis_key_token()
    {
        return $this->redis_token_prefix . $this->guard['provider'] . '_';
    }

    private function get_redis_key_user()
    {
        return $this->redis_user_prefix . $this->guard['provider'] . '_';
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


    private function provider_signin_mode()
    {
        $provider_signin_mode = is_null($this->provider_signin_mode) ? $this->signin_mode : $this->provider_signin_mode;
        return $provider_signin_mode;
    }

    /**
     *
     *
     * @return array|bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function arr_token()
    {
        $token = $this->get_token();
        if (empty($token)) {
            return false;
            /*$arr_data = [
              'msg'=>'参数错误',
              'err_msg'=>'token不存在',
            ];
            throw new \Exception(yoo_param_str($arr_data,false));*/
        }
        $arr_token = explode('.', $token);
        if (count($arr_token) != 3) {
            return false;
            /*$arr_data = [
              'msg'=>'参数错误',
              'err_msg'=>'token参数格式错误',
            ];
            throw new \Exception(yoo_param_str($arr_data,false));*/
        }

        return $arr_token;
    }

    private function get_arr_claim()
    {
        $arr_token = $this->arr_token();
        $arr_claim = json_decode(base64_decode($arr_token[1]), true);

        return $arr_claim;
    }

    private function claim_lifetime()
    {
        $n_lifetime = $this->get_arr_claim()['lft'];
        return $n_lifetime;
    }

    private function claim_expiretime()
    {
        $n_expiretime = $this->get_arr_claim()['exp'];
        return $n_expiretime;
    }

    public function claim_user_id()
    {
        $n_user_id = intval($this->get_arr_claim()['sub']);
        return $n_user_id;
    }

    /**
     * redis存放 tt
     *
     *
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function redis_save_tt()
    {
        $token      = $this->get_token();
        $n_lifetime = $this->claim_lifetime();
        $n_userid   = $this->claim_user_id();

        $arr_jwt_token = [
          'id'           => $n_userid,
          'access_token' => $token,
        ];
        $redis_key     = $this->redis_tt . md5($token);
        $n_db          = $this->redis_db;
        predis_str_set($redis_key, $arr_jwt_token, $n_lifetime, $n_db);
    }

    /**
     * redis存放 token
     *
     *
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function redis_save_token()
    {
        $token      = $this->get_token();
        $n_lifetime = $this->claim_lifetime();
        $n_userid   = $this->claim_user_id();

        $arr_jwt_token = [
          'id'           => $n_userid,
          'access_token' => $token,
          'token_type'   => 'bearer',
          'expires_in'   => $n_lifetime,
        ];
        $redis_key     = $this->get_redis_key_token() . $n_userid;
        $n_db          = $this->redis_db;
        predis_str_set($redis_key, $arr_jwt_token, $n_lifetime, $n_db);
    }

    /**
     * redis存储用户
     *
     * @param $n_user_id
     *
     * @return mixed
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function redis_save_user()
    {
        $arr_user   = $this->user;
        $n_user_id  = $arr_user['id'];
        $redis_key  = $this->get_redis_key_user() . $n_user_id;
        $n_redis_db = $this->redis_db;
        //        $arr_user     = $this->model_query->find($n_user_id);
        $n_expiretime = $this->get_user_expire();
        predis_str_set($redis_key, $arr_user, $n_expiretime, $n_redis_db);

        return $arr_user;
    }


    /**
     * 生成token
     *
     * @param int $n_user_id
     *
     * @return string
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function create_token($n_user_id = 0)
    {
        $n_user_id = intval($n_user_id);
        if($n_user_id == 0){
            return false;
        }
        $arr_user     = $this->model_query->find($n_user_id);
        if(empty($arr_user)){
            return false;
        }

        $payload   = $this->new_payload->get_payload($n_user_id);

        //签名signature
        $signature   = $this->new_sign->signature($payload);
        $token       = $payload . '.' . $signature;
        $this->token = $token;
        $this->user  = $arr_user;

        $guard_driver = $this->guard['driver'];
        if ($guard_driver == 'session') {
            session_start();
            $token_key            = $this->config['token_key'];
            $_SESSION[$token_key] = $token;
        }


        $n_db           = $this->redis_db;
        $redis_key      = $this->get_redis_key_token() . $n_user_id;
        $arr_user_token = predis_str_get($redis_key, $n_db);
        $s_user_token   = $arr_user_token['access_token'];
        if ($this->provider_signin_mode() == 'se') {
            /* 删除 redis_tt */
            $redis_key     = $this->redis_tt . md5($s_user_token);
            $result = predis_str_del($redis_key, $n_db);
        }

        //redis存放 token
        $this->redis_save_tt();
        $this->redis_save_token();
        $this->redis_save_user();
        return $token;
    }

    private function get_token()
    {
        if (empty($this->token)) {
            $guard_driver = $this->guard['driver'];
            $token_key    = $this->config['token_key'];
            if ($guard_driver == 'session') {
                session_start();
                $token = $_SESSION[$token_key];
            }
            else {
                $token = \Illuminate\Support\Facades\Request::header($token_key);
            }
            $this->token = $token;
        }
        return $this->token;
    }

    public function check_token()
    {
        $token = $this->get_token();
        if (empty($token)) {
            return false;
        }

        $redis_key     = $this->redis_tt . md5($token);
        $n_db          = $this->redis_db;
        $result = predis_str_get($redis_key, $n_db);
        if (empty($result)) {
            return false;
        }

        /*$arr_token = $this->arr_token();

        //签名验证 token是否合法
        $payload    = $arr_token[0] . '.' . $arr_token[1];
        $token_sign = $arr_token[2];
        $signature  = $this->new_sign->signature($payload);
        if ($token_sign != $signature) {
            return false;
        }*/

        //验证token是否过期
//        $expire_time = $this->claim_expiretime();
        $n_userid    = $this->claim_user_id();
//        $now_time    = time();
//        if ($now_time >= $expire_time) {
//            return false;
//        }

        $redis_key      = $this->get_redis_key_token() . $n_userid;
        $n_db           = $this->redis_db;
        $arr_user_token = predis_str_get($redis_key, $n_db);
        $s_user_token   = $arr_user_token['access_token'];

        //登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)
        $signin_mode = $this->provider_signin_mode();
        if ($signin_mode == 'se' && ($token !== $s_user_token)) {
            return false;
        }

        return true;
    }


    public function refresh_token(){
        $token = $this->get_token();
        if (empty($token)) {
            return false;
        }

        $n_user_id    = $this->claim_user_id();
        $token = $this->create_token($n_user_id);
        return $token;
    }


    /**
     * 获取用户信息
     *
     * @return mixed|null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function get_user()
    {
        $n_user_id = $this->claim_user_id();
        if ($n_user_id === null) {
            return null;
        }

        $redis_key  = $this->get_redis_key_user() . $n_user_id;
        $n_redis_db = $this->redis_db;
        $arr_user   = predis_str_get($redis_key, $n_redis_db);
        if (is_null($arr_user)) {
            $arr_user     = $this->model_query->find($n_user_id);
            $n_expiretime = $this->get_user_expire();
            predis_str_set($redis_key, $arr_user, $n_expiretime, $n_redis_db);
        }

        return $arr_user;
    }


    public function login_out(){
        $token = $this->get_token();
        if(!empty($token)){
            /* 删除 redis_tt */
            $redis_key     = $this->redis_tt . md5($token);
            $n_db          = $this->redis_db;
            $result = predis_str_del($redis_key, $n_db);

            /* 删除 redis_token */
            $n_userid    = $this->claim_user_id();
            $redis_key      = $this->get_redis_key_token() . $n_userid;
            $arr_user_token = predis_str_get($redis_key, $n_db);
            $s_user_token   = $arr_user_token['access_token'];

            //登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)
            $signin_mode = $this->provider_signin_mode();
            if ($signin_mode == 'se' && ($token == $s_user_token)) {
                //删除 redis_token

                $result = predis_str_del($redis_key, $n_db);
            }
        }

        return true;
    }


}