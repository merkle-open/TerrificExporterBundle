<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 03.01.13
 * Time: 10:56
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
    abstract class AbstractExporterCommand extends ContainerAwareCommand {
        /**
         * @var LoggerInterface
         */
        protected $logger;

        /*
         *
         */
        protected function checkRequirements(ActionRequirementStack $requirementStack) {
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
                throw new ExporterException("Cannot proceed with requirement errors");
            }
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
                    Log::blkstart();
                    $ret = $action->run($output, $params);
                    Log::blkend();
                    return $ret;
                } else {
                    Log::info("Skipped [" . $refClass->getShortName() . "]");
                    $this->logger->info("Skipped tasks '" . $refClass->getName() . "' during config settings.");
                }
            }

            return null;
        }


        /**
         * @param array $extend
         * @return array
         */
        protected function compileConfiguration(BuildOptions $buildOptions, InputInterface $input) {
            $ret = $this->getContainer()->getParameter("terrific_exporter");

            /** @var $fs Filesystem */
            $fs = $this->getContainer()->get("filesystem");

            /** @var $configFinder ConfigFinder */
            $configFinder = $this->getContainer()->get("terrific.exporter.configfinder");


            if (!empty($ret["build_path"]) && $fs->isAbsolutePath($ret["build_path"])) {
                $ret["exportPath"] = $ret["build_path"];
            } else if (!empty($ret["build_path"])) {
                $ret["exportPath"] = realpath($this->getContainer()->getParameter("kernel.root_dir") . "/../" . $ret["build_path"]);
            }


            // append build options
            if (!empty($ret["build_settings"])) {
                $file = $this->getContainer()->getParameter("kernel.root_dir") . "/../" . $ret["build_settings"];

                try {
                    if (!$fs->exists(dirname($file))) {
                        FileHelper::createPathRecursive(dirname($file));
                    }

                    if (!$fs->exists($file)) {
                        $defaultConfig = $configFinder->find("build.ini");
                        $fs->copy($defaultConfig, $file);
                        LOG::warn("Installed default buildOptions file [%s]", array(basename($file)));
                    }

                    $buildOptions->setFile($file);

                    $version = $buildOptions["version"];

                    if ($ret["export_with_version"]) {
                        $ret["exportPath"] .= sprintf("/%s-%s.%s.%s", $version["name"], $version["major"], $version["minor"], $version["build"]);
                    } else {
                        $ret["exportPath"] .= "/" . $version["name"];

                    }
                } catch (IOException $ex) {
                    throw $ex;
                }
            }

            // Setting console options
            if ($input->hasParameterOption("--no-validation")) {
                $ret["validate_js"] = false;
                $ret["validate_html"] = false;
                $ret["validate_css"] = false;
            }

            if ($input->hasParameterOption("--no-js-doc")) {
                $ret["build_js_doc"] = false;
            }

            if ($input->hasParameterOption("--no-image-optimization")) {
                $ret["optimize_images"] = false;
            }

            return $ret;
        }


        /**
         *
         */
        public function __construct() {
            parent::__construct();
        }
    }
}
