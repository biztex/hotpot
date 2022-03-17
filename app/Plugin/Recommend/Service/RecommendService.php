<?php
/*
 * This file is part of the Recommend Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Recommend\Service;

use Eccube\Common\Constant;
use Plugin\Recommend\Entity\RecommendProduct;

/**
 * Class RecommendService.
 */
class RecommendService
{
    /** @var \Eccube\Application */
    public $app;

    /**
     * コンストラクタ
     *
     * @param \Eccube\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * おすすめ商品情報を新規登録する.
     *
     * @param array $data
     *
     * @return bool
     */
    public function createRecommend($data)
    {
        // おすすめ商品詳細情報を生成する
        $Recommend = $this->newRecommend($data);

        return $this->app['eccube.plugin.recommend.repository.recommend_product']->saveRecommend($Recommend);
    }

    /**
     * おすすめ商品情報を更新する.
     *
     * @param array $data
     *
     * @return bool
     */
    public function updateRecommend($data)
    {
        // おすすめ商品情報を取得する
        $Recommend = $this->app['eccube.plugin.recommend.repository.recommend_product']->find($data['id']);
        if (!$Recommend) {
            return false;
        }

        // おすすめ商品情報を書き換える
        $Recommend->setComment($data['comment']);
        $Recommend->setProduct($data['Product']);

        // おすすめ商品情報を更新する
        return $this->app['eccube.plugin.recommend.repository.recommend_product']->saveRecommend($Recommend);
    }

    /**
     * おすすめ商品情報を削除する.
     *
     * @param int $recommendId
     *
     * @return bool
     */
    public function deleteRecommend($recommendId)
    {
        // おすすめ商品情報を取得する
        $Recommend = $this->app['eccube.plugin.recommend.repository.recommend_product']->find($recommendId);
        if (!$Recommend) {
            return false;
        }
        // おすすめ商品情報を書き換える
        $Recommend->setDelFlg(Constant::ENABLED);

        // おすすめ商品情報を登録する
        return $this->app['eccube.plugin.recommend.repository.recommend_product']->saveRecommend($Recommend);
    }

    /**
     * おすすめ商品情報を生成する.
     *
     * @param array $data
     *
     * @return RecommendProduct
     */
    protected function newRecommend($data)
    {
        $rank = $this->app['eccube.plugin.recommend.repository.recommend_product']->getMaxRank();

        $Recommend = new RecommendProduct();
        $Recommend->setComment($data['comment']);
        $Recommend->setProduct($data['Product']);
        $Recommend->setRank(($rank ? $rank : 0) + 1);
        $Recommend->setDelFlg(Constant::DISABLED);

        return $Recommend;
    }
}
