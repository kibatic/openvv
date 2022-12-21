<?php

namespace App\Form;

use App\Entity\Link;
use App\Entity\Media;
use App\Entity\Project;
use App\Repository\MediaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('targetMedia', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'name',
                'query_builder' => function (MediaRepository $repo) use ($options) {
                    return $repo->createQueryBuilder('m')
                        ->where('m.project = :project')
                        ->andWhere('m.id != :currentMedia')
                        ->orderBy('m.orderInProject', 'ASC')
                        ->setParameter('currentMedia', $options['media']->getId())
                        ->setParameter('project', $options['project'])
                    ;
                },
            ])
            ->add('sourcePitch', HiddenType::class)
            ->add('sourceYaw', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Link::class,
            'project' => null,
            'media' => null,
        ]);
        $resolver->addAllowedTypes('project', Project::class);
    }
}
