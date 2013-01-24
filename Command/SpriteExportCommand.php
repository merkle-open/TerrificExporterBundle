<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 24.06.12
 * Time: 08:04
 * To change this template use File | Settings | File Templates.
 */


namespace Terrific\ExporterBundle\Command {
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Input\InputInterface;
    use Terrific\ExporterBundle\Service\Log;

    /**
     *
     */
    class SpriteExportCommand extends ExportCommand {

        /**
         *
         */
        protected function configure() {
            $this->setName('build:sprite')->setDescription('Creates all Sprites configured in the given context.');
        }

        /**
         *
         */
        protected function retrieveActionStack(array $config, InputInterface $input) {
            $actions = array("build_actions" => array('Terrific\ExporterBundle\Actions\GenerateSprites'));

            return parent::retrieveActionStack($actions, $input);
        }


        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @return int|void
         */
        protected function execute(InputInterface $input, OutputInterface $output) {
            // Init console logger !
            Log::init($output);

            $this->logger = $this->getContainer()->get('logger');

            /** @var $timer TimerService */
            $timer = $this->getContainer()->get("terrific.exporter.timerservice");

            /** @var $buildOptions BuildOptions */
            $buildOptions = $this->getContainer()->get("terrific.exporter.build_options");

            // startup timer
            $timer->start();

            try {
                $config = $this->compileConfiguration($buildOptions, $input);
                $actionStack = $this->retrieveActionStack($config, $input);
                $this->runChain($timer, $output, $actionStack, $config);
                $this->printTimings($timer, $actionStack);

                // stop timer
                $this->logger->info(sprintf("Building Sprites completed in %s seconds", $timer->stop()));
            } catch (ExporterException $ex) {
                $this->logger->err($ex->getMessage());
                $this->logger->debug($ex->getTraceAsString());
                throw $ex;
            }
        }
    }
}
