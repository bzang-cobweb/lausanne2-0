<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 19:30
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\Championship;
use AppBundle\Entity\Season;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class ChampionshipType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'championship.name',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('cup', CheckboxType::class, [
                'label' => 'championship.cup',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('highlighted', CheckboxType::class, [
                'label' => 'championship.highlighted',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('teams', EntityType::class, [
                'label' => 'championship.teams',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ],
                'class' => 'AppBundle:Team',
                'required' => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.deleted = 0')
                        ->orderBy('t.name', 'ASC');
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
            'data_class' => 'AppBundle\Entity\Championship'
        ));
    }


    public function getName()
    {
        return 'form_type_championship';
    }

}