<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 09.12.12
 * Time: 15:05
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Service {
    use Symfony\Component\Config\FileLocator;

    /**
     *
     */
    class ConfigFinder {

        /**
         * @var String
         */
        private $workingPath;

        /**
         * @var FileLocator
         */
        private $fileLocator = null;

        /**
         * @param String $workingPath
         */
        public function setWorkingPath($workingPath) {
            $this->workingPath = $workingPath;
        }

        /**
         * @return String
         */
        public function getWorkingPath() {
            return $this->workingPath;
        }

        /**
         * @param $filename
         */
        public function find($filename) {
            $this->initialize();

            try {
                $file = $this->fileLocator->locate($filename);
            } catch (\Exception $ex) {
                var_dump($ex->getMessage());
            }
        }


        /**
         *
         */
        protected function initialize() {
            if ($this->fileLocator != null) {
                return;
            }

            $paths = array();
            $paths[] = __DIR__ . "/../Resources/config/";
            $paths[] = $this->getWorkingPath() . "/app/config";

            $this->fileLocator = new FileLocator($paths);
        }

        /**
         * Constructor
         */
        public function __construct() {
        }
    }
}
