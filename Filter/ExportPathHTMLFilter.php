<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 07.08.12
 * Time: 08:24
 * To change this template use File | Settings | File Templates.
 */

namespace Terrific\ExporterBundle\Filter {
    use Symfony\Component\Finder\Finder;

    /**
     *
     */
    class ExportPathHTMLFilter
    {
        protected $appPath = "";

        /**
         * @param $appPath
         */
        public function setAppPath($appPath)
        {
            $this->appPath = $appPath;
        }

        /**
         * @return string
         */
        public function getAppPath()
        {
            return $this->appPath;
        }

        /**
         *
         */
        public function rebuildURL($in, $pattern, $extensions, $type)
        {
            $linksDone = array();

            $ret = $in;
            $matches = array();
            if (preg_match_all($pattern, $in, $matches) !== false) {

                for ($i = 0; $i < count($matches[0]); $i++) {
                    if (in_array($matches[1][$i], $linksDone)) {
                        continue;
                    }

                    $linksDone[] = $matches[1][$i];
                    $link = $this->clearLink($matches[1][$i]);

                    if ($this->validateExtension($link, $extensions)) {
                        $cStr = $matches[0][$i];

                        switch ($type) {
                            case "JS":
                                if (strpos($link, 'dependencies') !== false) {
                                    $nLink = "../js/dependencies/" . basename($link);
                                } else {
                                    $nLink = "../js/" . basename($link);
                                }
                                break;

                            case "CSS":
                                $nLink = "../css/" . basename($link);
                                break;

                            case "HTML":
                                $nLink = $this->rebuildImageLink($link);
                                break;
                        }

                        $ncStr = str_replace($matches[1][$i], $nLink, $cStr);
                        $ret = str_replace($cStr, $ncStr, $ret);
                    }
                }
            }

            return $ret;
        }


        /**
         * @param $nLink
         * @return string
         */
        protected function rebuildImageLink($nLink)
        {
            $basePath = "../img";

            switch (true) {
                case (strpos($nLink, '/terrificmodule') !== false):
                    $splInfo = $this->findFile($nLink);
                    $nLink = $basePath . "/" . $this->getModuleFromPath($splInfo->getPathname()) . "/" . basename($nLink);
                    break;

                case (strpos($nLink, 'img') !== false):
                    $nLink = $basePath . "/common/" . basename($nLink);
                    break;

                default:
                    break;
            }

            return $nLink;
        }


        /**
         * @param $path
         * @return null
         */
        public function getModuleFromPath($path)
        {
            $pos = strpos($path, "/Terrific/Module/");
            $module = substr($path, $pos + 17);

            $module = substr($module, 0, strpos($module, "/"));
            return $module;
        }

        /**
         * @param $link
         */
        protected function findFile($link)
        {
            $fileName = basename($link);

            $f = new Finder();
            $f->files()->in($this->appPath . "/web/img")->in($this->appPath . "/src/Terrific/Module")->depth("< 99");

            $ret = array_values(iterator_to_array($f->name($fileName)));
            if (count($ret) > 1) {
                throw new \Exception('Multiple pictures found using the same name.');
            } elseif (count($ret) == 0) {
                throw new \Exception("No picture found using the given name.");
            }

            return $ret[0];
        }


        /**
         * Clears a filepath from additional url parameters.
         *
         * @param String $link
         * @return String
         */
        protected function clearLink($link)
        {
            if (strpos($link, '?') !== false) {
                return substr($link, 0, strpos($link, '?'));
            }

            return $link;
        }

        /**
         * @param $link
         * @param array $allowedExtensions
         */
        protected function validateExtension($link, $allowedExtensions = array())
        {
            array_walk($allowedExtensions, function (&$item, $key) {
                $item = strtoupper($item);
            });

            $pInfo = pathinfo($link);
            if (isset($pInfo["extension"])) {
                return in_array(strtoupper($pInfo["extension"]), $allowedExtensions);
            }
            return false;
        }


        /**
         * @param $in
         */
        public function filterJS($in)
        {
            $ret = $this->rebuildURL($in, '#<script.*src=[\'"]([^"\']*)[\'"].*>#', array("JS"), "JS");

            return $ret;
        }

        /**
         * @param $in
         */
        public function filterCSS($in)
        {
            $ret = $this->rebuildURL($in, '#<link.*href=[\'"]([^"\']*)[\'"].*/>#', array("CSS"), "CSS");

            return $ret;
        }

        /**
         * @param $in
         */
        public function filterHTML($in)
        {
            $ret = $this->rebuildURL($in, '#data-[^=]*=[\'"]([^\'"]*)#', array("GIF", "PNG", "JPG"), "HTML");
            $ret = $this->rebuildURL($ret, '# src[= ]*[\'"]([^"\']*)#', array("GIF", "PNG", "JPG"), "HTML");

            return $ret;
        }

        /**
         *
         *
         * @param String $in
         * @return String
         */
        public function filter($in)
        {
            $ret = $in;
            $ret = $this->filterCSS($ret);
            $ret = $this->filterJS($ret);
            $ret = $this->filterHTML($ret);

            return $ret;
        }

        /**
         * @param $appPath
         */
        public function __construct($appPath)
        {
            $this->setAppPath($appPath);
        }
    }
}
