<?php
/*
* Plugin Name : ContactProduct
*
* Copyright (C) 2015 BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\ContactProduct\ServiceProvider;

use Eccube\Application;
use Eccube\Common\Constant;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class ContactProductServiceProvider implements ServiceProviderInterface
{

    private $app;

    public function register(BaseApplication $app)
    {
        $this->app = $app;

        // Form/Extension
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new \Plugin\ContactProduct\Form\Extension\ContactExtension($app);
            return $extensions;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}