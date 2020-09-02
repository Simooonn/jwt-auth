<?php
/**
 * Created by PhpStorm.
 * User: wumengmeng <wu_mengmeng@foxmail.com>
 * Date: 2020/6/30 0030
 * Time: 18:00
 */

namespace HashyooJWTAuth\JWT;

class Model
{

    private $provider;//当前使用的provider

    private $provider_driver;//当前使用的provider驱动

    private $password_key;//密码字段

    private $model;//数据model

    public function __construct($provider)
    {
        $this->provider        = $provider;
        $this->provider_driver = $provider['driver'];
        $this->model           = $this->provider_model();
        $this->password_key    = $provider['pass_key'] ? $provider['pass_key'] : 'password';
    }

    /**
     * provider驱动model
     *
     * @param $provider
     *
     * @return mixed
     * @throws \Exception
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    private function provider_model()
    {
        $provider        = $this->provider;
        $provider_driver = $this->provider_driver;
        if (is_null($provider_driver)) {
            throw new \Exception('找不到provider的driver');
        }
        switch ($provider_driver) {
            case 'eloquent':
                $provider_model = $provider['model'];
                if (is_null($provider_model)) {
                    throw new \Exception('找不到provider的model');
                }
                $model = new $provider_model;
                break;
            default:
                throw new \Exception('没有默认的provider驱动');
        }

        return $model;
    }

    /**
     * 根据where条件查询一条数据
     *
     * @param array $arr_where
     *
     * @return null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function get_one($arr_where = [])
    {
        $provider_driver = $this->provider_driver;
        $provider_model  = $this->model;
        switch ($provider_driver) {
            case 'eloquent':
                //yoo_array_remove($login_data, [$password])
                //                $s_password = $user->password;
                $user = $provider_model->where($arr_where)
                                       ->first();
                if (is_null($user)) {
                    return null;
                }
                $s_password                = $user->password;
                $user                      = $user->toarray();
                $user[$this->password_key] = $s_password;
                break;
            default:
                return null;

        }
        return $user;
    }

    /**
     * 根据数据id查询一条数据
     *
     * @param int $n_id
     *
     * @return null
     * @author wumengmeng <wu_mengmeng@foxmail.com>
     */
    public function find($n_id = 0)
    {
        $provider_driver = $this->provider_driver;
        $provider_model  = $this->model;
        switch ($provider_driver) {
            case 'eloquent':
                //yoo_array_remove($login_data, [$password])
                //                $s_password = $user->password;
                $user = $provider_model->find($n_id);
                if (is_null($user)) {
                    return null;
                }
                $user = $user->toarray();
                break;
            default:
                return null;

        }
        return $user;
    }


}