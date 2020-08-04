<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth;

class Token extends JWT
{

    private $new_payload;
    private $new_sign;
    private $guard;
    private $token;
    private $provider;
    private $provider_signin_mode;
    private $redis_key_token;

    public function __construct($guard)
    {
        parent::__construct();
        $this->new_sign = new Sign();
        $this->guard = $guard;
        $this->new_payload = new Payload($guard);
        $this->provider = $this->config['providers'][$guard['provider']];
        $this->token = $this->token();
        $this->redis_key_token = $this->redis_token_prefix.$this->guard['provider'].'_';
        $this->provider_signin_mode = $this->provider['signin_mode'];

    }

    /**
     * 获取token
     *
     * @return array|mixed|string|null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function token(){
        $guard_driver = $this->guard['driver'];
        $token_key = $this->config['token_key'];
        if($guard_driver == 'session'){
            session_start();
            $token = $_SESSION[$token_key];
        }
        else{
            $token = \Illuminate\Support\Facades\Request::header($token_key);  
        }
     
        return $token;
    }

    private function provider_signin_mode()
    {
        $provider_signin_mode = is_null($this->provider_signin_mode) ? $this->signin_mode: $this->provider_signin_mode;
        return $provider_signin_mode;
    }

    /**
     *
     *
     * @return array
     * @throws \Exception
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function arr_token(){
        $token = $this->token;
        if(empty($token)){
            throw new \Exception('token不存在');
        }
        $arr_token = explode('.',$token);
        if(count($arr_token) != 3){
            throw new \Exception('token参数格式错误');
        }
        
        return $arr_token;
    }
    
    private function get_arr_claim(){
        $arr_token = $this->arr_token();
        $arr_claim = json_decode(base64_decode($arr_token[1]),true);
        
        return $arr_claim;
    }
    
    private function get_lifetime(){
        $n_lifetime = $this->get_arr_claim()['lft'];
        return $n_lifetime;
    }

    private function get_expiretime(){
        $n_expiretime = $this->get_arr_claim()['exp'];
        return $n_expiretime;
    }

    public function get_user_id(){
        $n_user_id = intval($this->get_arr_claim()['sub']);
        return $n_user_id;
    }
    
    /**
     * redis存放 token
     *
     *
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function redis_set_token(){
        $token = $this->token;
        $n_lifetime = $this->get_lifetime();
        $n_userid = $this->get_user_id();

        $arr_jwt_token = [
          'id'=>$n_userid,
          'access_token'=>$token,
          'token_type'=>'bearer',
          'expires_in'=>$n_lifetime,
        ];
        $redis_key = $this->redis_key_token.$n_userid;
        $n_db = $this->redis_db;
        predis_str_set($redis_key,$arr_jwt_token,$n_lifetime,$n_db);
    }

    private function get_token(){
        $token = is_null($this->token) ? $this->token():$this->token;
        return $token;
    }

    /**
     * 生成token
     *
     * @param int $n_user_id
     *
     * @return string
     * @throws \Exception
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function create_token($n_user_id = 0)
    {
        $n_user_id = intval($n_user_id);
        if($n_user_id <= 0){
            throw new \Exception('用户id不存在');
        }

        $payload = $this->new_payload->get_payload($n_user_id);

        //签名signature
        $signature = $this->new_sign->signature($payload);
        $token     = $payload . '.' . $signature;
        $this->token = $token;

        $guard_driver = $this->guard['driver'];
        if($guard_driver == 'session'){
            session_start();
            $token_key = $this->config['token_key'];
            $_SESSION[$token_key] = $token;
        }

        //redis存放 token
        $this->redis_set_token();
        return $token;
    }
    
    public function check_token(){
        $token = $this->token;
        if(is_null($token)){
            return false;
        }
        $arr_token = $this->arr_token();

        //签名验证 token是否合法
        $payload = $arr_token[0].'.'.$arr_token[1];
        $token_sign = $arr_token[2];
        $signature = $this->new_sign->signature($payload);
        if($token_sign != $signature){
            return false;
        }

        //验证token是否过期
        $expire_time = $this->get_expiretime();
        $n_userid = $this->get_user_id();
        $now_time = time();
        if($now_time >= $expire_time){
            return false;
        }

        $redis_key = $this->redis_key_token.$n_userid;
        $n_db = $this->redis_db;
        $arr_user_token = predis_str_get($redis_key,$n_db);
        $s_user_token = $arr_user_token['access_token'];

        //登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)
        $signin_mode = $this->provider_signin_mode();
/*        switch ($signin_mode)
        {
            case 'se':
                if($token === $s_user_token){
                    return true;
                }
                else{
                    return false;
                }
                break;

            case 'me':
                //多设备登录时，一个设备退出登录时出现问题，需要给token存redis，并设置有效期
//                if($token === $s_user_token){
//                    return true;
//                }
//                else{
//                    return false;
//                }
                break;
            default:
                return false;
        }*/
        if($signin_mode == 'se' && ($token !== $s_user_token)){
            return false;
        }

        return true;
    }





}