<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth\JWT;

class Sign extends Base
{

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 算法加密
     *
     * @param string $sting
     *
     * @return string
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function signature($sting = '')
    {
        $alg    = strtolower($this->config['algo']);
        $secret = $this->config['secret'];

        switch ($alg) {
            case 'hs256':
                $result = hash_hmac('sha256', $sting, $secret, true);
                break;
            default:
                $result = $sting;
        }
        $result = str_replace('=', '', base64_encode($result));
        return $result;
    }


}