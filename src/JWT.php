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

    protected function __construct()
    {
        $this->config = config('hashyoo-jwt');
    }
    
    public function get_ttl(){
        $ttl_time        = $this->config['ttl'] * 60 * 60;
        return $ttl_time;
    }
    
  


}