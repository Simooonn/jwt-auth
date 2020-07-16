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
    
    
    
    private function base64_encode($data){
        return base64_encode($data);
    }
    
    private function jwt_header(){
        return [
          'typ'=>'hashyoo-jwt-auth',
          'alg'=>config('hashyoo-jwt.algo')
        ];
    }


}