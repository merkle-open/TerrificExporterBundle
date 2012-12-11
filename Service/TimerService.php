<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 11.12.12
 * Time: 10:17
 * To change this template use File | Settings | File Templates.
 */


namespace Terrific\ExporterBundle\Service {
    /**
     *
     */
    class TimerService {

        /** @var array */
        private $snapshots = array();


        /**
         * @param $name
         */
        public function lap($name = null) {
            if ($name == null) {
                $name = substr(md5(microtime()), 0, 5);
            }

            $this->snapshots[$name] = microtime(true);
            return $name;
        }

        /**
         * @param string $startPoint
         * @param string $endPoint
         */
        public function getTime($startPoint = "START", $endPoint = "STOP") {

            $end = $this->snapshots[$endPoint];
            $start = $this->snapshots[$startPoint];

            return sprintf("%.3f", ($end - $start));
        }


        /**
         *
         */
        public function start() {
            $this->lap("START");
        }


        /**
         *
         */
        public function stop() {
            $this->lap("STOP");

            return $this->getTime();
        }

        /**
         *
         */
        public function __construct() {
        }
    }
}
