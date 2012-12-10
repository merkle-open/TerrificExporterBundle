<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 24.06.12
 * Time: 08:04
 * To change this template use File | Settings | File Templates.
 */


namespace Terrific\ExporterBundle\Command {
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Actions\IAction;
    use Terrific\ExporterBundle\Actions\AbstractAction;

    /**
     *
     */
    class ExportCommand extends ContainerAwareCommand {
        /**
         * @var LoggerInterface
         */
        protected $logger;

        /**
         *
         */
        protected function configure() {
            $this->setName('build:export')->setDescription('Builds a release')->addOption('no-validation', null, InputOption::VALUE_OPTIONAL, "no build validation")->addOption('no-image-optimization', null, InputOption::VALUE_OPTIONAL, "Do not optimize images")->addOption('no-js-doc', null, InputOption::VALUE_OPTIONAL, 'Do not generate javascript doc')->addOption('export-lang', null, InputOption::VALUE_OPTIONAL, 'Used to export a specific language');
        }

        /**
         *
         */
        protected function retrieveActionStack() {
            $ret = array();

            if ($this->getContainer()->hasParameter("terrific_exporter.action_stack")) {

            } else {
#                $ret[] = 'Terrific\ExporterBundle\Actions\ValidateJS';
#                $ret[] = 'Terrific\ExporterBundle\Actions\ValidateCSS';
                $ret[] = 'Terrific\ExporterBundle\Actions\ValidateViews';
                $ret[] = 'Terrific\ExporterBundle\Actions\GenerateSprites';
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportAssets';
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportModules';
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportViews';
            }

            $this->logger->debug("Retrieved actionstack:\n" . print_r($ret, true));
            return $ret;
        }

        /**
         * Starts a ActionClass
         *
         * @param \ReflectionClass $refClass
         * @return ActionResult
         */
        protected function runAction(\ReflectionClass $refClass, OutputInterface $output, $params) {
            $action = $refClass->newInstance();

            if ($action instanceof AbstractAction) {
                $action->setLogger($this->logger);
            }


            if ($action instanceof IAction) {
                $action->setContainer($this->getContainer());
                $ret = $action->run($output, $params);

                return $ret;
            }


            return null;
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @return int|void
         */
        protected function execute(InputInterface $input, OutputInterface $output) {
            $this->logger = $this->getContainer()->get('logger');

            $actionStack = $this->retrieveActionStack();

            $reRunTimer = array();
            for ($i = 0; $i < count($actionStack); $i++) {
                $queueItem = $actionStack[$i];

                if (!isset($reRunTimer[$queueItem])) {
                    $reRunTimer[$queueItem] = 0;
                }
                $refClass = new \ReflectionClass($queueItem);

                $ret = $this->runAction($refClass, $output, array("runnedTimer" => $reRunTimer[$queueItem]));

                if ($ret instanceof ActionResult) {
                    switch ($ret->getResultCode()) {
                        case ActionResult::OK:
                            $output->writeln("Successfully ran [" . $refClass->getShortName() . "] Action");
                            break;

                        case ActionResult::STOP:
                            $output->writeln("Stopping export after [" . $refClass->getShortName() . "] Action");
                            break 2;

                        case ActionResult::TRY_AGAIN:
                            $reRunTimer[$queueItem] += 1;
                            if ($reRunTimer[$queueItem] < 10) {
                                --$i;
                            } else {
                                $reRunTimer[$queueItem] = 0;
                                $output->writeln("Aborted after > 10 retries [" . $refClass->getShortName() . "] Action");
                            }
                            break;

                        default:
                            $output->writeln("Retrieved from " . $refClass->getShortName() . " Code: " . $ret->getResultCode());
                            break;
                    }
                }
            }
        }


    }
}
