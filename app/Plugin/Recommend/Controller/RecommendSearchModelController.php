<?php
/*
 * This file is part of the Recommend Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Recommend\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RecommendSearchModelController.
 */
class RecommendSearchModelController
{
    /**
     * 商品検索画面を表示する.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $page_no
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function searchProduct(Application $app, Request $request, $page_no = null)
    {
        if (!$request->isXmlHttpRequest()) {
            return null;
        }

        log_debug('Search product start.');

        $pageCount = $app['config']['default_page_count'];
        $session = $app['session'];
        if ('POST' === $request->getMethod()) {
            $page_no = 1;
            $searchData = array(
                'name' => trim($request->get('id')),
            );

            if ($categoryId = $request->get('category_id')) {
                    $searchData['category_id'] = $categoryId;
            }

            $session->set('eccube.plugin.recommend.product.search', $searchData);
            $session->set('eccube.plugin.recommend.product.search.page_no', $page_no);
        } else {
            $searchData = (array) $session->get('eccube.plugin.recommend.product.search');
            if (is_null($page_no)) {
                $page_no = intval($session->get('eccube.plugin.recommend.product.search.page_no'));
            } else {
                $session->set('eccube.plugin.recommend.product.search.page_no', $page_no);
            }
        }

        //set parameter
        $searchData['id'] = $searchData['name'];

        if (!empty($searchData['category_id'])) {
            $searchData['category_id'] = $app['eccube.repository.category']->find($searchData['category_id']);
        }

        $qb = $app['eccube.repository.product']->getQueryBuilderBySearchDataForAdmin($searchData);

        /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
        $pagination = $app['paginator']()->paginate(
            $qb,
            $page_no,
            $pageCount,
            array('wrap-queries' => true)
        );
        /** @var ArrayCollection */
        $arrProduct = $pagination->getItems();

        log_debug('Search product finish.');
        if (count($arrProduct) == 0) {
            log_debug('Search product not found.');
        }

        return $app->render('Recommend/Resource/template/admin/search_product.twig', array(
            'pagination' => $pagination,
        ));
    }
}
