<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 19:30
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\Picture;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class PictureType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'label.file',
                'translation_domain' => 'messages',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => implode(',', Picture::$acceptExtensions)
                ],
                'required' => false
            ])
            ->add('cover', CheckboxType::class, [
                'label' => 'label.cover_image',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'label.title',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('alt', TextType::class, [
                'required' => false,
                'label' => 'label.alt',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
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
            'data_class' => 'AppBundle\Entity\Picture'
        ));
    }

    public function getName()
    {
        return 'form_type_picture';
    }
}