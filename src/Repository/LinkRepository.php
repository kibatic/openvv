<?php

namespace App\Repository;

use App\Entity\Link;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 *
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function save(Link $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getBackLink(Link $link): ?Link
    {
        return $this->findOneBy([
            'sourceMedia' => $link->getTargetMedia(),
            'targetMedia' => $link->getSourceMedia(),
        ]);
    }

    public function remove(Link $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByProject(Project $project): array
    {
        $linksBySource = $this->createQueryBuilder('l')
            ->where('m.project = :project')
            ->andWhere('l.sourceYaw IS NOT NULL')
            ->andWhere('l.sourcePitch IS NOT NULL')
            ->andWhere('l.targetYaw IS NOT NULL')
            ->andWhere('l.targetPitch IS NOT NULL')
            ->join('l.sourceMedia', 'm')
            ->setParameter('project', $project)
            ->getQuery()->getResult()
        ;
        $linksByTarget = $this->createQueryBuilder('l')
            ->where('m.project = :project')
            ->andWhere('l.sourceYaw IS NOT NULL')
            ->andWhere('l.sourcePitch IS NOT NULL')
            ->andWhere('l.targetYaw IS NOT NULL')
            ->andWhere('l.targetPitch IS NOT NULL')
            ->join('l.targetMedia', 'm')
            ->setParameter('project', $project)
            ->getQuery()->getResult()
        ;
        // merge linksByTarget and linksBySource and deduplicate
        $links = [];
        foreach ($linksBySource as $link) {
            $links[$link->getId()] = $link;
        }
        foreach ($linksByTarget as $link) {
            $links[$link->getId()] = $link;
        }
        return array_values($links);
    }
}
