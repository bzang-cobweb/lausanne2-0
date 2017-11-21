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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class NewsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'label.title',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('teaser', TextareaType::class, [
                'required' => false,
                'label' => 'label.teaser',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('championship', EntityType::class, [
                'label' => 'label.championship',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ],
                'class' => 'AppBundle:Championship',
                'required' => false,
                'multiple' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.deleted = 0')
                        ->orderBy('c.name', 'ASC');
                }
            ])
            ->add('body', TextareaType::class, [
                'required' => false,
                'label' => 'label.content',
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
            'data_class' => 'AppBundle\Entity\News'
        ));
    }


    public function getName()
    {
        return 'form_type_news';
    }

}