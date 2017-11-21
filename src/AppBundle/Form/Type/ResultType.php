<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 19:30
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\Season;
use AppBundle\Entity\SeasonTeam;
use AppBundle\Entity\Match;
use AppBundle\Entity\Result;
use AppBundle\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Doctrine\ORM\EntityRepository;

class ResultType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('homeGoal',IntegerType::class, [
                'required' => true,
                'label' => 'result.goal',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('visitorGoal',IntegerType::class, [
                'required' => true,
                'label' => 'result.goal',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('homeYellowCard',IntegerType::class, [
                'required' => true,
                'label' => 'result.yellow_card',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 14
                ]
            ])
            ->add('visitorYellowCard',IntegerType::class, [
                'required' => true,
                'label' => 'result.yellow_card',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 14
                ]
            ])
            ->add('homeRedCard',IntegerType::class, [
                'required' => true,
                'label' => 'result.red_card',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 6
                ]
            ])
            ->add('visitorRedCard',IntegerType::class, [
                'required' => true,
                'label' => 'result.red_card',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 6
                ]
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Result'
        ));
    }


    public function getName()
    {
        return 'form_type_result';
    }

}