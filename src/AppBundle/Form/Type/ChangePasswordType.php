<?php
/**
 * Created by PhpStorm.
 * User: bzang
 * Date: 04/02/16
 * Time: 19:30
 */

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ChangePasswordType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'user.new_password',
                'required' => true,
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('repeat_password', PasswordType::class, [
                'label' => 'user.repeat_password',
                'required' => true,
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'button.change',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-md btn-default'
                ]
            ]);
    }


    public function getName()
    {
        return 'form_type_change_password';
    }
}