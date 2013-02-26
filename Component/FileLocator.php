<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 26.02.13
 * Time: 10:41
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Component {

    class FileLocator extends \Symfony\Component\Config\FileLocator {


        /**
         * @param $path
         */
        public function addPath($path) {
            if (is_array($path)) {
                $path = array_filter($path, function ($itm) {
                    return file_exists($itm);
                });

                $this->paths = array_merge($this->paths, $path);
            } else if (file_exists($path)) {
                $this->paths[] = $path;
            }
        }

    }
}
