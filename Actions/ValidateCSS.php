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
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Helper\ProcessHelper;
    use Terrific\ExporterBundle\Helper\AsseticHelper;
    use Terrific\ExporterBundle\Object\ValidationResult;
    use Terrific\ExporterBundle\Object\ValidationResultItem;
    use Terrific\ExporterBundle\Service\ConfigFinder;
    use Terrific\ExporterBundle\Helper\XmlLintHelper;
    use Terrific\ExporterBundle\Service\TempFileManager;
    use Terrific\ExporterBundle\Service\PageManager;
    use Assetic\Asset\FileAsset;
    use Terrific\ExporterBundle\Object\ActionRequirement;
    use Terrific\ExporterBundle\Service\Log;


    /**
     *
     */
    class ValidateCSS extends AbstractValidateAction implements IAction {
        /**
         * Returns requirements for running this Action.
         *
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @param array $runnedActions
         * @return array
         */
        public static function getRequirements() {
            $ret = array();

            $ret[] = new ActionRequirement("csslint", ActionRequirement::TYPE_PROCESS, 'Terrific\ExporterBundle\Actions\ValidateCSS');

            return $ret;
        }

        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return isset($params["validate_css"]) && $params["validate_css"];
        }

        /**
         * @param $file
         */
        private function prepareConfigurationData($file) {
            $ini = parse_ini_file($file);

            $ret = array();
            if (isset($ini["ERRORS"])) {
                $ret[] = "--errors=" . $ini["ERRORS"];
            }

            if (isset($ini["WARNINGS"])) {
                $ret[] = "--warnings=" . $ini["WARNINGS"];
            }

            if (isset($ini["IGNORE"])) {
                $ret[] = "--ignore=" . $ini["IGNORE"];
            }

            $ret[] = "--format=lint-xml";

            return $ret;
        }

        /**
         *
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            $error = false;
            $this->log(AbstractAction::LOG_LEVEL_DEBUG, "Found CSSLint starting validation.");

            /** @var $assetManager LazyAssetManager */
            $assetManager = $this->container->get("assetic.asset_manager");

            /** @var $tmpFileMgr TempFileManager */
            $tmpFileMgr = $this->container->get("terrific.exporter.tempfilemanager");

            /** @var $configFinder ConfigFinder */
            $configFinder = $this->container->get("terrific.exporter.configfinder");
            $config = $configFinder->find("csslint.cfg");

            $configData = $this->prepareConfigurationData($config);

            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.pagemanager");

            // retrieve only assets matching the current export
            $assetList = $pageManager->retrieveAllAssets(true);

            foreach ($assetManager->getNames() as $name) {
                /** @var $asset FileAsset */
                $asset = $assetManager->get($name);

                if (FileHelper::isStylesheet($asset->getTargetPath()) && in_array($asset->getTargetPath(), $assetList)) {
                    $this->log(AbstractAction::LOG_LEVEL_INFO, "Starting Validation of parts for " . basename($asset->getTargetPath()));

                    foreach ($asset as $origLeaf) {
                        $leaf = AsseticHelper::removeMinFilters($origLeaf);

                        $content = $leaf->dump();
                        $file = $tmpFileMgr->putContent($content);

                        $ret = ProcessHelper::startCommand("csslint", array_merge($configData, array($file)));
                        $parseRet = XmlLintHelper::parseXML($ret->getOutput());

                        if ($parseRet instanceof \Exception) {
                            $this->log(AbstractAction::LOG_LEVEL_ERROR, "Cannot parse CSSLint output.");
                            $this->log(AbstractAction::LOG_LEVEL_ERROR, $parseRet->getMessage());
                            $this->log(AbstractAction::LOG_LEVEL_ERROR, $ret->getCommandLine());
                            $this->log(AbstractAction::LOG_LEVEL_ERROR, $ret->getErrorOutput());
                            $error = true;
                        } else {
                            $this->processValidationResults($parseRet, basename($leaf->getTargetPath()));
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
