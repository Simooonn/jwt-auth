<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth\JWT;

use Illuminate\Support\Facades\Hash;

class Guard extends Base
{

    private $defaults_guard;//

    private $guards;//

    private $providers;//

    private $guard;//

    private $provider;//

    public function __construct($module = '')
    {
        parent::__construct();

        $config               = $this->config;
        $this->defaults_guard = $config['defaults']['guard'];
        $this->guards         = $config['guards'];
        $this->providers      = $config['providers'];

        /*   if (is_null($this->defaults_guard)) {
               throw new \Exception('没有设置默认的guard');
           }*/

        /*设置guard和provider*/
        $module   = empty($module) ? $this->defaults_guard : $module;
        $guard    = $this->guards[$module];
        $provider = $this->providers[$guard['provider']];
        /*   if (is_null($guard)) {
               throw new \Exception('没有找到对应的guard');
           }
           if (is_null($provider)) {
               throw new \Exception('没有找到对应的provider');
           }*/

        $this->guard                = $guard;
        $this->provider             = $provider;
        $this->provider['pass_key'] = !empty($this->provider['pass_key']) ? $this->provider['pass_key'] : 'password';
    }

    public function get_password_key()
    {
        return $this->provider['pass_key'];
    }

    public function get_provider()
    {
        return $this->provider;
    }

    public function get_guard()
    {
        return $this->guard;

    }


}