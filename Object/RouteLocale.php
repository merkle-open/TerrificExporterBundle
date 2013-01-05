<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 28.12.12
 * Time: 11:12
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Object {

    /**
     *
     */
    class RouteLocale {

        /** @var String */
        private $exportName;

        /** @var String */
        private $locale;

        /**
         * @param String $locale
         */
        public function setLocale($locale) {
            $this->locale = $locale;
        }

        /**
         * @return String
         */
        public function getLocale() {
            return $this->locale;
        }

        /**
         * @param string $exportName
         */
        public function setExportName($exportName) {
            $this->exportName = $exportName;
        }

        /**
         * @return string
         */
        public function getExportName() {
            return $this->exportName;
        }

        /**
         * @param $locale
         * @param $name
         */
        public function __construct($locale, $exportName) {
            $this->exportName = $exportName;
            $this->locale = $locale;
        }
    }
}
