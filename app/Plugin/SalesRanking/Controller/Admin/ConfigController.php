<?php
/*
* Plugin Name : SalesRanking
*
* Copyright (C) 2015 BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\SalesRanking\Controller\Admin;

use Eccube\Application;
use Eccube\Common\Constant;
use Symfony\Component\HttpFoundation\Request;

class ConfigController
{
    public function index(Application $app, Request $request)
    {
        $form = $app['form.factory']
            ->createBuilder('admin_setting_sales_ranking')
            ->getForm();
        
        $Configs = $app['eccube.salesranking.repository.config']->findAll();
        
        foreach($Configs as $config){
            if(is_null($config->getValue()) || is_array($config->getValue()))continue;
            $form[$config->getName()]->setData($config->getValue());
        }
        
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                //設定内容を一度クリア
                foreach($Configs as $config){
                    $app['orm.em']->remove($config);
                }
                $app['orm.em']->flush();

                //設定登録
                $Values = $form->getData();
                foreach($Values as $name => $value){
                    $Config = new \Plugin\SalesRanking\Entity\Config();
                    $Config->setName($name);
                    $Config->setValue($value);
                    $app['orm.em']->persist($Config);
                }
                $app['orm.em']->flush();
                $app->addSuccess('admin.setting.sales_ranking.save.complete', 'admin');
            }
        }
        
        return $app->render('SalesRanking/Resource/template/admin/Setting/config.twig', array(
            'form' => $form->createView(),
        ));
    }
}