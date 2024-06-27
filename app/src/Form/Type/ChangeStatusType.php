<?php

namespace App\Form\Type;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add
            ('status', ChoiceType::class, [
            'choices' => [
                'label.pending' => 'label.pending',
                'label.accepted' => 'label.accepted',
                'label.cancelled' => 'label.cancelled',
                ],
            'label' => 'label.status',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            ]);
    }
}
