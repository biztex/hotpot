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

namespace Plugin\SalesRanking\Controller;

use Eccube\Application;
use Eccube\Common\Constant;
use Symfony\Component\HttpFoundation\Request;

class SalesRankingController
{
    private $app;

    public function index(Application $app)
    {
        $this->app = $app;

        $BaseInfo = $app['eccube.repository.base_info']->get();

        // Doctrine SQLFilter
        if ($BaseInfo->getNostockHidden() === Constant::ENABLED) {
            $app['orm.em']->getFilters()->enable('nostock_hidden');
        }

        $term = $this->getConfig('term');
        $num = $this->getConfig('display_num');
        if(!is_numeric($num))$num = 0;
        $num = intval($num);

        $excludes = array(
            $app['config']['order_processing'],
            $app['config']['order_cancel'],
            $app['config']['order_pending'],
        );

        $qb = $app['orm.em']->createQueryBuilder();
        $qb
            ->select('SUM(od.price * od.quantity) as total,p.id')
            ->from('Eccube\Entity\OrderDetail', 'od')
            ->leftJoin('Eccube\Entity\Product', 'p', 'WITH', 'p = od.Product')
            ->innerJoin('Eccube\Entity\ProductClass', 'pc', 'WITH', 'pc.Product = p')
            ->leftJoin('Eccube\Entity\Order', 'o', 'WITH', 'od.Order = o')
            ->andWhere('o.del_flg = 0')
            ->andWhere('o.OrderStatus NOT IN (:excludes)')
            ->andWhere('p.del_flg = 0')
            ->andWhere('p.Status = 1')
            ->setParameter(':excludes', $excludes)
            ->orderBy('total', 'DESC')
            ->groupBy('p.id')
            ->setMaxResults($num);

        if($term != '4'){
            $Date = new \DateTime();
            $end = $Date->format("Y-m-d 23:59:59");
            if($term == '1'){
                $prev = "- 2 week";
            }elseif($term == '2'){
                $prev = "- 1 month";
            }elseif($term == '3'){
                $prev = "- 1 year";
            }else{
                $prev = "- 1 week";
            }
            $start = $Date->modify($prev)->format("Y-m-d 00:00:00");

            $qb
                ->andWhere('o.create_date >= :start')
                ->andWhere('o.create_date <= :end')
                ->setParameter(':start', $start)
                ->setParameter(':end', $end);
        }

        $result = array();
        try {
            $result = $qb->getQuery()->getResult();
        } catch (NoResultException $e) {

        }

        $ItemList = array();
        foreach ($result as $item) {
            $ItemList[] = $app['eccube.repository.product']->find($item['id']);
        }


        return $app->render('Block/sales_ranking.twig', array(
                    'ItemList' => $ItemList,
        ));
    }

    private function getConfig($name)
    {
        $ret = $this->app['eccube.salesranking.repository.config']->findOneBy(array('name' => $name));
        if($ret)return $ret->getValue();
    }

}
