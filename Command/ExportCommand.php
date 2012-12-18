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
    use Terrific\ExporterBundle\Helper\TimerService;
    use Symfony\Component\Filesystem\Filesystem;
    use Terrific\ExporterBundle\Service\BuildOptions;
    use Terrific\ExporterBundle\Object\ActionRequirement;
    use Terrific\ExporterBundle\Helper\ProcessHelper;
    use Terrific\ExporterBundle\Object\ActionRequirementStack;
    use Terrific\ExporterBundle\Service\Log;

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
            $this->setName('build:export')->setDescription('Builds m release')->addOption('no-validation', null, InputOption::VALUE_OPTIONAL, "no build validation")->addOption('no-image-optimization', null, InputOption::VALUE_OPTIONAL, "Do not optimize images")->addOption('no-js-doc', null, InputOption::VALUE_OPTIONAL, 'Do not generate javascript doc')->addOption('export-lang', null, InputOption::VALUE_OPTIONAL, 'Used to export a specific language');
        }

        /**
         *
         */
        protected function retrieveActionStack(array $config) {
            $ret = array();

            if ($this->getContainer()->hasParameter("terrific_exporter.action_stack")) {

            } else {
                $ret[] = 'Terrific\ExporterBundle\Actions\ClearAction';
                $ret[] = 'Terrific\ExporterBundle\Actions\ValidateJS';
                $ret[] = 'Terrific\ExporterBundle\Actions\ValidateCSS';
                $ret[] = 'Terrific\ExporterBundle\Actions\ValidateModules';
                $ret[] = 'Terrific\ExporterBundle\Actions\ValidateViews';
                $ret[] = 'Terrific\ExporterBundle\Actions\GenerateSprites';
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportImages';
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportAssets';
                $ret[] = 'Terrific\ExporterBundle\Actions\OptimizeImages';
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportModules';
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportViews';
            }


            $requirementStack = new ActionRequirementStack();

            $stack = array();
            foreach ($ret as $action) {
                $refAction = new \ReflectionClass($action);

                if ($refAction->implementsInterface('Terrific\ExporterBundle\Actions\IAction')) {
                    /** @var $method \ReflectionMethod */
                    $method = $refAction->getMethod("getRequirements");

                    $requirementStack->addStacks($method->invoke(null));
                }
                $stack[] = $refAction;
            }


            $this->logger->debug("Checking Requirements");
            Log::info("Checking Requirements");
            Log::blkstart();

            $failedOverall = false;

            /** @var $req ActionRequirement */
            foreach ($requirementStack->getStack(true) as $req) {
                $failed = false;

                switch ($req->getType()) {
                    case ActionRequirement::TYPE_PHPEXT:
                        $failed = !extension_loaded($req->getName());
                        $str = "Check for PHP Extension [%s] => %s";
                        break;

                    case ActionRequirement::TYPE_PROCESS:
                        $failed = !ProcessHelper::checkCommand($req->getName());
                        $str = "Check for Command [%s] => %s";
                        break;

                    case ActionRequirement::TYPE_SETTING:
                        $failed = !(isset($config[$req->getName()]) && !empty($config[$req->getName()]));
                        $str = "Check for Setting [%s] => %s";
                        break;
                }


                $ret = (!$failed ? 'passed' : 'failed');
                $this->logger->debug(sprintf("- " . $str, $req->getName(), $ret));

                if ($failed) {
                    $failedOverall = true;
                    Log::err($str, array($req->getName(), $ret));

                    Log::blkstart();
                    Log::err("Used by the following Actions:");

                    /** @var $refAction \ReflectionClass */
                    foreach ($requirementStack->findAffectedActions($req) as $refAction) {
                        Log::err($refAction->getName(), array());
                    }

                    Log::blkend();
                } else {
                    Log::info($str, array($req->getName(), $ret));
                }
            }

            Log::blkend();

            if ($failedOverall) {
                Log::err("Cannot proceed with requirement errors");
                die();
            }

            $this->logger->debug("Retrieved actionstack:\n" . print_r($ret, true));
            return $stack;
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
                if ($action->isRunnable($params)) {
                    $action->setContainer($this->getContainer());

                    $this->logger->debug("Starting command with params: " . print_r($params, true));
                    Log::info("Start [" . $refClass->getShortName() . "]");

                    $ret = $action->run($output, $params);
                    return $ret;
                } else {
                    $this->logger->info("Skipped tasks '" . $refClass->getName() . "' during config settings.");
                }
            }

            return null;
        }


        /**
         * @param array $extend
         * @return array
         */
        protected function compileConfiguration(BuildOptions $buildOptions) {
            $ret = $this->getContainer()->getParameter("terrific_exporter");

            /** @var $fs Filesystem */
            $fs = $this->getContainer()->get("filesystem");

            if (!empty($ret["build_path"]) && $fs->isAbsolutePath($ret["build_path"])) {
                $ret["exportPath"] = $ret["build_path"];
            } else if (!empty($ret["build_path"])) {
                $ret["exportPath"] = realpath($this->getContainer()->getParameter("kernel.root_dir") . "/../" . $ret["build_path"]);
            }


            // append build options
            if (!empty($ret["build_settings"])) {
                $buildOptions->setFile($this->getContainer()->getParameter("kernel.root_dir") . "/../" . $ret["build_settings"]);

                $version = $buildOptions["version"];

                if ($ret["export_with_version"]) {
                    $ret["exportPath"] .= sprintf("/%s-%s.%s.%s", $version["name"], $version["major"], $version["minor"], $version["build"]);
                } else {
                    $ret["exportPath"] .= "/" . $version["name"];

                }
            }

            return $ret;
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

            $config = $this->compileConfiguration($buildOptions);
            $actionStack = $this->retrieveActionStack($config);

            $reRunTimer = array();
            for ($i = 0; $i < count($actionStack); $i++) {
                /** @var $refClass \ReflectionClass */
                $refClass = $actionStack[$i];
                $queueItem = $refClass->getName();

                if (!isset($reRunTimer[$queueItem])) {
                    $reRunTimer[$queueItem] = 0;
                }
                $timer->lap("START-" . $refClass->getShortName());

                $config["runnedTimer"] = $reRunTimer[$queueItem];
                $ret = $this->runAction($refClass, $output, $config);

                if ($ret instanceof ActionResult) {
                    switch ($ret->getResultCode()) {
                        case ActionResult::OK:
                            break;

                        case ActionResult::STOP:
                            Log::info("Stopping export after [" . $refClass->getShortName() . "] Action");
                            break 2;

                        case ActionResult::TRY_AGAIN:
                            $reRunTimer[$queueItem] += 1;
                            if ($reRunTimer[$queueItem] < 10) {
                                --$i;
                            } else {
                                $reRunTimer[$queueItem] = 0;
                                Log::err("Aborted after > 10 retries [" . $refClass->getShortName() . "] Action");
                            }
                            break;

                        default:
                            Log::warn("Retrieved unknown return from " . $refClass->getShortName() . " Code: " . $ret->getResultCode());
                            break;
                    }
                }

                $timer->lap("STOP-" . $refClass->getShortName());
            }


            for ($i = 0; $i < count($actionStack); $i++) {
                $refClass = $actionStack[$i];

                $msg = vsprintf("Action [%s] completed after %s seconds.", array($refClass->getName(), $timer->getTime("START-" . $refClass->getShortName(), "STOP-" . $refClass->getShortName())));
                if ($this->logger) {
                    $this->logger->info($msg);
                }

                Log::info($msg);
            }

            // stop timer
            $this->logger->info(sprintf("Export completed in %s seconds", $timer->stop()));

            if ($config["autoincrement_build"]) {
                $buildOptions["version.build"] = intval($buildOptions["version.build"]) + 1;
                $buildOptions->save();
            }
        }


    }
}
