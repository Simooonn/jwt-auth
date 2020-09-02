<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth\JWT;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class Payload extends Base
{

//    private $guard;//当前使用的guard
//
//    private $provider;//当前使用的provider

    private $jwt_ttl;//token有效期

    public function __construct($provider)
    {
        parent::__construct();
        $this->jwt_ttl  = !isset($provider['ttl']) ? $this->get_ttl() : $provider['ttl'] * 60 * 60;

        //        $this->redis_key_token = $this->redis_token_prefix.$this->guard['provider'].'_';
    }

    /**
     * base64 json转码
     *
     * @param $data
     *
     * @return mixed
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function base64_json_encode($data)
    {
        $result = str_replace('=', '', base64_encode(json_encode($data)));
        return $result;
    }

    /**
     * jwt_header
     *
     * @return array
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function jwt_header()
    {
        return [
          'typ' => 'hashyoo-jwt-auth',
          'alg' => $this->config['algo'],
        ];
    }

    /**
     * 声明
     *
     * @param int $n_user_id
     *
     * @return array
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function claim($n_user_id = 0)
    {
        $now_time        = time();
        $ttl_time        = $this->jwt_ttl;
        $arr_data        = [];
        $arr_data['iat'] = $now_time;
        $arr_data['exp'] = $now_time + $ttl_time;
        $arr_data['lft'] = $ttl_time;
        $arr_data['sub'] = $n_user_id;
        return $arr_data;
    }

    /**
     * payload - header
     *
     * @return mixed
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function payload_header()
    {
        $payload_header = $this->base64_json_encode($this->jwt_header());
        return $payload_header;
    }

    /**
     * payload - claim
     *
     * @param int $n_user_id
     *
     * @return mixed
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function payload_claim($n_user_id = 0)
    {
        $payload_claim = $this->base64_json_encode($this->claim($n_user_id));
        return $payload_claim;
    }

    /**
     * payload
     *
     * @param int $n_user_id
     *
     * @return string
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function get_payload($n_user_id = 0)
    {
        //playload-header
        $payload_header = $this->payload_header();

        //playload-claim
        $payload_claim = $this->payload_claim($n_user_id);

        $payload = $payload_header . '.' . $payload_claim;
        return $payload;
    }


}