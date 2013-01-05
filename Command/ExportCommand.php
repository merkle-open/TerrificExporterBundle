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
    use Terrific\ExporterBundle\Service\ConfigFinder;
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Exception\ExporterException;

    /**
     *
     */
    class ExportCommand extends AbstractExporterCommand {

        /**
         *
         */
        protected function configure() {
            $this->setName('build:export')->setDescription('Builds m release');
            $this->addOption('no-validation', null, InputOption::VALUE_OPTIONAL, "no build validation");
            $this->addOption('no-image-optimization', null, InputOption::VALUE_OPTIONAL, "Do not optimize images");
            $this->addOption('no-js-doc', null, InputOption::VALUE_OPTIONAL, 'Do not generate javascript doc');
            $this->addOption("last-export", null, InputOption::VALUE_OPTIONAL, 'Folder to the last export to build a diff between the current and the old.');
        }

        /**
         *
         */
        protected function retrieveActionStack(array $config) {
            $ret = array();

            if (!empty($config["build_actions"]) && is_array($config["build_actions"])) {
                $ret = $config["build_actions"];
            } else {
                $ret[] = 'Terrific\ExporterBundle\Actions\ClearAction';
                $ret[] = 'Terrific\ExporterBundle\Actions\BuildJSDoc';
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
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportChangelogs';
                $ret[] = 'Terrific\ExporterBundle\Actions\ExportDiffs';
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

            $this->checkRequirements($requirementStack);

            $this->logger->debug("Retrieved actionstack:\n" . print_r($ret, true));
            return $stack;
        }

<<<<<<< HEAD
    private function findLocationsToUrl($url)
    {
        $eList = $this->getContainer()->getParameter('terrific_exporter.layout_export_list');

        foreach ($eList as $k => $v) {
            if ($v["url"] == $url) {
                return $v["locations"];
            }
        }
    }

    private function backupLocales()
    {
        return array(
            $this->getContainer()->get("session")->getLocale(),
            $this->getContainer()->get("translator")->getLocale()
        );
    }

    private function restoreLocales($locales)
    {
        $this->getContainer()->get("session")->setLocale($locales[0]);
        $this->getContainer()->get("translator")->setLocale($locales[1]);
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function exportLayouts(InputInterface $input, OutputInterface $output)
    {
        $exportFilter = $this->getContainer()->get('terrific.exporter.filter.html');
        $pageManager = $this->getContainer()->get("terrific.composer.page.manager");
        $http = $this->getContainer()->get("http_kernel");
=======
>>>>>>> refactored

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @return int|void
         */
        protected function execute(InputInterface $input, OutputInterface $output) {
            // Init console logger !
            Log::init($output);

            $this->logger = $this->getContainer()->get('logger');

<<<<<<< HEAD
        $localeBackup = $this->backupLocales();

        foreach ($pageManager->getPages() as $page) {
            if ($exportList == null || in_array($page->getUrl(), $exportList)) {
                $locales = $this->findLocationsToUrl($page->getUrl());

                if (count($locales) == 0) {
                    $urlList = array($page->getUrl());
                } else {
                    $urlList = array();
                    foreach ($locales as $loc) {
                        $urlList[$loc] = $page->getUrl() . "/" . $loc;
                    }
                }


                foreach ($urlList as $loc => $url) {
                    $tplName = $page->getName();
                    if (is_string($loc)) {
                        $tplName .= "_" . $loc;
                        $this->restoreLocales(array($loc, $loc));
                    }

                    $request = Request::create($url);
                    $resp = $http->handle($request);
                    $ret = $resp->getContent();

                    if ($this->getContainer()->getParameter('terrific_exporter.build_local_paths')) {
                        $ret = $exportFilter->filter($ret);
                    }

                    file_put_contents($tempPath . "/" . $tplName . ".html", $ret);
                    $output->writeln("  " . $this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Built layout " . $tplName));
                }


            }
        }

        $this->restoreLocales($localeBackup);
    }
=======
            /** @var $timer TimerService */
            $timer = $this->getContainer()->get("terrific.exporter.timerservice");

            /** @var $buildOptions BuildOptions */
            $buildOptions = $this->getContainer()->get("terrific.exporter.build_options");

>>>>>>> refactored

            // startup timer
            $timer->start();

            try {
                $config = $this->compileConfiguration($buildOptions, $input);
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


<<<<<<< HEAD
            $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Start Asset export"));
            #$returnCode = $command->run($cmdInput, $output);
            $returnCode = $command->run($cmdInput, new \Terrific\ExporterBundle\Service\EmptyOutput());


            $tempPath = $this->buildTempPath(true);

            //
            // build sprites
            //
            if ($this->getContainer()->getParameter('terrific_exporter.build_sprites')) {
                $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Build Sprites"));
                $command = $this->getApplication()->find('build:sprites');
                $returnCode = $command->run($cmdInput, $output);
            }

            $this->getContainer()->enterScope('request');

            if ($input->getOption("export-lang") != "") {
                $this->getContainer()->get("session")->setLocale($input->getOption("export-lang"));
                $this->getContainer()->get("translator")->setLocale($input->getOption("export-lang"));
            }

            $this->exportAssets($input, $output);

            if ($this->getContainer()->getParameter('terrific_exporter.export_modules')) {
                $this->exportModules($input, $output);
            }

            if ($this->getContainer()->getParameter('terrific_exporter.export_layouts')) {
                $this->exportLayouts($input, $output);
            }

            $this->getContainer()->leaveScope('request');


            if ($this->getContainer()->getParameter('terrific_exporter.export_rewrite_routes')) {
                $output->writeln($this->getMessage(AbstractCommand::MSG_LEVEL_INFO, "Output router rules"));
                $sOutput = new StreamOutput(fopen($tempPath . "/.htaccess", "w"));
                $command = $this->getApplication()->find('router:dump-apache');
                $returnCode = $command->run($cmdInput, $sOutput);
            }
=======
                for ($i = 0; $i < count($actionStack); $i++) {
                    $refClass = $actionStack[$i];
>>>>>>> refactored

                    $msg = vsprintf("Action [%s] completed after %s seconds.", array($refClass->getName(), $timer->getTime("START-" . $refClass->getShortName(), "STOP-" . $refClass->getShortName())));
                    if ($this->logger) {
                        $this->logger->info($msg);
                    }

                    Log::info($msg);
                }

                if (!empty($config["export_type"]) && strtolower($config["export_type"]) === "zip") {
                    $file = FileHelper::buildZip($config["exportPath"], null, true);
                    Log::info(sprintf("Built zipfile [%s]", basename($file)));
                }


                // stop timer
                $this->logger->info(sprintf("Export completed in %s seconds", $timer->stop()));

                if ($config["autoincrement_build"]) {
                    $buildOptions["version.build"] = intval($buildOptions["version.build"]) + 1;
                    $buildOptions->save();
                }
            } catch (ExporterException $ex) {
                $this->logger->err($ex->getMessage());
                $this->logger->debug($ex->getTraceAsString());
                throw $ex;
            }
        }

    }
}
