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
         * Find a specific configuration file.
         *
         * @throws InvalidArgumentException
         * @param String $filename
         * @return String
         */
        public function find($filename) {
            $this->initialize();

            $file = $this->fileLocator->locate($filename);
            return realpath($file);

        }


        /**
         *
         */
        protected function initialize() {
            if ($this->fileLocator != null) {
                return;
            }

            $paths = array();
            $paths[] = $this->getWorkingPath() . "/app/config";
            $paths[] = __DIR__ . "/../Resources/config/";


            $this->fileLocator = new FileLocator($paths);
        }

        /**
         * Constructor
         */
        public function __construct() {
        }
    }
}
