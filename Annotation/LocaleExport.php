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
    class LocaleExport extends AbstractEnvironmentAnnotation {

        /** @var string */
        private $name = "";

        /** @var string */
        private $locale = "";

        /**
         * @param string $locale
         */
        public function setLocale($locale) {
            $this->locale = $locale;
        }

        /**
         * @return string
         */
        public function getLocale() {
            return $this->locale;
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
            $this->name = $name;
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
