<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Annotation {


    /**
     * Export annotation.
     *
     * @Annotation
     */
    class Export {

        /** @var array */
        private $environment = array();

        /** @var string */
        private $name = "";

        /** @var array */
        private $locales = array();

        /**
         * @return array
         */
        public function getLocales() {
            return $this->locales;
        }

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
         * @return string
         */
        public function getName() {
            return $this->name;
        }

        /**
         * @param String $name
         */
        public function setName($name) {
            if (is_string($name)) {
                $this->name = $name;
            } else {
                $this->locales = $name;
            }
        }

        /**
         * @param bool $env
         */
        public function matchEnvironment($env) {
            return ((count($this->environment) == 0) || in_array($env, $this->environment));
        }

        /**
         *
         */
        public function __construct(array $data) {
            if (isset($data['value'])) {
                $data['name'] = $data['value'];
                unset($data['value']);
            }

            foreach ($data as $key => $value) {
                $method = 'set' . $key;
                if (!method_exists($this, $method)) {
                    throw new \BadMethodCallException(sprintf("Unknown property '%s' on annotation '%s'.", $key, get_class($this)));
                }
                $this->$method($value);
            }
        }
    }
}
