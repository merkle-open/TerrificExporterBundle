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
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Object\RouteModule;

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

        /** @var array */
        private $assets = array();

        /** @var string */
        private $template = "";

        /** @var string */
        private $exportName = "";

        /** @var array */
        private $urlParameters = array();

        /** @var array */
        private $modules = array();


        /**
         * @return array
         */
        public function getUrlParameters() {
            return $this->urlParameters;
        }

        /**
         * @param string $template
         */
        public function setTemplate($template) {
            $this->template = $template;
        }

        /**
         * @return string
         */
        public function getTemplate() {
            return $this->template;
        }

        /**
         * @return \ReflectionMethod
         */
        public function getMethod() {
            return $this->method;
        }

        /**
         * @return String
         */
        public function getUrl(array $params = array()) {
            return $this->buildUrl($params);
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
         * @return array
         */
        public function getAssets(array $types = array()) {
            if (count($types) == 0) {
                return $this->assets;
            } else {
                array_walk($types, function ($itm) {
                    $itm = strtoupper($itm);
                    return $itm;
                });

                $searchJS = in_array("JS", $types);
                $searchCSS = in_array("CSS", $types);
                $searchIMG = in_array("IMG", $types);

                $ret = array();
                foreach ($this->assets as $asset) {
                    if (($searchJS && FileHelper::isJavascript($asset)) || ($searchCSS && FileHelper::isStylesheet($asset) || $searchIMG && FileHelper::isImage($asset))) {
                        $ret[] = $asset;
                    }
                }
                return $ret;
            }
        }

        /**
         * @return array
         */
        public function getModules() {
            return $this->modules;
        }

        /**
         * @return bool
         */
        public function hasUrlParameters() {
            return (count($this->urlParameters) > 0);
        }

        /**
         *
         * @param array $values
         * @return string
         * @throws \InvalidArgumentException
         */
        protected function buildUrl(array $values) {
            $url = $this->url;

            foreach ($this->urlParameters as $param) {
                if (!isset($values[$param])) {
                    throw new \InvalidArgumentException("Option '${$param}' not found in the given parameter values.");
                }

                $url = str_replace('{' . $param . '}', $values[$param], $url);
            }

            return rtrim($url, "/");
        }


        /**
         * @param $asset
         */
        public function addAssets(array $assets) {
            $ret = array();

            foreach ($assets as $a) {
                if ($a instanceof RouteModule) {
                    $this->addModule($a);
                } else {
                    $this->assets[] = $a;
                }
            }

            $this->assets = array_unique($this->assets);
        }

        /**
         * @param $module
         */
        public function addModule(RouteModule $module) {
            $this->modules[$module->getId()] = $module;
        }

        /**
         * @param $param
         */
        public function addUrlParameter($param) {
            $this->urlParameters[] = $param;
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
