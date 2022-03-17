<?php
/*
 * This file is part of the Recommend plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Recommend\Tests\Web;

use Eccube\Common\Constant;
use Eccube\Tests\Web\AbstractWebTestCase;

/**
 * Class RecommendControllerTest.
 */
class RecommendControllerTest extends AbstractWebTestCase
{
    /**
     * setUp.
     */
    public function setUp()
    {
        parent::setUp();

        // recommend for product 1 with rank 1
        $this->initRecommendData(1, 1);
        // recommend for product 2 with rank 2
        $this->initRecommendData(2, 2);
    }

    /**
     * Block.RecommendController.
     */
    public function testRecommendBlock()
    {
        $crawler = $this->client->request(
            'GET',
            $this->app->url('block_recommend_product_block')
        );

        $this->assertContains('<div id="item_list">', $crawler->html());
    }
    /**
     * @param $productId
     * @param $rank
     *
     * @return \Plugin\Recommend\Entity\RecommendProduct
     */
    private function initRecommendData($productId, $rank)
    {
        $dateTime = new \DateTime();
        $fake = $this->getFaker();

        $Recommend = new \Plugin\Recommend\Entity\RecommendProduct();
        $Recommend->setComment($fake->word);
        $Recommend->setProduct($this->app['eccube.repository.product']->find($productId));
        $Recommend->setRank($rank);
        $Recommend->setDelFlg(Constant::DISABLED);
        $Recommend->setCreateDate($dateTime);
        $Recommend->setUpdateDate($dateTime);
        $this->app['orm.em']->persist($Recommend);
        $this->app['orm.em']->flush();

        return $Recommend;
    }
}
