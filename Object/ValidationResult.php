<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 09.12.12
 * Time: 13:10
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Object {

    /**
     *
     */
    class ValidationResult {
        /**
         * @var array|null
         */
        private $results = null;


        /**
         *
         * @param ValidationResultItem $item
         * @return void
         */
        public function addResult(ValidationResultItem $item) {
            $this->results[] = $item;
        }

        /**
         * Returns true if the validation has a error.
         *
         * @return boolean
         */
        public function hasErrors() {
            $ret = false;

            foreach ($this->results as $item) {
                if ($item->isError()) {
                    $ret = true;
                    break;
                }
            }

            return $ret;
        }

        /**
         * Returns true if the validation has a warning.
         *
         * @return boolean
         */
        public function hasWarning() {
            $ret = false;

            foreach ($this->results as $item) {
                if ($item->isWarning()) {
                    $ret = true;
                    break;
                }
            }

            return $ret;
        }


        /**
         * Converts all results to output.
         *
         * @param $tpl String
         * @return String
         */
        public function toOutputString($tpl) {
            $ret = array();

            foreach ($this->results as $item) {
                $ret[] = $item->toOutputString($tpl);
            }

            return $ret;
        }


        /**
         *
         */
        public function __construct() {
            $this->results = array();
        }
    }
}
