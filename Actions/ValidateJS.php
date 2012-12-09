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

    /**
     *
     */
    class ValidateJS extends AbstractAction implements IAction {

        /**
         *
         * @param $file String
         * @return Boolean
         */
        private function isJavascript($file) {
            return (strtolower(substr($file, -2)) == "js");
        }

        /**
         * Returns all issues detected by JSHint.
         *
         * @param $data String
         * @return ValidationResult
         */
        private function parseXML($data) {
            $ret = new ValidationResult();

            try {
                $xml = new \DOMDocument();
                $xml->loadXML($data);


                $xpath = new \DOMXPath($xml);
                foreach ($xpath->query('/jslint/file/issue') as $issue) {
                    $item = new ValidationResultItem($issue->getAttribute("reason"));
                    $item->setChar($issue->getAttribute("char"));
                    $item->setLine($issue->getAttribute("line"));

                    $ret->addResult($item);
                }
            } catch (\Exception $ex) {
                return $ex;
            }

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

            /** @var $configFinder ConfigFinder */
            $configFinder = $this->container->get("terrific.exporter.configfinder");
            $config = $configFinder->find("jshint.json");

            foreach ($assetManager->getNames() as $name) {
                $asset = $assetManager->get($name);

                if ($this->isJavascript($asset->getTargetPath())) {
                    $this->log(AbstractAction::LOG_LEVEL_INFO, "Starting Validation of parts for " . basename($asset->getTargetPath()));

                    foreach ($assetManager->get($name) as $leaf) {
                        $this->log(AbstractAction::LOG_LEVEL_DEBUG, "- Validating " . basename($leaf->getSourcePath()));
                        $leafPath = realpath($leaf->getSourceRoot() . "/" . $leaf->getSourcePath());

                        if ($leafPath != "" && is_file($leafPath)) {
                            $ret = ProcessHelper::startCommand("jshint", array("--jslint-reporter", "--config", $config, $leafPath));

                            $parseRet = $this->parseXML($ret->getOutput());

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
    }
}
