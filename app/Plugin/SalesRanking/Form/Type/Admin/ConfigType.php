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

namespace Plugin\SalesRanking\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigType extends AbstractType
{
    public $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;
        
        $arrTerm[0] = "1週間";
        $arrTerm[1] = "2週間";
        $arrTerm[2] = "1ヶ月間";
        $arrTerm[3] = "1年間";
        $arrTerm[4] = "無期限";

        $builder
            ->add('term', 'choice', array(
                'label' => '集計期間',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'empty_value' => false,
                'choices'  => $arrTerm,
            ))
            ->add('display_num', 'number', array(
                'label' => '表示個数',
                'required' => true,
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber())
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_setting_sales_ranking';
    }
}
