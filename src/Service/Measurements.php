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
                'mintemp_dht_hic' => $this->prepareDataValue($data, 'mintemp_dht_hic'),
                'maxtemp_dht_hic' => $this->prepareDataValue($data, 'maxtemp_dht_hic'),
                'avgtemp_dht_hic' => $this->prepareDataValue($data, 'avgtemp_dht_hic'),
                'mintemp_dht_hif' => $this->prepareDataValue($data, 'mintemp_dht_hif'),
                'maxtemp_dht_hif' => $this->prepareDataValue($data, 'maxtemp_dht_hif'),
                'avgtemp_dht_hif' => $this->prepareDataValue($data, 'avgtemp_dht_hif'),
                'minhumidity_dht' => $this->prepareDataValue($data, 'minhumidity_dht'),
                'maxhumidity_dht' => $this->prepareDataValue($data, 'maxhumidity_dht'),
                'avghumidity_dht' => $this->prepareDataValue($data, 'avghumidity_dht'),
                'minpressure_bmp' => $this->prepareDataValue($data, 'minpressure_bmp'),
                'maxpressure_bmp' => $this->prepareDataValue($data, 'maxpressure_bmp'),
                'avgpressure_bmp' => $this->prepareDataValue($data, 'avgpressure_bmp'),
                'mintemp_bmp' => $this->prepareDataValue($data, 'mintemp_bmp'),
                'maxtemp_bmp' => $this->prepareDataValue($data, 'maxtemp_bmp'),
                'avgtemp_bmp' => $this->prepareDataValue($data, 'avgtemp_bmp'),
                'datetime_from' => $this->prepareDataValueDatetime($data,'mindatetime'),
                'datetime_to' => $this->prepareDataValueDatetime($data, 'maxdatetime'),
                'dateday' => $this->prepareDataValueDate($data, 'mindatetime')
            );

            //Return only valid temperatures in range of -50 to +60 °C
            if(
                floatval($this->prepareDataValue($data, 'maxtemp_dht_hic')) < 60 &&
                floatval($this->prepareDataValue($data, 'maxtemp_bmp')) < 60 &&
                floatval($this->prepareDataValue($data, 'maxtemp_dht_hic')) > -50 &&
                floatval($this->prepareDataValue($data, 'maxtemp_bmp')) > -50
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

    public function prepareDataValue(array $data, string $dataKey): ?string
    {
        if(array_key_exists($dataKey, $data) && $data[$dataKey]) {
            return number_format($data[$dataKey],2);
        }
        return null;
    }

    public function prepareDataValueDatetime(array $data, string $dataKey): ?string
    {
        if(array_key_exists($dataKey, $data) && $data[$dataKey]) {
            return $data[$dataKey];
        }
        return null;
    }

    public function prepareDataValueDate(array $data, string $dataKey): ?string
    {
        if(array_key_exists($dataKey, $data) && $data[$dataKey]) {
            return date("d.m.", strtotime($data['mindatetime']));
        }

        return null;
    }
}
