<?php
/*
 * Copyright (c) 2024. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace App\Service;

use App\Entity\Measurement;
use App\Repository\MeasurementsRepository;

class Measurements
{
    public function __construct(
        protected MeasurementsRepository $measurementsRepository
    )
    {
    }

    public function getCurrentValues(): Measurement
    {
        $db = $this->measurementsRepository->createQueryBuilder('m');
        $db->setMaxResults(1);
        $db->orderBy('m.id','desc');

        /** @var Measurement $test */
        $test = $db->getQuery()->getSingleResult();
        $test2 = $test->toArray();
        $addDatetime = $test->getAddDatetime();
        return $db->getQuery()->getSingleResult();
    }
}
