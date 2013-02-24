<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 15.12.12
 * Time: 12:33
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Object {
    use Terrific\ExporterBundle\Helper\FileHelper;

    /**
     *
     */
    class RouteModule {
        /** @var string */
        private $module;

        /** @var array */
        private $modules = array();

        /** @var string */
        private $template;

        /** @var array */
        private $skins;

        /** @var array */
        private $assets = array();

        /** @var array */
        private $connectors;

        /**
         * @param array $modules
         */
        public function setModules($modules)
        {
            $this->modules = $modules;
        }

        /**
         * @return array
         */
        public function getModules()
        {
            return $this->modules;
        }

        /**
         * @param $connector
         */
        public function isConnectedTo($connector) {
            return (in_array($connector, $this->connectors));
        }

        /**
         * @return array
         */
        public function getConnectors() {
            return $this->connectors;
        }

        /**
         *
         */
        public function getExportingPath() {
            $ret = $this->getTemplate(true);
            $ret .= (count($this->skins) > 0 ? "_" . implode("-", $this->skins) : "");
            $ret .= ".html";
            return $ret;
        }

        /**
         *
         */
        public function getUrl() {
            return rtrim(sprintf("/terrific/composer/module/details/%s/%s/%s", $this->module, $this->getTemplate(true), implode(",", $this->skins)), "/");
        }

        /**
         * @param array $assets
         */
        public function setAssets($assets) {
            $assets = array_unique($assets);
            $this->addAssets($assets);
        }

        /**
         * @return array
         */
        public function getAssets(array $types = array()) {
            $assets = $this->assets;

            // RouteModule in RouteModule
            /** @var $mod RouteModule */
            foreach($this->modules as $mod) {
                $assets = array_merge($assets, $mod->getAssets($types));
            }

            $assets = array_unique($assets);

            if (count($types) == 0) {
                return $assets;
            } else {
                array_walk($types, function ($itm) {
                    $itm = strtoupper($itm);
                    return $itm;
                });

                $searchJS = in_array("JS", $types);
                $searchCSS = in_array("CSS", $types);
                $searchIMG = in_array("IMG", $types);

                $ret = array();
                foreach ($assets as $asset) {
                    if (($searchJS && FileHelper::isJavascript($asset)) || ($searchCSS && FileHelper::isStylesheet($asset) || $searchIMG && FileHelper::isImage($asset))) {
                        $ret[] = $asset;
                    }
                }
                return $ret;
            }
        }


        /**
         * @return string
         */
        public function getModule() {
            return $this->module;
        }

        /**
         * @return array
         */
        public function getSkins() {
            return $this->skins;
        }

        /**
         * @return string
         */
        public function getTemplate($withoutExtension = false) {
            if (!$withoutExtension) {
                return $this->template;
            } else {
                return substr($this->template, 0, strpos($this->template, ".html.twig"));
            }
        }

        /**
         *
         */
        public function getId() {
            $id = strtolower($this->module) . "-" . strtolower($this->template) . strtolower(implode("-", $this->skins));
            return md5($id);
        }

        /**
         *
         */
        public function __toString() {
            return sprintf("RouteModule[%s::%s::%s]", $this->module, $this->template, implode(",", $this->skins));
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
         * @param string $module
         * @param string $template
         * @param array $skins
         */
        public function __construct($module, $template, array $skins, array $connectors) {
            $this->module = $module;
            $this->template = $template;
            $this->skins = $skins;
            $this->connectors = $connectors;
        }
    }
}
