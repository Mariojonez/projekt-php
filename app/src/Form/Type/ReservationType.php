<?php

namespace App\Form\Type;

use App\Entity\Reservation;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function __construct(string $userEmail = null)
    {
        $this->userEmail = $userEmail;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add(
            'user',
            EntityType::class,
            [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'label.user',
                'disabled' => true,
            ]
        )
        ->add(
            'task',
            EntityType::class,
            [
                'class' => Task::class,
                'choice_label' => 'title',
                'label' => 'label.select_task',
                'placeholder' => 'placeholder.select_task',
                'required' => true,
            ]
        )
        ->add(
            'status',
            ChoiceType::class,
            [
                'label' => 'label.status',
                'choices' => [
                    'label.pending' => 'label.pending',
                ],
                'disabled' => true, // Status field is disabled
                'data' => 'label.pending', // Default value for status
                'attr' => ['class' => 'form-control'],
            ]
        )
        ->add(
            'comment',
            TextareaType::class,
            [
                'label' => 'label.comment',
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
