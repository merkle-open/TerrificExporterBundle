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
    use Terrific\ExporterBundle\Service\Log;
    use Terrific\ExporterBundle\Helper\NumberHelper;
    use Terrific\ExporterBundle\Helper\OSHelper;

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

            $ret[] = new ActionRequirement("advpng", ActionRequirement::TYPE_PROCESS, 'Terrific\ExporterBundle\Actions\OptimizeImages');
            $ret[] = new ActionRequirement("optipng", ActionRequirement::TYPE_PROCESS, 'Terrific\ExporterBundle\Actions\OptimizeImages');


            if (!OSHelper::isWin()) {
                $ret[] = new ActionRequirement("jpegoptim", ActionRequirement::TYPE_PROCESS, 'Terrific\ExporterBundle\Actions\OptimizeImages');
            }


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
         *
         * @return int
         */
        public function optimizePNG(SplFileInfo $file) {
            $oldSize = $file->getSize();


            /** @var $process Process */
            $process = ProcessHelper::startCommand("optipng", array("-o7", $file->getPathname()));

            if ($process->isSuccessful()) {
                /** @var $process Process */
                $process = ProcessHelper::startCommand("advpng", array("-q", "-4", $file->getPathname()));

                if ($process->getExitCode() == 1) {
                    clearstatcache(true, $file->getPathname());

                    return ($oldSize - $file->getSize());
                }
            }

            return -1;
        }

        /**
         * @param \Symfony\Component\Finder\SplFileInfo $file
         */
        public function optimizeJPEG(SplFileInfo $file) {
            if (OSHelper::isWin()) {
                return -1;
            }

            $oldSize = $file->getSize();

            /** @var $process Process */
            $process = ProcessHelper::startCommand("jpegoptim", array("-q", $file->getPathname()));

            if ($process->isSuccessful()) {
                clearstatcache(true, $file->getPathname());

                return ($oldSize - $file->getSize());
            }

            return -1;
        }

        /**
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @return ActionResult|\Terrific\ExporterBundle\Object\ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            $fileList = $this->retrieveFileList($params);

            $savedOverall = 0;

            /** @var $file SplFileInfo */
            foreach ($fileList as $file) {
                $optimized = false;

                switch (strtoupper($file->getExtension())) {
                    case "PNG":
                        $optimized = true;
                        $compressed = $this->optimizePNG($file);
                        break;

                    case "JPG":
                        $optimized = true;
                        $compressed = $this->optimizeJPEG($file);
                        break;
                }

                if ($optimized) {
                    $savedOverall += $compressed;
                    Log::info("Optimized Image [%s] Saved => %s", array($file->getFilename(), NumberHelper::formatBytes($compressed)));
                }
            }

            Log::info("Total saving: %s", array(NumberHelper::formatBytes($savedOverall)));

            return new ActionResult(ActionResult::OK);
        }
    }
}





