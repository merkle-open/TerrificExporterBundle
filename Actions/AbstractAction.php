<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.11.12
 * Time: 23:54
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\DependencyInjection\ContainerAwareInterface;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;

    /**
     *
     */
    abstract class AbstractAction implements IAction {
        const LOG_LEVEL_INFO = 1;
        const LOG_LEVEL_WARN = 2;
        const LOG_LEVEL_ERROR = 3;
        const LOG_LEVEL_DEBUG = 4;


        /**
         * @var Array
         */
        protected $config;

        /**
         * @var ContainerInterface
         */
        protected $container;

        /**
         * @var String
         */
        protected $workingDir;

        /**
         * @var Symfony\Component\Filesystem\Filesystem
         */
        protected $fs;

        /**
         * @var LoggerInterface
         */
        protected $logger;

        /**
         * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
         * @return void
         */
        public function setContainer(ContainerInterface $container = null) {
            $this->container = $container;
        }

        /**
         * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
         * @return void
         */
        public function setLogger(LoggerInterface $logger) {
            $this->logger = $logger;
        }

        /**
         * @param $directory
         * @return void
         */
        public function setWorkingDir($directory) {
            if (!file_exists($this->workingDir)) {
                $this->fs->mkdir($this->workingDir);
            }

            $this->workingDir = $directory;
        }

        /**
         * Logs a Message.
         *
         * @param $lvl Integer
         * @param $msg String
         * @return void
         */
        public function log($lvl, $msg, $ctx = array()) {
            if ($this->logger == null) {
                return;
            }

            switch ($lvl) {
                case self::LOG_LEVEL_INFO:
                    $this->logger->info($msg, $ctx);
                    break;

                case self::LOG_LEVEL_WARN:
                    $this->logger->warn($msg, $ctx);
                    break;

                case self::LOG_LEVEL_ERROR:
                    $this->logger->err($msg, $ctx);
                    break;

                case self::LOG_LEVEL_DEBUG:
                    $this->logger->debug($msg, $ctx);
                    break;
            }
        }

        /**
         * Constructor
         */
        public function __construct() {
            $this->fs = new Filesystem();
        }

    }
}
