<?php declare(strict_types=1);
/**
 * @package    Berryfrog
 * @copyright  Copyright (c) 2024 by muckiware
 */
namespace App\Repository;

use App\Entity\Measurement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Measurement>
 */
class MeasurementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Measurement::class);
    }
}
