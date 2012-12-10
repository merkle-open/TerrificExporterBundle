<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 10:54
 * To change this template use File | Settings | File Templates.
 */

namespace Terrific\ExporterBundle\Object {
    use ReflectionMethod;

    /**
     *
     */
    class Route {

        /** @var String */
        private $url;

        /** @var ReflectionMethod */
        private $method;

        /** @var Boolean */
        private $exportable = false;

        /**
         * @return ReflectionMethod
         */
        public function getMethod() {
            return $this->method;
        }

        /**
         * @return String
         */
        public function getUrl() {
            return $this->url;
        }

        /**
         * @param boolean $exportable
         */
        public function setExportable($exportable) {
            $this->exportable = $exportable;
        }

        /**
         * @return boolean
         */
        public function isExportable() {
            return $this->exportable;
        }


        /**
         * @param $method
         * @return ReflectionMethod
         */
        protected function getReflectionMethod($methodString) {
            list($class, $method) = explode("::", $methodString);

            return new ReflectionMethod($class, $method);
        }


        /**
         * Constructor
         */
        public function __construct(\Symfony\Component\Routing\Route $route) {
            $this->url = $route->getPattern();

            $this->method = $this->getReflectionMethod($route->getDefault("_controller"));
        }
    }
}
