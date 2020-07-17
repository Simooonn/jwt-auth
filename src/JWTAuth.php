<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JWTAuth extends JWT
{

    private $defaults;

    private $guards;

    private $providers;

    private $model;

    private $provider_driver;

    private $user;

    //    protected $config;

    /**
     * JWT constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaults  = $this->config['defaults'];
        $this->guards    = $this->config['guards'];
        $this->providers = $this->config['providers'];

    }

    /**
     * 设置token
     *
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function set_token()
    {
        $new_jwt = new Token();
        $new_jwt->set_user($this->user);
        $token                      = $new_jwt->create_token();

        //redis存放 token
        $this->redis_set_token($token);
    }

    /**
     * 验证凭证
     *
     * @param array $credentials
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function by_credentials($credentials = [])
    {
        $password = $this->config['password_key'] ? $this->config['password_key'] : 'password';
        if (array_key_exists($password, $credentials)) {
            $result = $this->check_password($credentials, $password);
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * 验证密码
     *
     * @param $credentials
     * @param $password
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function check_password($credentials, $password)
    {
        $provider_driver = $this->provider_driver;
        $s_pass          = $credentials[$password];

        //查询账号数据
        switch ($provider_driver) {
            case 'eloquent':
                $user       = $this->model->where(yoo_array_remove($credentials, [$password]))
                                          ->first();
                $s_password = $user->password;
                if (is_null($user)) {
                    return false;
                }
                break;
            default:
                return false;

        }

        //验证密码
        if (Hash::check($s_pass, $s_password) !== true) {
            return false;
        }

        //设置token
        $this->user = $user->toarray();
        $this->set_token();
        return true;
    }


    /**
     * guard
     *
     * @param string $module
     *
     * @return $this|bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function guard($module = '')
    {
        $module   = $module === '' ? $this->defaults['guard'] : $module;
        $provider = $this->providers[$this->guards[$module]['provider']];
        if (is_null($provider)) {
            return false;
        }

        $provider_driver       = $provider['driver'];
        $provider_model        = $provider['model'];
        $this->provider_driver = $provider_driver;

        switch ($provider_driver) {
            case 'eloquent':
                $this->model = new $provider_model;
                return $this;
                break;
            default:
                return $this;


        }
    }

    /**
     * 尝试登录
     *
     * @param array $credentials
     *
     * @return bool
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function attempt(array $credentials)
    {
        if ($this->by_credentials($credentials) !== true) {
            return false;
        }

        return $this->user;
    }

    public function redis_set_token($token = ''){
        $arr_token = explode('.',$token);
        $arr_claim = json_decode(base64_decode($arr_token[1]),true);
        $n_lifetime = $arr_claim['lft'];
        $n_userid = $arr_claim['sub'];

        $arr_jwt_token = [
          'id'=>$n_userid,
          'access_token'=>$token,
          'token_type'=>'bearer',
          'expires_in'=>$n_lifetime,
        ];
        predis_str_set('jwt_token_user_'.$n_userid,$arr_jwt_token,$n_lifetime,15);

    }

    public function redis_set_user(){

    }


}