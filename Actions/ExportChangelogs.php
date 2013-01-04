<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 03.01.13
 * Time: 18:52
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Finder\SplFileInfo;
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Service\Log;

    class ExportChangelogs extends AbstractExportAction {
        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return !empty($params["changelogPath"]) && $params["changelogPath"] !== false && $this->fs->exists($params["changelogPath"]);
        }

        /**
         * Returns requirements for running this Action.
         *
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @param array $runnedActions
         * @return array
         */
        public static function getRequirements() {
            return array();
        }

        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            $finder = new Finder();

            $finder->in($params["changelogPath"]);
            $finder->name("*.md")->name("*.txt")->name("*.log");


            if ($finder->count() > 0) {
                $exportPath = $params["exportPath"] . "/changelogs";
                FileHelper::createPathRecursive($exportPath);

                $this->log(self::LOG_LEVEL_DEBUG, "Append Changelogs:");
                /** @var $file SplFileInfo */
                foreach ($finder as $file) {
                    $this->log(self::LOG_LEVEL_DEBUG, "-- Append Changelog: " . $file->getFilename());
                    $this->fs->copy($file->getPathname(), $exportPath . "/" . $file->getFilename());
                }

                Log::info("Append %d changelogs", array($finder->count()));
            } else {
                Log::info("No changelog found");
            }

            return new ActionResult(ActionResult::OK);
        }
    }
}
