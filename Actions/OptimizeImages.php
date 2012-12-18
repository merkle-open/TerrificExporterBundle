<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.11.12
 * Time: 23:47
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Assetic\Asset\FileAsset;
    use Assetic\Factory\LazyAssetManager;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Filesystem\Exception\IOException;
    use Symfony\Component\Finder\Finder;
    use Terrific\ExporterBundle\Helper\ProcessHelper;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Symfony\Component\Finder\SplFileInfo;
    use Symfony\Component\Process\Process;
    use Terrific\ExporterBundle\Object\ActionRequirement;

    /**
     *
     */
    class OptimizeImages extends AbstractAction implements IAction {
        const OPTIM_TRIMAGE = 0;

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

            $ret[] = new ActionRequirement("trimage", ActionRequirement::TYPE_PROCESS, 'Terrific\ExporterBundle\Actions\OptimizeImages');

            return $ret;
        }


        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return (isset($params["optimize_images"]) && $params["optimize_images"]);
        }


        /**
         * @return bool|int
         */
        public function retrieveOptimizer() {
            if (ProcessHelper::checkCommand("trimage --version")) {
                return self::OPTIM_TRIMAGE;
            }

            return false;
        }


        /**
         * @param array $params
         * @return iterator
         */
        public function retrieveFileList(array $params) {
            $f = new Finder();

            $f->in($params["exportPath"]);
            $f->name("*.jpg")->name("*.png")->name("*.gif");

            return $f->getIterator();
        }


        /**
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @return ActionResult|\Terrific\ExporterBundle\Object\ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            $optim = $this->retrieveOptimizer();

            $fileList = $this->retrieveFileList($params);

            switch ($optim) {
                case self::OPTIM_TRIMAGE:
                    /** @var $file SplFileInfo */
                    foreach ($fileList as $file) {
                        /** @var $process Process */
                        $process = ProcessHelper::startCommand("trimage", array("--file", $file->getPathname()));

                        $this->logger->debug(str_replace($params["exportPath"], "", trim($process->getOutput())));
                    }
                    break;
            }


            return new ActionResult(ActionResult::OK);
        }
    }
}





