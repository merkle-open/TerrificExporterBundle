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
    abstract class AbstractAction implements IAction
    {
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
        public function setContainer(ContainerInterface $container = null)
        {
            $this->container = $container;
        }

        /**
         * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
         * @return void
         */
        public function setLogger(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        /**
         * @param $directory
         * @return void
         */
        public function setWorkingDir($directory)
        {
            if (!file_exists($this->workingDir)) {
                $this->fs->mkdir($this->workingDir);
            }

            $this->workingDir = $directory;
        }

        /**
         * Constructor
         */
        public function __construct()
        {
            $this->fs = new Filesystem();
        }

    }
}
