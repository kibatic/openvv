<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

class MediaRepository extends SortableRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        $class = new ClassMetadata(Media::class);
        parent::__construct($em, $class);
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

//    public function findOneBySomeField($value): ?Media
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
