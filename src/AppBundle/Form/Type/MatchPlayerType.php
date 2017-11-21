<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 19:30
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\Championship;
use AppBundle\Entity\MatchPlayer;
use AppBundle\Entity\Season;
use AppBundle\Entity\Match;
use AppBundle\Entity\Result;
use AppBundle\Entity\SingleResult;
use AppBundle\Entity\Team;
use AppBundle\Utility\EntityUtility;
use AppBundle\Utility\ToolUtility;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;




class MatchPlayerType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $matchPlayer = $options['data'];

        $builder
            ->add('team', EntityType::class, [
                'label' => 'label.team',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control change-submit-form'
                ],
                'class' => 'AppBundle:Team',
                'required' => false,
                'multiple' => false,
                'choices' => $matchPlayer->getTeams()
            ]);

        $formModifier = function (FormInterface $form, Team $team = null) {
            if($team instanceof Team) {
                $form
                    ->add('player', EntityType::class, [
                        'class' => 'AppBundle:Player',
                        'label' => 'label.player',
                        'translation_domain' => 'messages',
                        'attr' => [
                            'class' => 'form-control'
                        ],
                        'required' => false,
                        'multiple' => false,
                        'choices' => $team->getPlayers()
                    ])
                    ->add('goal',IntegerType::class, [
                        'required' => true,
                        'label' => 'result.goal',
                        'attr' => [
                            'class' => 'form-control',
                            'min' => 0
                        ]
                    ])
                    ->add('autoGoal',IntegerType::class, [
                        'required' => true,
                        'label' => 'label.auto_goal',
                        'attr' => [
                            'class' => 'form-control',
                            'min' => 0
                        ]
                    ])
                    ->add('yellowCard',IntegerType::class, [
                        'required' => true,
                        'label' => 'result.yellow_card',
                        'attr' => [
                            'class' => 'form-control',
                            'min' => 0,
                            'max' => 2
                        ]
                    ])
                    ->add('redCard',IntegerType::class, [
                        'required' => true,
                        'label' => 'result.red_card',
                        'attr' => [
                            'class' => 'form-control',
                            'min' => 0,
                            'max' => 1
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
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $matchPlayer = $event->getData();
                $form = $event->getForm();
                $formModifier($form, $matchPlayer->getTeam());
            }
        );

        $builder->get('team')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $matchPlayer = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $matchPlayer);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\MatchPlayer'
        ));
    }


    public function getName()
    {
        return 'form_type_match_player';
    }
}