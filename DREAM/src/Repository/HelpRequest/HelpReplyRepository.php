<?php

namespace App\Repository\HelpRequest;

use App\Entity\HelpRequest\HelpReply;
use App\Entity\HelpRequest\HelpRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HelpReply|null find($id, $lockMode = null, $lockVersion = null)
 * @method HelpReply|null findOneBy(array $criteria, array $orderBy = null)
 * @method HelpReply[]    findAll()
 * @method HelpReply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelpReplyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpReply::class);
    }

}