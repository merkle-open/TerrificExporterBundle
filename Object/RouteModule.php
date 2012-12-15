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

        /** @var string */
        private $template;

        /** @var array */
        private $skins;

        /** @var array */
        private $assets;


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
            return sprintf("/terrific/composer/module/details/%s/%s/%s", $this->module, $this->getTemplate(true), implode(",", $this->skins));
        }

        /**
         * @param array $assets
         */
        public function setAssets($assets) {
            $this->assets = $assets;
            $this->assets = array_unique($this->assets);
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
         * @param string $module
         * @param string $template
         * @param array $skins
         */
        public function __construct($module, $template, array $skins) {
            $this->module = $module;
            $this->template = $template;
            $this->skins = $skins;
        }
    }
}
