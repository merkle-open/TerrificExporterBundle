<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 09.12.12
 * Time: 13:12
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Object {

    /**
     *
     */
    class ValidationResultItem {

        /**
         * @var boolean
         */
        private $isError = false;

        /**
         * @var boolean
         */
        private $isWarning = false;

        /**
         * @var int
         */
        private $line;

        /**
         * @var int
         */
        private $char;

        /**
         * @var string
         */
        private $description;


        /**
         * @param bool s$warning
         */
        public function setWarning($warning) {
            $this->isWarning = $warning;
        }

        /**
         * @param bool $error
         */
        public function setError($error) {
            $this->isError = $error;
        }

        /**
         * Returns true if this item is a error.
         *
         * @return bool
         */
        public function isError() {
            return $this->isError;
        }

        /**
         * Returns true if this item is a warning.
         *
         * @return bool
         */
        public function isWarning() {
            return $this->isWarning;
        }

        /**
         * @param int $char
         */
        public function setChar($char) {
            $this->char = $char;
        }

        /**
         * @return int
         */
        public function getChar() {
            return $this->char;
        }

        /**
         * @param string $description
         */
        public function setDescription($description) {
            $this->description = $description;
        }

        /**
         * @return string
         */
        public function getDescription() {
            return $this->description;
        }

        /**
         * @param int $line
         */
        public function setLine($line) {
            $this->line = $line;
        }

        /**
         * @return int
         */
        public function getLine() {
            return $this->line;
        }

        /**
         * Converts this object to an array entry.
         *
         * return Array
         */
        public function toArray() {
            return array("line" => $this->line, "char" => $this->char, "description" => $this->description);
        }

        /**
         * Converts the item to a string.
         *
         * @param $tpl String
         * @return String
         */
        public function toOutputString($tpl) {
            return vsprintf($tpl, array($this->line, $this->char, $this->description));
        }

        /**
         * Constructor
         *
         * @param $description String
         * @param $line Integer
         * @param $char Integer
         */
        public function __construct($description, $line = -1, $char = -1) {
            $this->setDescription($description);
            $this->setLine($line);
            $this->setChar($char);
        }
    }
}
