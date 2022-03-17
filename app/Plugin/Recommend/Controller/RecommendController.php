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

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Plugin\Recommend\Entity\RecommendProduct;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class RecommendController.
 */
class RecommendController extends AbstractController
{
    /**
     * おすすめ商品一覧.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function index(Application $app, Request $request)
    {
        $pagination = $app['eccube.plugin.recommend.repository.recommend_product']->getRecommendList();

        return $app->render('Recommend/Resource/template/admin/index.twig', array(
            'pagination' => $pagination,
            'total_item_count' => count($pagination),
        ));
    }

    /**
     * Create & Edit.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Application $app, Request $request, $id = null)
    {
        /* @var RecommendProduct $Recommend */
        $Recommend = null;
        $Product = null;
        if (!is_null($id)) {
            // IDからおすすめ商品情報を取得する
            $Recommend = $app['eccube.plugin.recommend.repository.recommend_product']->find($id);

            if (!$Recommend) {
                $app->addError('admin.recommend.not_found', 'admin');
                log_info('The recommend product is not found.', array('Recommend id' => $id));

                return $app->redirect($app->url('admin_recommend_list'));
            }

            $Product = $Recommend->getProduct();
        }

        // formの作成
        /* @var Form $form */
        $form = $app['form.factory']
            ->createBuilder('admin_recommend', $Recommend)
            ->getForm();

        $form->handleRequest($request);
        $data = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            $service = $app['eccube.plugin.recommend.service.recommend'];
            if (is_null($data['id'])) {
                if ($status = $service->createRecommend($data)) {
                    $app->addSuccess('admin.plugin.recommend.register.success', 'admin');
                    log_info('Add the new recommend product success.', array('Product id' => $data['Product']->getId()));
                }
            } else {
                if ($status = $service->updateRecommend($data)) {
                    $app->addSuccess('admin.plugin.recommend.update.success', 'admin');
                    log_info('Update the recommend product success.', array('Recommend id' => $Recommend->getId(), 'Product id' => $data['Product']->getId()));
                }
            }

            if (!$status) {
                $app->addError('admin.recommend.not_found', 'admin');
                log_info('Failed the recommend product updating.', array('Product id' => $data['Product']->getId()));
            }

            return $app->redirect($app->url('admin_recommend_list'));
        }

        if (!empty($data['Product'])) {
            $Product = $data['Product'];
        }

        $arrProductIdByRecommend = $app['eccube.plugin.recommend.repository.recommend_product']->getRecommendProductIdAll();

        return $this->registerView(
            $app,
            array(
                'form' => $form->createView(),
                'recommend_products' => json_encode($arrProductIdByRecommend),
                'Product' => $Product,
            )
        );
    }

    /**
     * おすすめ商品の削除.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     *
     * @throws BadRequestHttpException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Application $app, Request $request, $id)
    {
        // Valid token
        $this->isTokenValid($app);

        // Check request
        if (!'POST' === $request->getMethod()) {
            log_error('Delete with bad method!');
            throw new BadRequestHttpException();
        }

        // Id valid
        if (!$id) {
            $app->addError('admin.recommend.recommend_id.not_exists', 'admin');

            return $app->redirect($app->url('admin_recommend_list'));
        }

        $service = $app['eccube.plugin.recommend.service.recommend'];

        // おすすめ商品情報を削除する
        if ($service->deleteRecommend($id)) {
            log_info('The recommend product delete success!', array('Recommend id' => $id));
            $app->addSuccess('admin.plugin.recommend.delete.success', 'admin');
        } else {
            $app->addError('admin.recommend.not_found', 'admin');
            log_info('The recommend product is not found.', array('Recommend id' => $id));
        }

        return $app->redirect($app->url('admin_recommend_list'));
    }

    /**
     * Move rank with ajax.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return bool
     */
    public function moveRank(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $arrRank = $request->request->all();
            $arrRankMoved = $app['eccube.plugin.recommend.repository.recommend_product']->moveRecommendRank($arrRank);
            log_info('Recommend move rank', $arrRankMoved);
        }

        return true;
    }

    /**
     * 編集画面用のrender.
     *
     * @param Application $app
     * @param array       $parameters
     *
     * @return Response
     */
    protected function registerView($app, $parameters = array())
    {
        // 商品検索フォーム
        $searchProductModalForm = $app['form.factory']->createBuilder('admin_search_product')->getForm();
        $viewParameters = array(
            'searchProductModalForm' => $searchProductModalForm->createView(),
        );
        $viewParameters += $parameters;

        return $app->render('Recommend/Resource/template/admin/regist.twig', $viewParameters);
    }
}
