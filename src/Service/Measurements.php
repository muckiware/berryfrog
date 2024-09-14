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

    public function getLast24HoursValues(): array
    {
        $last24HoursValues = array();
        $date = date('Y-m-d H:i:s', strtotime('-24 hour'));
        $db = $this->measurementsRepository->createQueryBuilder('m');
        $db->where('(m.addDatetime > :date AND (m.tempDhtHic - m.tempBmp) < 8) OR (m.addDatetime > :date AND (m.tempBmp - m.tempDhtHic) < 8)');
        $db->setParameter('date', $date);
        $db->orderBy('m.addDatetime','asc');


        /** @var Measurement $resultMeasurement */
        $resultMeasurements = $db->getQuery()->getResult();
        foreach ($resultMeasurements as $resultMeasurement) {

            $resultMeasurementArray = $resultMeasurement->toArray();
            $resultMeasurementArray['createDateTime'] = $resultMeasurement->getAddDatetime();
            $last24HoursValues[] = $resultMeasurementArray;
        }

        return $last24HoursValues;
    }
}
