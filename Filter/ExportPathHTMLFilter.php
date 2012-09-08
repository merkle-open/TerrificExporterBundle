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
        protected function getModuleFromPath($path)
        {
            $matches = array();
            if (preg_match('/\/Terrific\/Module\/([^\/]+)\//', $path, $matches) !== false) {
                return $matches[1];
            }

            return null;
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
            if (count($ret) != 1) {
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
            array_walk($allowedExtensions, function(&$item, $key)
            {
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
            $ret = $in;
            $matches = array();
            if (preg_match_all('#<script.*src=[\'"]([^"\']*)[\'"].*>#', $in, $matches) !== false) {

                for ($i = 0; $i < count($matches[0]); $i++) {
                    $nLink = $this->clearLink($matches[1][$i]);

                    if ($this->validateExtension($nLink, array('JS'))) {
                        if (strpos($nLink, 'dependencies') !== false) {
                            $nLink = "../js/dependencies/" . basename($nLink);
                        } else {
                            $nLink = "../js/" . basename($nLink);
                        }

                        $nLink = str_replace($matches[1][$i], $nLink, $matches[0][$i]);
                        $ret = str_replace($matches[0][$i], $nLink, $ret);
                    }
                }
            }
            return $ret;
        }

        /**
         * @param $in
         */
        public function filterCSS($in)
        {
            $ret = $in;
            $matches = array();
            if (preg_match_all('#<link.*href=[\'"]([^"\']*)[\'"].*/>#', $in, $matches) !== false) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $link = $this->clearLink($matches[1][$i]);

                    if ($this->validateExtension($link, array('CSS'))) {
                        $cStr = $matches[0][$i];
                        $ncStr = str_replace($matches[1][$i], '../css/' . basename($link), $cStr);
                        $ret = str_replace($cStr, $ncStr, $ret);
                    }
                }
            }
            return $ret;
        }

        /**
         * @param $in
         * @return mixed
         */
        public function filterDATA($in)
        {
            return $in;
        }

        /**
         * @param $in
         */
        public function filterHTML($in)
        {

            $ret = $in;
            $matches = array();
            if (preg_match_all('#src[= ]*[\'"]([^"\']*)#', $in, $matches) !== false) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $nLink = $matches[1][$i];
                    $nLink = $this->clearLink($nLink);

                    if ($this->validateExtension($nLink, array('GIF', 'PNG', 'JPG'))) {
                        $nLink = $this->rebuildImageLink($nLink);
                        $nRet = str_replace($matches[1][$i], $nLink, $matches[0][$i]);
                        $ret = str_replace($matches[0][$i], $nRet, $ret);
                    }
                }
            }


            $matches = array();
            if (preg_match_all('#data-[^=]*=[\'"]([^\'"]*)#', $ret, $matches) !== false) {

                for ($i = 0; $i < count($matches[0]); $i++) {
                    $nLink = $matches[1][$i];
                    $nLink = $this->clearLink($nLink);

                    if ($this->validateExtension($nLink, array('GIF', 'PNG', 'JPG'))) {
                        $nLink = $this->rebuildImageLink($nLink);

                        $nRet = str_replace($matches[1][$i], $nLink, $matches[0][$i]);
                        $ret = str_replace($matches[0][$i], $nRet, $ret);
                    }
                }

            }

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
            $ret = $this->filterDATA($ret);
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
