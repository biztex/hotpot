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

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150911190000 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->createPlgSalesRankingConfig($schema);
    }
    
    public function postUp(Schema $schema)
    {

        $app = new \Eccube\Application();
        $app->boot();
        
        $this->connection->insert('plg_sales_ranking_dtb_config' , array('name' => 'term', 'value' => 0));
        $this->connection->insert('plg_sales_ranking_dtb_config' , array('name' => 'display_num', 'value' => 5));
    }

    public function down(Schema $schema)
    {
        $this->dropPlgSalesRankingConfig($schema);
    }
    
    protected function createPlgSalesRankingConfig(Schema $schema)
    {
        if ($schema->hasTable("plg_sales_ranking_dtb_config")) {
            return true;
        }
        $table = $schema->createTable("plg_sales_ranking_dtb_config");

        $table->addColumn('name', 'text', array(
            'notnull' => true,
        ));
        
        $table->addColumn('value', 'text', array(
            'notnull' => false,
        ));
    }
    
    protected function dropPlgSalesRankingConfig(Schema $schema)
    {
        if (!$schema->hasTable("plg_sales_ranking_dtb_config")) {
            return true;
        }
        $schema->dropTable('plg_sales_ranking_dtb_config');
    }
}
