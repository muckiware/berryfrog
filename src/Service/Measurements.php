<?php declare(strict_types=1);
/**
 * @package    Berryfrog
 * @copyright  Copyright (c) 2024 by muckiware
 */
namespace App\Service;

use App\Entity\Measurement;
use App\Repository\MeasurementsRepository;

class Measurements
{
    public function __construct(
        protected MeasurementsRepository $measurementsRepository
    )
    {}

    public function getCurrentValues(): array
    {
        $db = $this->measurementsRepository->createQueryBuilder('m');
        $db->setMaxResults(1);
        $db->orderBy('m.id','desc');

        /** @var Measurement $singleResultMeasurement */
        $singleResultMeasurement = $db->getQuery()->getSingleResult();
        $currentValues = $singleResultMeasurement->toArray();
        $currentValues['createDateTime'] = $singleResultMeasurement->getAddDatetime();

        return $currentValues;
    }
}
