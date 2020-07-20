<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class Payload extends JWT
{

    private $user_id;

    /**
     * JWT constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function base64_json_encode($data)
    {
        $result = str_replace('=', '', base64_encode(json_encode($data)));
        return $result;
    }

    public function jwt_header()
    {
        return [
          'typ' => 'hashyoo-jwt-auth',
          'alg' => $this->config['algo'],
        ];
    }

    public function set_user($arr_user = [])
    {
        $this->user = $arr_user;

    }

    /**
     * 声明
     *
     * @param int $n_user_id
     *
     * @return array
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function claim()
    {
        $n_user_id = $this->user['id'];

        $now_time        = time();
        $ttl_time        = $this->get_ttl();
        $arr_data        = [];
        $arr_data['iat'] = $now_time;
        $arr_data['exp'] = $now_time + $ttl_time;
        $arr_data['lft'] = $ttl_time;
        $arr_data['sub'] = $n_user_id;
        return $arr_data;
    }

    public function payload_header()
    {
        $payload_header = $this->base64_json_encode($this->jwt_header());
        return $payload_header;
    }

    public function payload_claim()
    {
        $payload_claim = $this->base64_json_encode($this->claim());
        return $payload_claim;


    }


}