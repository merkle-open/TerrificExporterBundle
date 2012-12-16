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
    use Symfony\Component\Finder\SplFileInfo;
    use Terrific\ExporterBundle\Service\TempFileManager;
    use Terrific\ExporterBundle\Service\PageManager;
    use Terrific\ExporterBundle\Service\PathResolver;
    use Terrific\ExporterBundle\Helper\TimerService;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Helper\FileHelper;

    /**
     *
     */
    class OptimizeAssets extends AbstractAction implements IAction {

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
         * @param \Symfony\Component\Console\Output\OutputInterface $output
         * @param array $params
         * @return ActionResult|\Terrific\ExporterBundle\Object\ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {


            return new ActionResult(ActionResult::OK);
        }
    }
}





