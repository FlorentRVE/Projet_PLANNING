<?php

namespace App\Form;

use App\Entity\Roulement;
use App\Entity\Service;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoulementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date')
            ->add('priseDeService')
            ->add('finDeService')
            ->add('service', EntityType::class, [
                'class' => Service::class,
'choice_label' => 'label',
            ])
            ->add('agent', EntityType::class, [
                'class' => User::class,
'choice_label' => 'username',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Roulement::class,
        ]);
    }
}
