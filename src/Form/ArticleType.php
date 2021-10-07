<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('date_publication', DateType::class, [
				'widget' => 'single_text',
				'input_format' => 'd/m/Y',
			])
            ->add('image', FileType::class,[
                'mapped' => false,
                'required' => false

            ])
            ->add('contenu', TextareaType::class,[
                'attr' =>
                [
                    'placeholder' => 'Commentaire... ',
                    'rows' => 10,
                ]
            ])
            ->add('submit', SubmitType::class, [
				'label' => 'Envoyer'	
			])        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
