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
        return $this->createMeasurementExtensions($singleResultMeasurement);
    }

    public function getLast24HoursValues(): array
    {
        $last24HoursValues = array();
        $date = date('Y-m-d H:i:s', strtotime('-24 hour'));
        $db = $this->measurementsRepository->createQueryBuilder('m');
        $db->where('
            (m.addDatetime > :date AND (m.tempDhtHic - m.tempBmp) < 8)
            OR
            (m.addDatetime > :date AND (m.tempBmp - m.tempDhtHic) < 8)
        ');
        $db->setParameter('date', $date);
        $db->orderBy('m.addDatetime','asc');


        /** @var Measurement $resultMeasurement */
        $resultMeasurements = $db->getQuery()->getResult();
        foreach ($resultMeasurements as $resultMeasurement) {
            $last24HoursValues[] = $this->createMeasurementExtensions($resultMeasurement);
        }

        return $last24HoursValues;
    }

    public function getlastNDays(int $days): array
    {
        $result = array();
        $day_i = $days;

        for ($i = 1; $i <= $days; $i++) {

            $db = $this->measurementsRepository->createQueryBuilder('m');
            $db->select('
				min(m.tempDhtHic) as mintemp_dht_hic,
				max(m.tempDhtHic) as maxtemp_dht_hic,
				avg(m.tempDhtHic) as avgtemp_dht_hic,
				min(m.tempDhtHif) as mintemp_dht_hif,
				max(m.tempDhtHif) as maxtemp_dht_hif,
				avg(m.tempDhtHif) as avgtemp_dht_hif,
				min(m.humidityDht) as minhumidity_dht,
				max(m.humidityDht) as maxhumidity_dht,
				avg(m.humidityDht) as avghumidity_dht,
				min(m.pressureBmp) as minpressure_bmp,
				max(m.pressureBmp) as maxpressure_bmp,
				avg(m.pressureBmp) as avgpressure_bmp,
				min(m.tempBmp) as mintemp_bmp,
				max(m.tempBmp) as maxtemp_bmp,
				avg(m.tempBmp) as avgtemp_bmp,
				min(m.addDatetime) as mindatetime,
				max(m.addDatetime) as maxdatetime
			');

            $greater_date = date('Y-m-d 00:00:00', strtotime('-'.$day_i.' day'));
            $less_date = date('Y-m-d 23:59:59', strtotime('-'.$day_i.' day'));

            $db->setParameter('greaterdate', $greater_date);
            $db->setParameter('lessdate', $less_date);
            $db->where('
				m.addDatetime < :lessdate
				AND
				m.addDatetime > :greaterdate
				AND
				(m.tempBmp - m.tempDhtHic) < 8
				AND
				(m.tempDhtHic - m.tempBmp) < 8
			');

            $db->orderBy('m.addDatetime','asc');

            $data = $db->getQuery()->getSingleResult();
            $datas = array(
                'mintemp_dht_hic' => number_format($data['mintemp_dht_hic'],2),
                'maxtemp_dht_hic' => number_format($data['maxtemp_dht_hic'],2),
                'avgtemp_dht_hic' => number_format($data['avgtemp_dht_hic'],2),
                'mintemp_dht_hif' => number_format($data['mintemp_dht_hif'],2),
                'maxtemp_dht_hif' => number_format($data['maxtemp_dht_hif'],2),
                'avgtemp_dht_hif' => number_format($data['avgtemp_dht_hif'],2),
                'minhumidity_dht' => number_format($data['minhumidity_dht'],2),
                'maxhumidity_dht' => number_format($data['maxhumidity_dht'],2),
                'avghumidity_dht' => number_format($data['avghumidity_dht'],2),
                'minpressure_bmp' => number_format($data['minpressure_bmp'],2),
                'maxpressure_bmp' => number_format($data['maxpressure_bmp'],2),
                'avgpressure_bmp' => number_format($data['avgpressure_bmp'],2),
                'mintemp_bmp' => number_format($data['mintemp_bmp'],2),
                'maxtemp_bmp' => number_format($data['maxtemp_bmp'],2),
                'avgtemp_bmp' => number_format($data['avgtemp_bmp'],2),
                'datetime_from' => $data['mindatetime'],
                'datetime_to' => $data['maxdatetime'],
                'dateday' => date("d.m.", strtotime($data['mindatetime']))
            );

            //Return only valid temperatures in range of -50 to +60 °C
            if(
                number_format($data['maxtemp_dht_hic'],2) < 60 &&
                number_format($data['maxtemp_bmp'],2) < 60 &&
                number_format($data['maxtemp_dht_hic'],2) > -50 &&
                number_format($data['maxtemp_bmp'],2) > -50
            ) {
                $result[] = $datas;
            }

            $day_i = $day_i -1;

        }

        return $result;
    }

    public function createMeasurementExtensions(Measurement $resultMeasurement): array
    {
        $resultMeasurementArray = $resultMeasurement->toArray();
        $resultMeasurementArray['createDateTime'] = $resultMeasurement->getAddDatetime();

        return $resultMeasurementArray;
    }
}
