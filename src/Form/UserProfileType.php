<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserProfileType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('fullname')
            ->add('lang', ChoiceType::class, [
                'choices'  => [
                    $this->translator->trans('English') => 'en',
                    $this->translator->trans('French') => 'fr',
                    $this->translator->trans('German') => 'de',
                    $this->translator->trans('Spanish') => 'es',
                ],
            ])
            ->add('preferedTheme', ChoiceType::class, [
                'choices'  => [
                    $this->translator->trans('Light') => 'light',
                    $this->translator->trans('Dark') => 'dark',
                    $this->translator->trans('System') => 'auto',
                ]
            ])
            ->add('imageFilename', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
