<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth;

class JWTAuth extends JWT
{
    private $defaults;
    private $guards;
    private $providers;
    private $model;
    private $provider_driver;
//    protected $config;

    /**
     * JWT constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaults = $this->config['defaults'];
        $this->guards = $this->config['guards'];
        $this->providers = $this->config['providers'];

    }

    private function login_in($credentials){
        $password = $this->config['password_key'] ? $this->config['password_key']:'password';
        if(array_key_exists($password,$credentials)){
            $user = $this->model->where(yoo_array_remove($credentials,[$password]))->first();
            if(is_null($user)){
                return false;
            }
            else{

            }

            dd($user->toarray());
            
        }
        else{
            return false;
        }
    }

    
    public function guard($module = ''){
        $module = $module===''? $this->defaults['guard']:$module;
        $provider = $this->providers[$this->guards[$module]['provider']];
        if(is_null($provider)){
            return false;
        }
        
        $provider_driver = $provider['driver'];
        $provider_model = $provider['model'];
        $this->provider_driver = $provider_driver;

        switch ($provider_driver)
        {
            case 'eloquent':
                $this->model = new $provider_model;
                return $this;
                break;
            default:
                return $this;


        }
    }

    public function attempt(array $credentials){
        if (!$this->login_in($credentials)) {
            return false;
        }
    }


}