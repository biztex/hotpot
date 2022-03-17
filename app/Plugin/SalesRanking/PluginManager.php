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

namespace Plugin\SalesRanking;

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Entity\Master\DeviceType;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{

    public function install($config, $app)
    {   
        $this->migrationSchema($app, __DIR__ . '/Migration', $config['code']);
        $file = new Filesystem();
        try {
            $file->copy($app['config']['root_dir']. '/app/Plugin/SalesRanking/Resource/template/Block/sales_ranking.twig', $app['config']['template_realdir']. '/Block/sales_ranking.twig', true);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Migration', $config['code'], 0);
        unlink($app['config']['template_realdir']. '/Block/sales_ranking.twig');
    }

    public function enable($config, $app)
    {
        $Block = new \Eccube\Entity\Block();
        $Block->setFileName('sales_ranking');
        $Block->setName('売上ランキング');
        $Block->setLogicFlg(1);
        $Block->setDeletableFlg(0);
        $DeviceType = $app['eccube.repository.master.device_type']
            ->find(DeviceType::DEVICE_TYPE_PC);
        $Block->setDeviceType($DeviceType);
        $app['orm.em']->persist($Block);
        $app['orm.em']->flush();
    }

    public function disable($config, $app)
    {
        $Block = $app['eccube.repository.block']->findOneBy(array('file_name' => 'sales_ranking'));
        if($Block){
             $BlockPositions = $app['orm.em']
            ->getRepository('Eccube\Entity\BlockPosition')
            ->findBy(array('Block' => $Block));
            foreach($BlockPositions as $BlockPosition){
                $app['orm.em']->remove($BlockPosition);
            }
            $app['orm.em']->remove($Block);
            $app['orm.em']->flush();
        }
    }

    public function update($config, $app)
    {
    }
}
