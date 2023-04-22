<?php

namespace App\Form;

use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\StringType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',null,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a last name',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Your name should be at least 3 characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ]] )
            ->add('description',null,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a first name',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Your description should be at least 3 characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ]])
            ->add('prix',NumberType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a price',
                    ]),
                ]] )
            ->add('stock',NumberType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a stock',
                    ]),
                ]] )
            ->add('photo', FileType::class, [
                'label' => 'Photo (JPG,JPEG,PNG)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPG,JPEG,PNG image',
                    ])
                ],
            ])
            ->add('Save',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
