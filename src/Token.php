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
        return $token;
    }


}