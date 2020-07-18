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

    private $user;
    private $token;

    /**
     * JWT constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();

    }

    public function set_user($arr_user = [])
    {
        $this->user = $arr_user;
    }

    /**
     * 生成token
     *
     * @return string
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function create_token()
    {

        $obj_payload = new Payload();
        $obj_payload->set_user($this->user);

        //playload-header
        $payload_header = $obj_payload->payload_header();

        //playload-claim
        $payload_claim = $obj_payload->payload_claim();

        $payload = $payload_header . '.' . $payload_claim;

        //签名signature
        $obj_sign  = new Sign();
        $signature = $obj_sign->signature($payload);
        $token     = $payload . '.' . $signature;
        $this->token = $token;
        return $token;
    }

    public function get_token(){
        $token_key = $this->config['token_key'];
        $token = \Illuminate\Support\Facades\Request::header($token_key);
        $this->token = $token;
        return $token;
    }

//    public function decode_token_claim(){
//        $token = $this->token;
//        $arr_claim = json_decode(base64_decode(explode('.',$token)[1]),true);
//        return $arr_claim;
//    }
    
    public function check_token($guard_driver = ''){
        $token = $this->get_token();
        if(is_null($token)){
            return false;
        }
        
        $arr_token = explode('.',$token);
        
        //签名验证 token是否合法
        $obj_sign  = new Sign();
        $payload = $arr_token[0].'.'.$arr_token[1];
        $token_sign = $arr_token[2];
        $signature = $obj_sign->signature($payload);
        if($token_sign != $signature){
            return false;
        }

        //验证token是否过期
        $arr_claim = json_decode(base64_decode($arr_token[1]),true);
        $expire_time = $arr_claim['exp'];
        $now_time = time();
        if($now_time >= $expire_time){
            return false;
        }

        //登录驱动 redis、session
        switch ($guard_driver)
        {
            case 'redis':
                $n_userid = intval($arr_claim['sub']);
                break;
            default:
                return false;
        }
        $arr_user_token = predis_str_get($this->redis_token_key.$n_userid,$this->redis_db);
        $s_user_token = $arr_user_token['access_token'];

        //登录模式 se-单设备登录(Single equipment) me-多设备登录(More equipment)
        $jwt_model = $this->jwt_model;
        if($jwt_model == 'se' && ($token !== $s_user_token)){
            return false;
        }
        return true;
    }


}