<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Dépôt des médias (panoramas) d'un projet.
 *
 * Le tri des médias dans un projet (champ orderInProject) est géré par
 * l'extension Gedmo Sortable, via les attributs #[Gedmo\SortableGroup] /
 * #[Gedmo\SortablePosition] de l'entité Media et l'écouteur Doctrine
 * enregistré par StofDoctrineExtensionsBundle — pas par ce dépôt.
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function save(Media $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Media $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Media[] Returns an array of Media objects
     */
    public function findByProject(Project $project): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.project = :project')
            ->setParameter('project', $project)
            ->orderBy('m.orderInProject', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
