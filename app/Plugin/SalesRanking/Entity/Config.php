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

namespace Plugin\SalesRanking\Entity;

use Doctrine\ORM\Mapping as ORM;

class Config extends \Eccube\Entity\AbstractEntity
{   
    private $name;
    
    private $value;
    
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
        
        return $this;
    }
    
    public function getValue()
    {
        return $this->value;
    }
}
