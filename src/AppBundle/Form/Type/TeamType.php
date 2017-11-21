<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 19:30
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class TeamType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'team.name',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('trigram', TextType::class, [
                'required' => true,
                'label' => 'team.alias',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'team.description',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('players', EntityType::class, [
                'label' => 'team.players',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ],
                'class' => 'AppBundle:Player',
                'required' => false,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.deleted = 0')
                        ->orderBy('p.firstname', 'ASC')
                        ->orderBy('p.lastname', 'ASC')
                        ->orderBy('p.trigram', 'ASC');
                }])
            ->add('championships', EntityType::class, [
                'label' => 'team.championships',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ],
                'class' => 'AppBundle:Championship',
                'required' => false,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.deleted = 0')
                        ->orderBy('c.name', 'ASC');
                }])
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
            'data_class' => 'AppBundle\Entity\Team'
        ));
    }


    public function getName()
    {
        return 'form_type_team';
    }
}