<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Bulletin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BulletinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du Bulletin'
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie du Bulletin',
                'choices' => [
                //  Val Affichée => Val Retenue
                    'Général' => 'Général',
                    'Divers' => 'Divers',
                    'Urgent' => 'Urgent',
                ],
                'expanded' => false, //False: Menu Déroulant | True: Case à cocher
                'multiple' => false, //Multiple choix, ici FALSE sous peine d'erreur
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu du Bulletin',
            ])
            ->add('tags', EntityType::class, [
                'label' => 'Tags', //Désignation du champ
                'class' => Tag::class, //Classe de l'objet à lier
                'choice_label' => 'name', //Attribut représentant notre objet visé
                'expanded' => true, //Bouton plutôt qu'un affichage menu
                'multiple' => true, //Nécessaire en raison du ManyToMany
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'style' => 'margin-top: 5px',
                    'class' => 'btn btn-success',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bulletin::class,
        ]);
    }
}
