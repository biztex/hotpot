<?php
/*
 * This file is part of the Recommend Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Recommend\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvents;
use Eccube\Form\DataTransformer;

/**
 * Class RecommendProductType.
 */
class RecommendProductType extends AbstractType
{
    /**
     * @var \Eccube\Application
     */
    private $app;

    /**
     * RecommendProductType constructor.
     *
     * @param \Silex\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Build config type form.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;

        $builder
            ->add('id', 'text', array(
                'label' => '商品',
                'required' => false,
                'attr' => array('readonly' => 'readonly'),
            ))
            ->add('comment', 'textarea', array(
                'label' => '説明文',
                'required' => true,
                'trim' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => $app['config']['text_area_len'],
                    )),
                ),
                'attr' => array(
                    'maxlength' => $app['config']['text_area_len'],
                    'placeholder' => $app->trans('plugin.recommend.type.comment.placeholder'),
                ),
            ));

        $builder->add(
            $builder
                ->create('Product', 'hidden')
                ->addModelTransformer(new DataTransformer\EntityToIdTransformer($this->app['orm.em'], '\Eccube\Entity\Product'))
        );

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($app) {
            $form = $event->getForm();
            $data = $form->getData();

            // Check product
            $Product = $data['Product'];
            if (empty($Product)) {
                $form['comment']->addError(new FormError($app->trans('plugin.recommend.type.product.not_found')));

                return;
            }
        });
    }

    /**
     *  {@inheritdoc}
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plugin\Recommend\Entity\RecommendProduct',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_recommend';
    }
}
