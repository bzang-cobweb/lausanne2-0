<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 19:30
 */

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class UploadType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('files', CollectionType::class, [
                'entry_type' => FileType::class,
                'entry_options' => [
                    'label' => 'files.upload',
                    'translation_domain' => 'messages',
                    'multiple' => true,
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'accept' => '.jpg,.jpeg,.png'
                    ],
                ],
                'prototype' => true
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'button.save_and_add',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-md btn-default hidden-down'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'button.save',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-md btn-default'
                ]
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Utility\UploadUtility'
        ));
    }

    public function getName()
    {
        return 'form_type_result';
    }
}