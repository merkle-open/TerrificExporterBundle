<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 12.11.12
 * Time: 14:15
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Assetic\Factory\LazyAssetManager;
    use Symfony\Component\Process\ProcessBuilder;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Helper\ProcessHelper;
    use Terrific\ExporterBundle\Object\ValidationResult;
    use Terrific\ExporterBundle\Object\ValidationResultItem;
    use Terrific\ExporterBundle\Service\ConfigFinder;
    use Terrific\ExporterBundle\Helper\XmlLintHelper;
    use Terrific\ExporterBundle\Helper\AsseticHelper;
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Service\PageManager;

    /**
     *
     */
    class ValidateJS extends AbstractAction implements IAction {

        /**
         * @var array
         */
        private $validatedScripts = null;

        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            $ret = true;
            $ret &= $params["validate_js"];
            $ret &= $params["export_assets"];
            return $ret;
        }

        /**
         *
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            if (!ProcessHelper::checkCommand('jshint')) {
                $this->log(AbstractAction::LOG_LEVEL_ERROR, "Cannot find JSHint.");
                return new ActionResult(ActionResult::STOP);
            }

            $error = false;
            $this->log(AbstractAction::LOG_LEVEL_DEBUG, "Found JSHint starting validation.");

            /** @var $assetManager LazyAssetManager */
            $assetManager = $this->container->get("assetic.asset_manager");

            /** @var $tmpFileMgr TempFileManager */
            $tmpFileMgr = $this->container->get("terrific.exporter.tempfilemanager");

            /** @var $configFinder ConfigFinder */
            $configFinder = $this->container->get("terrific.exporter.configfinder");
            $config = $configFinder->find("jshint.json");

            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.pagemanager");

            // retrieve only assets matching the current export
            $assetList = $pageManager->retrieveAllAssets(true);

            /** @var $timer TimerService */
            $timer = $this->container->get("terrific.exporter.timerservice");


            foreach ($assetManager->getNames() as $name) {
                $asset = $assetManager->get($name);

                if (FileHelper::isJavascript($asset->getTargetPath()) && in_array($asset->getTargetPath(), $assetList)) {
                    $this->log(AbstractAction::LOG_LEVEL_INFO, "Starting Validation of parts for " . basename($asset->getTargetPath()));

                    foreach ($assetManager->get($name) as $origLeaf) {
                        $leafPath = realpath($origLeaf->getSourceRoot() . "/" . $origLeaf->getSourcePath());

                        if ($leafPath !== false && !in_array($leafPath, $this->validatedScripts)) {
                            $leaf = AsseticHelper::removeMinFilters($origLeaf);

                            $content = $leaf->dump();
                            $file = $tmpFileMgr->putContent($content);

                            $ret = ProcessHelper::startCommand("jshint", array("--jslint-reporter", "--config", $config, $file));
                            $this->validatedScripts[] = $leafPath;

                            $parseRet = XmlLintHelper::parseXML($ret->getOutput());

                            if ($parseRet instanceof \Exception) {
                                $this->log(AbstractAction::LOG_LEVEL_ERROR, "Cannot parse JSHint output.");
                                $this->log(AbstractAction::LOG_LEVEL_ERROR, $parseRet->getMessage());
                                $this->log(AbstractAction::LOG_LEVEL_ERROR, $ret->getCommandLine());
                                $this->log(AbstractAction::LOG_LEVEL_ERROR, $ret->getErrorOutput());
                                $error = true;
                            } else {
                                // OUT
                                $error = $parseRet->hasErrors();

                                $results = $parseRet->toOutputString('[%1$s : %2$s] %3$s');
                                foreach ($results as $item) {
                                    $this->log(AbstractAction::LOG_LEVEL_DEBUG, "--- " . $item);
                                }

                                $output->writeln(sprintf("Validated %s Found %d Issues.", basename($leafPath), count($results)));
                            }

                        }
                    }
                }
            }

            if ($error) {
                return new ActionResult(ActionResult::STOP);
            }

            return new ActionResult(ActionResult::OK);
        }


        /**
         * Constructor
         */
        public function __construct() {
            parent::__construct();

            $this->validatedScripts = array();
        }
    }
}
