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
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;

/**
 * Class RecommendAdminControllerTest.
 */
class RecommendAdminControllerTest extends AbstractAdminWebTestCase
{
    protected $Recommend1;
    protected $Recommend2;
    /**
     * please ensure have 1 or more order in database before testing.
     */
    public function setUp()
    {
        parent::setUp();
        $this->deleteAllRows(array('plg_recommend_product'));
        // recommend for product 1 with rank 1
        $this->Recommend1 = $this->initRecommendData(1, 1);
        // recommend for product 2 with rank 2
        $this->Recommend2 = $this->initRecommendData(2, 2);
    }

    /**
     * testRecommendList
     * none recommend.
     */
    public function testRecommendListEmpty()
    {
        $this->deleteAllRows(array('plg_recommend_product'));
        $crawler = $this->client->request('GET', $this->app->url('admin_recommend_list'));
        $this->assertContains('0 件', $crawler->html());
    }

    /**
     * testRecommendList
     * none recommend.
     */
    public function testRecommendList105()
    {
        $this->deleteAllRows(array('plg_recommend_product'));
        for ($i = 1; $i < 106; ++$i) {
            $Product = $this->createProduct();
            $this->initRecommendData($Product->getId(), $i);
        }

        $crawler = $this->client->request('GET', $this->app->url('admin_recommend_list'));
        $this->assertContains('105 件', $crawler->html());
    }

    /**
     * testRecommendCreate.
     */
    public function testRecommendCreate()
    {
        $crawler = $this->client->request('GET', $this->app->url('admin_recommend_new'));
        $this->assertContains('おすすめ商品管理', $crawler->html());
    }

    /**
     * testRecommendNew.
     */
    public function testRecommendNewEmpty()
    {
        $this->deleteAllRows(array('plg_recommend_product'));

        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_new'),
            array('admin_recommend' => array(
                '_token' => 'dummy',
                'comment' => '',
                'Product' => '',
            ),
            )
        );

        $this->assertContains('入力されていません。', $crawler->html());
        $this->assertContains('商品を追加してください。', $crawler->html());
    }

    /**
     * testRecommendNew.
     */
    public function testRecommendNewProduct()
    {
        $this->deleteAllRows(array('plg_recommend_product'));
        $productId = 1;
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_new'),
            array('admin_recommend' => array(
                '_token' => 'dummy',
                'comment' => '',
                'Product' => $productId,
            ),
            )
        );

        $this->assertContains('入力されていません。', $crawler->html());
    }

    /**
     * testRecommendNew.
     */
    public function testRecommendNewComment()
    {
        $this->deleteAllRows(array('plg_recommend_product'));
        $fake = $this->getFaker();
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_new'),
            array('admin_recommend' => array(
                '_token' => 'dummy',
                'comment' => $fake->word,
                'Product' => '',
            ),
            )
        );

        $this->assertContains('商品を追加してください。', $crawler->html());
    }

    /**
     * testRecommendNewComment4002.
     */
    public function testRecommendNewCommentOver()
    {
        $this->deleteAllRows(array('plg_recommend_product'));
        $fake = $this->getFaker();
        $productId = 1;
        $editMessage = $fake->text(99999);
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_new'),
            array('admin_recommend' => array(
                '_token' => 'dummy',
                'comment' => $editMessage,
                'Product' => $productId,
            ),
            )
        );

        $this->assertContains('値が長すぎます。4000文字以内でなければなりません。', $crawler->html());
    }

    /**
     * testRecommendNew.
     */
    public function testRecommendNew()
    {
        $this->deleteAllRows(array('plg_recommend_product'));
        $fake = $this->getFaker();
        $productId = 1;
        $editMessage = $fake->word;
        $this->client->request(
            'POST',
            $this->app->url('admin_recommend_new'),
            array('admin_recommend' => array(
                '_token' => 'dummy',
                'comment' => $editMessage,
                'Product' => $productId,
            ),
            )
        );

        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_recommend_list')));
        $ProductNew = $this->getRecommend($productId);

        $this->expected = $editMessage;
        $this->actual = $ProductNew->getComment();
        $this->verify();
    }

    /**
     * RecommendSearchModelController.
     */
    public function testAjaxSearchProductEmpty()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_search_product', array('id' => '', 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('ディナーフォーク', $productList);
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * RecommendSearchModelController.
     */
    public function testAjaxSearchPublicProduct()
    {
        $Disp = $this->app['orm.em']->getRepository('Eccube\Entity\Master\Disp')->find(1);
        $Product = $this->app['eccube.repository.product']->findOneBy(array('name' => 'ディナーフォーク'));
        $Product->setStatus($Disp);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush($Product);

        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_search_product', array('id' => '', 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('ディナーフォーク', $productList);
    }

    /**
     * RecommendSearchModelController.
     */
    public function testAjaxSearchUnpublicProduct()
    {
        $Disp = $this->app['orm.em']->getRepository('Eccube\Entity\Master\Disp')->find(2);
        $Product = $this->app['eccube.repository.product']->findOneBy(array('name' => 'ディナーフォーク'));
        $Product->setStatus($Disp);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush($Product);

        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_search_product', array('id' => '', 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('ディナーフォーク', $productList);
    }

    /**
     * RecommendSearchModelController.
     */
    public function testAjaxSearchProductValue()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_search_product', array('id' => '', 'category_id' => 1, '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * RecommendSearchModelController.
     */
    public function testAjaxSearchProductValueCode()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_search_product', array('id' => 'cafe-01', 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * RecommendSearchModelController.
     */
    public function testAjaxSearchProductValueId()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_search_product', array('id' => 1, 'category_id' => '', '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * RecommendSearchModelController.
     */
    public function testAjaxSearchProductCategory()
    {
        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_search_product', array('id' => '', 'category_id' => 6, '_token' => 'dummy')),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $productList = $crawler->html();
        $this->assertContains('ディナーフォーク', $productList);
        $this->assertContains('パーコレーター', $productList);
    }

    /**
     * testRecommendEditShow.
     */
    public function testRecommendEditShow()
    {
        $recommendId = $this->Recommend2->getId();

        $crawler = $this->client->request('GET', $this->app->url('admin_recommend_edit', array('id' => $recommendId)));

        $this->assertContains($this->Recommend2->getProduct()->getName(), $crawler->html());
    }
    /**
     * testRecommendEdit.
     */
    public function testRecommendEdit()
    {
        $fake = $this->getFaker();
        $productId = 2;
        $recommendId = $this->Recommend2->getId();
        $editMessage = $fake->word;

        $this->client->request(
            'POST',
            $this->app->url('admin_recommend_edit', array('id' => $recommendId)),
            array(
                'admin_recommend' => array(
                    '_token' => 'dummy',
                    'comment' => $editMessage,
                    'id' => $recommendId,
                    'Product' => $productId,
                ),
            )
        );
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_recommend_list')));
        $ProductNew = $this->getRecommend($productId);

        $this->expected = $editMessage;
        $this->actual = $ProductNew->getComment();
        $this->verify();
    }

    /**
     * testRecommendEditExit
     * change from product 2 to product 1.
     */
    public function testRecommendEditExist()
    {
        $fake = $this->getFaker();
        $productId = 1;
        //recommend of product 2
        $recommendId = $this->Recommend2->getId();
        $editMessage = $fake->word;

        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_recommend_edit', array('id' => $recommendId)),
            array(
                'admin_recommend' => array(
                    '_token' => 'dummy',
                    'comment' => $editMessage,
                    'id' => $recommendId,
                    'Product' => $productId,
                ),
            )
        );
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_recommend_list')));
    }

    /**
     * testRecommendDelete.
     */
    public function testRecommendDelete()
    {
        $productId = $this->Recommend1->getId();
        $this->client->request(
            'DELETE',
            $this->app->url('admin_recommend_delete', array('id' => $productId))
        );
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_recommend_list')));
        $ProductNew = $this->app['eccube.plugin.recommend.repository.recommend_product']->find($productId);

        $this->expected = 1;
        $this->actual = $ProductNew->getDelFlg();
        $this->verify();
    }

    /**
     * @param $productId
     *
     * @return mixed
     */
    private function getRecommend($productId)
    {
        $Product = $this->app['eccube.repository.product']->find($productId);

        return $this->app['eccube.plugin.recommend.repository.recommend_product']->findOneBy(array('Product' => $Product));
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
