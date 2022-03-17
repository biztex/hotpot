<?php
/*
* Plugin Name : ContactProduct
*
* Copyright (C) 2015 BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\ContactProduct\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ContactExtension extends AbstractTypeExtension
{
    public $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;

        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use($app) {
                /** @var \Symfony\Component\Form\Form $form */
                $form = $event->getForm();

                $request = $app['request'];
                if($product_id = $request->get('product_id')){
                    if(is_numeric($product_id)){
                        $Product = $app['eccube.repository.product']->find($product_id);
                        $value = '[商品ID:'. $Product->getId(). ']' .$Product->getName() . "についての問い合わせ\n";
                        $form['contents']->setData($value);
                    }
                }

            });
    }

    public function getExtendedType()
    {
        return 'contact';
    }

}
