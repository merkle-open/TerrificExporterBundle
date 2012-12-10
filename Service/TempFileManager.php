<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 08:36
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Service {
    use Symfony\Component\Filesystem\Exception\IOException;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;


    /**
     *
     */
    class TempFileManager {

        /**
         * @var String
         */
        private $tempDir = null;

        /**
         * @var array
         */
        private $savedTempFiles = null;

        /**
         * @var \Symfony\Component\Filesystem\Filesystem
         */
        private $fs;

        /**
         * @var LoggerInterface
         */
        private $logger;

        /**
         * @param String $tempDir
         */
        public function setTempDir($tempDir) {
            if ($this->logger != null) {
                $this->logger->debug("Set tempd dir to " . $tempDir);
            }
            $this->tempDir = $tempDir;
        }

        /**
         * @return String
         */
        public function getTempDir() {
            return $this->tempDir;
        }

        /**
         * @return array
         */
        public function getSavedTempFiles() {
            return $this->savedTempFiles;
        }

        /**
         * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
         */
        public function setLogger($logger) {
            $this->logger = $logger;
        }

        /**
         * @return \Symfony\Component\HttpKernel\Log\LoggerInterface
         */
        public function getLogger() {
            return $this->logger;
        }


        /**
         * Returns a random generated filename.
         *
         * @return String
         */
        public function generateFileName() {
            return substr(md5(microtime()), 0, 5);
        }

        /**
         * Generates a new file with the given content. Returns the name of the generated file.
         *
         * @throws IOException
         * @param String $content
         * @return String
         */
        public function putContent($content) {
            $filename = $this->generateFileName();
            $path = $this->tempDir . "/" . $filename;

            $ret = file_put_contents($path, $content);

            if ($ret === false) {
                if ($this->logger != null) {
                    $this->logger->err(sprintf("Cannot create file '%s'", $path));
                }
                throw new IOException(sprintf("Cannot create file '%s'", $path));
            }

            $this->savedTempFiles[] = $path;

            if ($this->logger != null) {
                $this->logger->debug("Created temp file " . $path);
            }
            return $path;
        }


        /**
         * Removes all created temp files during uninitialization.
         *
         * @return void
         */
        public function shutdown() {
            if ($this->fs->exists($this->savedTempFiles)) {
                if ($this->logger != null) {
                    $this->logger->debug("Removing Files: ");

                    foreach ($this->savedTempFiles as $file) {
                        $this->logger->debug("- " . $file);
                    }
                }
                $this->fs->remove($this->savedTempFiles);
            } else {
                throw new IOException("Set of temp files were changed during runtime.");
            }

            $this->savedTempFiles = array();
        }


        /**
         *
         */
        public function __destruct() {
            $this->shutdown();
        }

        /**
         * Constructor
         */
        public function __construct() {
            $this->tempDir = sys_get_temp_dir();
            $this->savedTempFiles = array();
            $this->fs = new Filesystem();
        }
    }
}

