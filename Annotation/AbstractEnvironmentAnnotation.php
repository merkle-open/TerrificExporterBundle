<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 28.12.12
 * Time: 11:15
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Annotation {

    /**
     *
     */
    abstract class AbstractEnvironmentAnnotation {

        /** @var array */
        protected $environment = array();

        /**
         * @return array
         */
        public function getEnvironment() {
            return $this->environment;
        }

        /**
         * @param String $env
         */
        public function setEnvironment($env) {
            $this->environment = explode(",", $env);
        }

        /**
         *
         * @param String $env
         * @return bool
         */
        public function matchEnvironment($env) {
            return ((count($this->environment) == 0) || in_array($env, $this->environment));
        }
    }
}
