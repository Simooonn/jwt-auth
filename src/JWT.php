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
    protected $redis_token_key = 'jwt_token_user_';
    protected $redis_user_key = 'user_';
    protected $redis_db = 15;
    protected $redis_user_expiretime = 4;//用户数据保存有效期 单位小时

    protected function __construct()
    {
        $this->config = config('hashyoo-jwt');
        $this->jwt_model = $this->config['jwt_model'];
    }
    
    public function get_ttl(){
        $ttl_time        = $this->config['ttl'] * 60 * 60;
        return $ttl_time;
    }

    protected function jwt_user(){
        $n_user_id = $this->jwt_user_id();
        if($n_user_id === null){
            return null;
        }

        $arr_user = predis_str_get($this->redis_user_key.$n_user_id,$this->redis_db);
        if(is_null($arr_user)){
            
        }


    }

    protected function jwt_user_id(){
        $new_jwtauth = new  JWTAuth();
        if($new_jwtauth->check() !== true){
            return null;
        }

        $new_token = new Token();
        $token = $new_token->get_token();
        $arr_token = explode('.',$token);
        $arr_claim = json_decode(base64_decode($arr_token[1]),true);
        $n_userid = intval($arr_claim['sub']);

        return intval($n_userid);
    }
    
  


}