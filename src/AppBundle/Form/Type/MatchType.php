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
use AppBundle\Entity\Match;
use AppBundle\Entity\Result;
use AppBundle\Entity\SingleResult;
use AppBundle\Utility\EntityUtility;
use AppBundle\Utility\ToolUtility;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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




class MatchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('championship', EntityType::class, [
                'label' => 'match.championship',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control change-submit-form'
                ],
                'class' => 'AppBundle:Championship',
                'required' => false,
                'multiple' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.deleted = 0')
                        ->orderBy('c.name', 'ASC');
                }]);

        $formModifier = function (FormInterface $form, Championship $championship = null) {
            if($championship instanceof Championship) {
                $teams = $championship->getTeams();
                $form
                    ->add('home', EntityType::class, [
                        'class' => 'AppBundle:Team',
                        'label' => 'match.home',
                        'translation_domain' => 'messages',
                        'attr' => [
                            'class' => 'form-control'
                        ],
                        'required' => false,
                        'multiple' => false,
                        'choices' => $teams
                    ])
                    ->add('visitor', EntityType::class, [
                        'class' => 'AppBundle:Team',
                        'label' => 'match.visitor',
                        'translation_domain' => 'messages',
                        'attr' => [
                            'class' => 'form-control'
                        ],
                        'required' => false,
                        'multiple' => false,
                        'choices' => $teams

                    ])
                    ->add(
                        'season', ChoiceType::class, [
                        'label' => 'match.season',
                        'translation_domain' => 'messages',
                        'attr' => [
                            'class' => 'form-control'
                        ],
                        'required' => true,
                        'multiple' => false,
                        'choices' => EntityUtility::getSeasons()
                    ])
                    ->add('place', TextType::class, [
                        'required' => true,
                        'label' => 'match.place',
                        'translation_domain' => 'messages',
                        'attr' => [
                            'class' => 'form-control',
                        ]
                    ])
                    ->add('scheduledAt', DateTimeType::class, [
                        'required' => true,
                        'label' => 'match.time',
                        'translation_domain' => 'messages',
                        'widget' => 'single_text',
                        'format' => 'dd.MM.yyyy HH:mm',
                        'html5' => false,
                        'attr' => [
                            'class' => 'form-control js-datetime-picker',
                        ]
                    ])
                    ->add('description', TextareaType::class, [
                        'required' => false,
                        'label' => 'match.description',
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

                if($championship->isCup()){
                    $form->add('stage', ChoiceType::class, [
                        'label' => 'match.stage',
                        'translation_domain' => 'messages',
                        'attr' => [
                            'class' => 'form-control'
                        ],
                        'required' => true,
                        'multiple' => false,
                        'choices' => [
                            'match.stage_9999' => 9999,
                            'match.stage_32' => 32,
                            'match.stage_16' => 16,
                            'match.stage_8' => 8,
                            'match.stage_4' => 4,
                            'match.stage_2' => 2,
                            'match.stage_1' => 1,
                        ]

                    ]);
                }
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $match = $event->getData();
                $form = $event->getForm();
                $formModifier($form, $match->getChampionship());

                if($match instanceof Match && $match->getResult() instanceof Result){
                    $form
                        ->add('result', ResultType::class, [
                            'required' => true,
                            'data' => $match->getResult(),

                        ]);
                }
            }
        );

        $builder->get('championship')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $championship = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $championship);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Match'
        ));
    }


    public function getName()
    {
        return 'form_type_match';
    }
}