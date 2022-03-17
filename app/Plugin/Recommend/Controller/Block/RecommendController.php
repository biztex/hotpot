<?php
/*
 * This file is part of the Recommend Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Recommend\Controller\Block;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Eccube\Entity\Master\Disp;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RecommendController.
 */
class RecommendController
{
    /**
     * Load block.
     *
     * @param Application $app
     *
     * @return Response
     */
    public function index(Application $app)
    {
        $Disp = $app['eccube.repository.master.disp']->find(Disp::DISPLAY_SHOW);

        /**
         * @var ArrayCollection
         */
        $arrRecommendProduct = $app['eccube.plugin.recommend.repository.recommend_product']->getRecommendProduct($Disp);

        return $app->render('Block/recommend_product_block.twig', array(
            'recommend_products' => $arrRecommendProduct,
        ));
    }
}
