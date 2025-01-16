<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ReportComment;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', CKEditorType::class, [
                'attr' => [
                    'class' => 'mb-0'
                ],
                'label' => false,
                'config' => [
                    'uiColor' => '#ffffff',
                    'versionCheck' => false,
                    'removePlugins' => 'elementspath',
                    'resize_enabled' => false,
                    'toolbar' => [
                        ['Bold', 'Italic', 'Underline', 'Strike'],
                        ['Link', 'Unlink', 'NumberedList', 'BulletedList'],
                        ['Blockquote']
                    ],
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The comment should be not empty'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'The comment should be at least {{ limit }} characters'
                    ])
                ]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReportComment::class,
            'csrf_protection' => false
        ]);
    }
}
