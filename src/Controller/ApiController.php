<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use App\Core\Defaults;
use App\Repository\MeasurementsRepository;
use App\Service\Measurements as ServiceMeasurements;

class ApiController extends AbstractController
{

    public function __construct(
        protected ServiceMeasurements $serviceMeasurements
    )
    {}

    #[Route('/api', name: 'app_api')]
    public function index(MeasurementsRepository $measurementsRepository): JsonResponse
    {

        $checkesr = $this->serviceMeasurements->getCurrentValues();
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/api/measurements/currentvalues', name: 'app_api_measurements_currentvalues')]
    public function currentValues(): JsonResponse
    {
        $currentValues = $this->serviceMeasurements->getCurrentValues()->toArray();
        return $this->json($currentValues);
    }

    /**
     * Method for to get current values from sensor
     *
     * @return array one db object result
     */
//    private function getCurrentValues(MeasurementRepository $measurementRepository): array
//    {
//        $db = $measurementRepository->createQueryBuilder('m')
//            ->setMaxResults(1)
//            ->orderBy('m.id','desc');
//
//        return $db->getQuery()->getSingleResult();
//    }
    /**
     * Method for to get the last 24 hours of values from sensor
     *
     * @return array one db object result
     */
    private function _getlast24Hours() {

        $date = date('Y-m-d H:i:s', strtotime('-24 hour'));
        $db = $this->_repository->createQueryBuilder('r')
            ->where('(r.addDatetime > :date AND (r.tempDhtHic - r.tempBmp) < 8) OR (r.addDatetime > :date AND (r.tempBmp - r.tempDhtHic) < 8)')
            ->setParameter('date', $date)
            ->orderBy('r.addDatetime','asc');


        return $db->getQuery()->getResult();

    }

    /**
     * Method for to get the last 7 days of values from sensor
     *
     * @return array one db object result
     */
    private function _getlastXDays($days) {

        //$logger = $this->get('logger');
        //$logger->info('We just go the logger');

        $result = array();
        $day_i = $days;

        for ($i = 1; $i <= $days; $i++) {

            $db = $this->_repository->createQueryBuilder('r');
            $db->select('
				min(r.tempDhtHic) as mintemp_dht_hic,
				max(r.tempDhtHic) as maxtemp_dht_hic,
				avg(r.tempDhtHic) as avgtemp_dht_hic,
				min(r.tempDhtHif) as mintemp_dht_hif,
				max(r.tempDhtHif) as maxtemp_dht_hif,
				avg(r.tempDhtHif) as avgtemp_dht_hif,
				min(r.humidityDht) as minhumidity_dht,
				max(r.humidityDht) as maxhumidity_dht,
				avg(r.humidityDht) as avghumidity_dht,
				min(r.pressureBmp) as minpressure_bmp,
				max(r.pressureBmp) as maxpressure_bmp,
				avg(r.pressureBmp) as avgpressure_bmp,
				min(r.tempBmp) as mintemp_bmp,
				max(r.tempBmp) as maxtemp_bmp,
				avg(r.tempBmp) as avgtemp_bmp,
				min(r.addDatetime) as mindatetime,
				max(r.addDatetime) as maxdatetime
			');

            $greater_date = date('Y-m-d 00:00:00', strtotime('-'.$day_i.' day'));
            $less_date = date('Y-m-d 23:59:59', strtotime('-'.$day_i.' day'));

            $db->setParameter('greaterdate', $greater_date);
            $db->setParameter('lessdate', $less_date);
            $db->where('
				r.addDatetime < :lessdate
				AND
				r.addDatetime > :greaterdate
				AND
				(r.tempBmp - r.tempDhtHic) < 8
				AND
				(r.tempDhtHic - r.tempBmp) < 8
			');

            $db->orderBy('r.addDatetime','asc');

            $data = $db->getQuery()->getResult();

            if($data[0]['maxtemp_dht_hic'] > 60 || $data[0]['maxtemp_bmp'] > 60) {
                //continue;
            }

            if($data[0]['mintemp_dht_hic'] < -50 || $data[0]['mintemp_bmp'] < -50) {
                //continue;
            }
            $datas = array(
                'mintemp_dht_hic' => number_format($data[0]['mintemp_dht_hic'],2),
                'maxtemp_dht_hic' => number_format($data[0]['maxtemp_dht_hic'],2),
                'avgtemp_dht_hic' => number_format($data[0]['avgtemp_dht_hic'],2),
                'mintemp_dht_hif' => number_format($data[0]['mintemp_dht_hif'],2),
                'maxtemp_dht_hif' => number_format($data[0]['maxtemp_dht_hif'],2),
                'avgtemp_dht_hif' => number_format($data[0]['avgtemp_dht_hif'],2),
                'minhumidity_dht' => number_format($data[0]['minhumidity_dht'],2),
                'maxhumidity_dht' => number_format($data[0]['maxhumidity_dht'],2),
                'avghumidity_dht' => number_format($data[0]['avghumidity_dht'],2),
                'minpressure_bmp' => number_format($data[0]['minpressure_bmp'],2),
                'maxpressure_bmp' => number_format($data[0]['maxpressure_bmp'],2),
                'avgpressure_bmp' => number_format($data[0]['avgpressure_bmp'],2),
                'mintemp_bmp' => number_format($data[0]['mintemp_bmp'],2),
                'maxtemp_bmp' => number_format($data[0]['maxtemp_bmp'],2),
                'avgtemp_bmp' => number_format($data[0]['avgtemp_bmp'],2),
                'datetime_from' => $data[0]['mindatetime'],
                'datetime_to' => $data[0]['maxdatetime'],
                'dateday' => date("d.m.", strtotime($data[0]['mindatetime']))
            );

            //Return only valid temperatures in range of -50 to +60 °C
            if(
                number_format($data[0]['maxtemp_dht_hic'],2) < 60 &&
                number_format($data[0]['maxtemp_bmp'],2) < 60 &&
                number_format($data[0]['maxtemp_dht_hic'],2) > -50 &&
                number_format($data[0]['maxtemp_bmp'],2) > -50
            ) {
                array_push($result,$datas);
            }

            $day_i = $day_i -1;

        }

        return $result;
    }

    /**
     * Method for to get the last 7 days of values from sensor
     *
     * @return array one db object result
     */
    private function _getAVGOfMonth($date) {

        $result = array();

        $db = $this->_repository->createQueryBuilder('r');
        $db->select('
			min(r.tempDhtHic) as mintemp_dht_hic,
			max(r.tempDhtHic) as maxtemp_dht_hic,
			avg(r.tempDhtHic) as avgtemp_dht_hic,
			min(r.tempDhtHif) as mintemp_dht_hif,
			max(r.tempDhtHif) as maxtemp_dht_hif,
			avg(r.tempDhtHif) as avgtemp_dht_hif,
			min(r.tempBmp) as mintemp_bmp,
			max(r.tempBmp) as maxtemp_bmp,
			avg(r.tempBmp) as avgtemp_bmp,
			min(r.addDatetime) as mindatetime,
			max(r.addDatetime) as maxdatetime
		');

        $greater_date = date($date.'-01 00:00:00');
        $less_date = date($date.'-31 23:59:59');

        $db->setParameter('greaterdate', $greater_date);
        $db->setParameter('lessdate', $less_date);
        $db->where('
            r.addDatetime < :lessdate AND
            r.addDatetime > :greaterdate AND
            r.tempDhtHic < 60 AND
            r.tempBmp < 60 AND
            r.tempDhtHic > -50 AND
            r.tempBmp > -50
        ');

        $data = $db->getQuery()->getResult();

        return ($data[0]['avgtemp_dht_hic'] + $data[0]['avgtemp_bmp']) / 2;
    }

    /**
     * Method for to get a range of values from sensor
     *
     * @return array one db object result
     */
    private function _getRangeValues() {

        $db = $this->_repository->createQueryBuilder('r')
            ->where('r.addDatetime > :date')
            ->setParameter('date', '2017-03-14 20:59:34')
            ->orderBy('r.addDatetime','desc');

        return $db->getQuery()->getResult();

    }
    /**
     * Method for to get a range of values from sensor
     *
     * @return array one db object result
     */
    private function _getSensorIds() {

        $db = $this->_repository->createQueryBuilder('s')
            ->select('s.transmitterId')
            ->groupBy('s.transmitterId');

        return $db->getQuery()->getResult();

    }
    /**
     * Method for to get weather sttic values
     * @param string $order oder direction asc or desc	 *
     * @return array
     */
    private function _getAvailableYears($oder='asc') {

        $db = $this->_repository
            ->createQueryBuilder('r')
            ->select('date_format(r.addDatetime,\'%Y\') AS year')
            ->orderBy('year',$oder)
            ->groupBy('year');


        if($db->getQuery()) {
            return $db->getQuery()->getResult();
        }
        return null;
    }
    /**
     * Method for to get weather sttic values
     * @param string $order oder direction asc or desc	 *
     * @return array
     */
    private function _getAvailableMonths($year,$oder='asc') {

        if(is_numeric($year)) {
            $greater_date = date($year.'-01-01 00:00:00');
            $less_date = date($year.'-12-31 23:59:59');

            $db = $this->_repository
                ->createQueryBuilder('r')
                ->select('date_format(r.addDatetime,\'%m\') AS month')
                ->setParameter('greaterdate', $greater_date)
                ->setParameter('lessdate', $less_date)
                ->where('r.addDatetime < :lessdate AND r.addDatetime > :greaterdate')
                ->orderBy('month',$oder)
                ->groupBy('month');

            return $db->getQuery()->getResult();
        } else {
            return null;
        }
    }
    /**
     * Method for to get weather sttic values
     * @param string $order oder direction asc or desc	 *
     * @return array
     */
    private function _getAvailableDaysPerMonth($date,$oder='asc') {


        $greater_date = date($date.'-01 00:00:00');
        $less_date = date($date.'-31 23:59:59');

        $db = $this->_repository
            ->createQueryBuilder('r')
            ->select('date_format(r.addDatetime,\'%d\') AS day')
            ->setParameter('greaterdate', $greater_date)
            ->setParameter('lessdate', $less_date)
            ->where('r.addDatetime < :lessdate AND r.addDatetime > :greaterdate')
            ->orderBy('day',$oder)
            ->groupBy('day');

        return $db->getQuery()->getResult();

    }

    /**
     * Method for to get weather sttic values
     *
     * @return array
     */
    private function _getStatic() {

        $results = array();

        $availableYears = $this->_getAvailableYears();

        if($availableYears && count($availableYears) >= 1) {

            foreach ($this->_getAvailableYears() as $year) {

                if(array_key_exists('year', $year)) {

                    $months = $this->_getAvailableMonths($year['year']);
                    $month_results = array();

                    foreach ($months as $month) {

                        if (array_key_exists('month', $month)) {

                            $monthvalues = array($month['month'] => $this->_getMonthStatic($year['year'].'-'.$month['month']));
                            array_push($month_results, $monthvalues);
                        }
                    }

                    $datas = array($year['year']=> $month_results);
                    array_push($results,$datas);
                }
            }
        }

        return $results;
    }

    /**
     * Method for to get static vaules per months
     *
     * @param string $date example: '12-2017' (MM-YYYY)
     * @return array
     */
    private function _getMonthStatic($date) {

        $db = $this->_repository->createQueryBuilder('r');
        $db->select('
				min(r.tempDhtHic) as mintemp_dht_hic,
				max(r.tempDhtHic) as maxtemp_dht_hic,
				avg(r.tempDhtHic) as avgtemp_dht_hic,
				min(r.tempDhtHif) as mintemp_dht_hif,
				max(r.tempDhtHif) as maxtemp_dht_hif,
				avg(r.tempDhtHif) as avgtemp_dht_hif,
				min(r.humidityDht) as minhumidity_dht,
				max(r.humidityDht) as maxhumidity_dht,
				avg(r.humidityDht) as avghumidity_dht,
				min(r.pressureBmp) as minpressure_bmp,
				max(r.pressureBmp) as maxpressure_bmp,
				avg(r.pressureBmp) as avgpressure_bmp,
				min(r.tempBmp) as mintemp_bmp,
				max(r.tempBmp) as maxtemp_bmp,
				avg(r.tempBmp) as avgtemp_bmp
			');

        $greater_date = date($date.'-01 00:00:00');
        $less_date = date($date.'-31 23:59:59');

        $db->setParameter('greaterdate', $greater_date);
        $db->setParameter('lessdate', $less_date);
        $db->where('
            r.addDatetime < :lessdate AND
            r.addDatetime > :greaterdate AND
            r.tempDhtHic < 60 AND
            r.tempBmp < 60 AND
            r.tempDhtHic > -50 AND
            r.tempBmp > -50 AND
			(r.tempBmp - r.tempDhtHic) < 8 AND
			(r.tempDhtHic - r.tempBmp) < 8
        ');

        $db->orderBy('r.addDatetime','asc');

        $data = $db->getQuery()->getResult();

        $summerdays = $this->_getNumOfDayDefs($date,$this->_DEFSUMMERDAY_START,$this->_DEFSUMMERDAY_END);
        $hotdays = $this->_getNumOfDayDefs($date,$this->_DEFHOTDAY_START,$this->_DEFHOTDAY_END);
        $desertdays = $this->_getNumOfDayDefs($date,$this->_DEFDESERTDAY_START,$this->_DEFDESERTDAY_END);
        //$tropicalnights_part1 = $this->_getNumOfDayDefs($date,$this->_TROPICALNIGHT_START,$this->_TROPICALNIGHT_END,'22:00:00');
        //$tropicalnights_part2 = $this->_getNumOfDayDefs($date,$this->_TROPICALNIGHT_START,$this->_TROPICALNIGHT_END,'0:00:00','06:00:00');

        $datas = array(
            'mintemp_dht_hic' => number_format($data[0]['mintemp_dht_hic'],2),
            'maxtemp_dht_hic' => number_format($data[0]['maxtemp_dht_hic'],2),
            'avgtemp_dht_hic' => number_format($data[0]['avgtemp_dht_hic'],2),
            'mintemp_dht_hif' => number_format($data[0]['mintemp_dht_hif'],2),
            'maxtemp_dht_hif' => number_format($data[0]['maxtemp_dht_hif'],2),
            'avgtemp_dht_hif' => number_format($data[0]['avgtemp_dht_hif'],2),
            'minhumidity_dht' => number_format($data[0]['minhumidity_dht'],2),
            'maxhumidity_dht' => number_format($data[0]['maxhumidity_dht'],2),
            'avghumidity_dht' => number_format($data[0]['avghumidity_dht'],2),
            'minpressure_bmp' => number_format($data[0]['minpressure_bmp'],2),
            'maxpressure_bmp' => number_format($data[0]['maxpressure_bmp'],2),
            'avgpressure_bmp' => number_format($data[0]['avgpressure_bmp'],2),
            'mintemp_bmp' => number_format($data[0]['mintemp_bmp'],2),
            'maxtemp_bmp' => number_format($data[0]['maxtemp_bmp'],2),
            'avgtemp_bmp' => number_format($data[0]['avgtemp_bmp'],2),
            'summerdays' => $summerdays,
            'hotdays' => $hotdays,
            'desertdays' => $desertdays
            //'tropicalnights' => $tropicalnights_part1 + $tropicalnights_part2
        );

        return $datas;
    }

    private function _getNumOfDayDefs($date,$start,$end) {

        $days = 0;
        $days_per_month = $this->_getAvailableDaysPerMonth($date);

        foreach ($days_per_month as $day) {
            $temps = $this->_getDayDefinition(
                $date.'-'.$day['day'],
                $start,
                $end
            );

            if($temps >= 1) {
                $days = $days +1;
            }
        }

        return $days;
    }

    /**
     * Method for to get static vaules per months
     *
     * @param string $date example: '24-12-2017' (DD-MM-YYYY)
     * @return array
     */
    private function _getDayDefinition($date,$range_definition_start,$range_definition_end,$time_start='00:00:00',$time_end='23:59:59') {

        $db = $this->_repository->createQueryBuilder('r');
        $db->select('count(r.id)');

        $greater_date = date($date.' '.$time_start);
        $less_date = date($date.' '.$time_end);

        $db->setParameter('greaterdate', $greater_date);
        $db->setParameter('lessdate', $less_date);
        $db->where('
            r.tempBmp '.$range_definition_start.' AND
            r.tempDhtHic '.$range_definition_start.' AND
            r.tempBmp '.$range_definition_end.' AND
            r.tempDhtHic '.$range_definition_end.' AND
            r.addDatetime < :lessdate AND
            r.addDatetime > :greaterdate AND
            r.tempDhtHic < 60 AND
            r.tempBmp < 60 AND
            r.tempDhtHic > -50 AND
            r.tempBmp > -50
        ');


        return $db->getQuery()->getSingleScalarResult();
    }
}
