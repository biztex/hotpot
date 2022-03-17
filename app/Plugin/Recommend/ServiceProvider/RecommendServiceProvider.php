<?php
/*
 * This file is part of the Recommend Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Recommend\ServiceProvider;

use Plugin\Recommend\Form\Type\RecommendProductType;
use Plugin\Recommend\Service\RecommendService;
use Plugin\Recommend\Utils\Version;
use Silex\Application as BaseApplication;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Yaml\Yaml;

// include log functions (for 3.0.0 - 3.0.11)
require_once(__DIR__.'/../log.php');

/**
 * Class RecommendServiceProvider.
 */
class RecommendServiceProvider implements ServiceProviderInterface
{
    /**
     * @param BaseApplication $app
     */
    public function register(BaseApplication $app)
    {
        // おすすめ情報テーブルリポジトリ
        $app['eccube.plugin.recommend.repository.recommend_product'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Recommend\Entity\RecommendProduct');
        });

        // おすすめ商品の一覧
        $app->match('/'.$app['config']['admin_route'].'/recommend', '\Plugin\Recommend\Controller\RecommendController::index')
            ->bind('admin_recommend_list');

        // おすすめ商品の新規先
        $app->match('/'.$app['config']['admin_route'].'/recommend/new', '\Plugin\Recommend\Controller\RecommendController::edit')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_recommend_new');

        // おすすめ商品の編集
        $app->match('/'.$app['config']['admin_route'].'/recommend/{id}/edit', '\Plugin\Recommend\Controller\RecommendController::edit')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_recommend_edit');

        // おすすめ商品の削除
        $app->delete('/'.$app['config']['admin_route'].'/recommend/{id}/delete', '\Plugin\Recommend\Controller\RecommendController::delete')
        ->value('id', null)->assert('id', '\d+|')
        ->bind('admin_recommend_delete');

        // move rank
        $app->post('/'.$app['config']['admin_route'].'/recommend/rank/move', '\Plugin\Recommend\Controller\RecommendController::moveRank')
            ->bind('admin_recommend_rank_move');

        // 商品検索画面表示
        $app->post('/'.$app['config']['admin_route'].'/recommend/search/product', '\Plugin\Recommend\Controller\RecommendSearchModelController::searchProduct')
            ->bind('admin_recommend_search_product');

        $app->match('/'.$app['config']['admin_route'].'/recommend/search/product/page/{page_no}', '\Plugin\Recommend\Controller\RecommendSearchModelController::searchProduct')
            ->assert('page_no', '\d+')
            ->bind('admin_recommend_search_product_page');

        // ブロック
        $app->match('/block/recommend_product_block', '\Plugin\Recommend\Controller\Block\RecommendController::index')
            ->bind('block_recommend_product_block');

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new RecommendProductType($app);

            return $types;
        }));

        // サービスの登録
        $app['eccube.plugin.recommend.service.recommend'] = $app->share(function () use ($app) {
            return new RecommendService($app);
        });

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function (Translator $translator, Application $app) {
            $file = __DIR__.'/../Resource/locale/message.'.$app['locale'].'.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));

        // Config
        $app['config'] = $app->share($app->extend('config', function ($config) {
            // menu bar
            $addNavi['id'] = 'admin_recommend';
            $addNavi['name'] = 'おすすめ管理';
            $addNavi['url'] = 'admin_recommend_list';
            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ('content' == $val['id']) {
                    $nav[$key]['child'][] = $addNavi;
                }
            }
            $config['nav'] = $nav;

            // Update path
            $pathFile = __DIR__.'/../Resource/config/path.yml';
            if (file_exists($pathFile)) {
                $path = Yaml::parse(file_get_contents($pathFile));
                if (!empty($path)) {
                    // Replace path
                    $config = array_replace_recursive($config, $path);
                }
            }

            // Update constants
            $constantFile = __DIR__.'/../Resource/config/constant.yml';
            if (file_exists($constantFile)) {
                $constant = Yaml::parse(file_get_contents($constantFile));
                if (!empty($constant)) {
                    // Replace constants
                    $config = array_replace_recursive($config, $constant);
                }
            }

            return $config;
        }));

        // initialize logger (for 3.0.0 - 3.0.8)
        if (!Version::isSupportGetInstanceFunction()) {
            eccube_log_init($app);
        }
    }

    /**
     * @param BaseApplication $app
     */
    public function boot(BaseApplication $app)
    {
    }
}
