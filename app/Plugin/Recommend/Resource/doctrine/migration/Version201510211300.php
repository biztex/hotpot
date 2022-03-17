<?php
/*
 * This file is part of the Recommend Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;
use Doctrine\ORM\EntityManager;
use Plugin\Recommend\Utils\Version;

/**
 * Class Version201510211300.
 */
class Version201510211300 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const NAME = 'plg_recommend_product';

    protected $entities = array(
        'Plugin\Recommend\Entity\RecommendProduct',
    );

    /**
     * Setup data.
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (Version::isSupportGetInstanceFunction()) {
            $this->createRecommendProduct($schema);
        } else {
            $this->createRecommendProductForOldVersion($schema);
        }
    }

    /**
     * Remove data.
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if (Version::isSupportGetInstanceFunction()) {
            $app = Application::getInstance();
            $meta = $this->getMetadata($app['orm.em']);
            $tool = new SchemaTool($app['orm.em']);
            $schemaFromMetadata = $tool->getSchemaFromMetadata($meta);
            // テーブル削除
            foreach ($schemaFromMetadata->getTables() as $table) {
                if ($schema->hasTable($table->getName())) {
                    $schema->dropTable($table->getName());
                }
            }
            // シーケンス削除
            foreach ($schemaFromMetadata->getSequences() as $sequence) {
                if ($schema->hasSequence($sequence->getName())) {
                    $schema->dropSequence($sequence->getName());
                }
            }
        } else {
            // this down() migration is auto-generated, please modify it to your needs
            $schema->dropTable(self::NAME);
            $schema->dropSequence('plg_recommend_product_recommend_product_id_seq');
        }
    }

    /**
     * Create recommend table.
     *
     * @param Schema $schema
     *
     * @return bool
     */
    protected function createRecommendProduct(Schema $schema)
    {
        if ($schema->hasTable(self::NAME)) {
            return true;
        }

        $app = Application::getInstance();
        $em = $app['orm.em'];
        $classes = array(
            $em->getClassMetadata('Plugin\Recommend\Entity\RecommendProduct'),
        );
        $tool = new SchemaTool($em);
        $tool->createSchema($classes);

        return true;
    }

    /**
     * おすすめ商品テーブル作成.
     *
     * @param Schema $schema
     */
    protected function createRecommendProductForOldVersion(Schema $schema)
    {
        $table = $schema->createTable(self::NAME);
        $table->addColumn('recommend_product_id', 'integer', array(
            'autoincrement' => true,
            'notnull' => true,
        ));

        $table->addColumn('product_id', 'integer', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('comment', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('rank', 'integer', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 1,
        ));

        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('recommend_product_id'));
    }

    /**
     * Get metadata.
     *
     * @param EntityManager $em
     *
     * @return array
     */
    protected function getMetadata(EntityManager $em)
    {
        $meta = array();
        foreach ($this->entities as $entity) {
            $meta[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }

        return $meta;
    }
}
