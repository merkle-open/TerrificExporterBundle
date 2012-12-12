<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 12.11.12
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Helper\ProcessHelper;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Filesystem\Filesystem;

    /**
     *
     */
    class GenerateSprites extends AbstractAction implements IAction {

        /**
         * @param String $directory
         * @param String $targetName
         * @param array $params
         */
        protected function buildSpriteFromDirectory($directory, $targetName, $width, $height) {
            /** @var $fs Filesystem */
            $fs = $this->container->get("filesystem");

            if (!$fs->isAbsolutePath($directory)) {
                $directory = $this->container->getParameter("kernel.root_dir") . "/../" . $directory;
            }

            if (!$fs->isAbsolutePath($targetName)) {
                $targetName = $this->container->getParameter("kernel.root_dir") . "/../" . $targetName;
            }

            $this->logger->debug("Building sprite from directory [${directory}] => [${targetName}]");

            $finder = new Finder();
            $fsIterator = $finder->in($directory)->sortByName()->files()->getIterator();

            $dirList = iterator_to_array($fsIterator);

            $processParams = array();
            $processParams[] = "-mode";
            $processParams[] = "Concatenate";

            $processParams[] = "-tile";
            $processParams[] = "x${height}";

            $processParams[] = "-geometry";
            $processParams[] = "${width}x${height}+0+0";

            $processParams[] = "-bordercolor";
            $processParams[] = " none";

            $processParams[] = "-background";
            $processParams[] = " none";

            $processParams = array_merge($processParams, $dirList);
            $processParams[] = $targetName;

            $process = ProcessHelper::startCommand("montage", $processParams);


            return $process;
        }

        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            if (!ProcessHelper::checkCommand("montage")) {
                $this->logger->info("Need montage (ImageMagick) to build sprites.");
                return;
            }

            $params["spriteList"] = array(array("directory" => "/data/vhosts.d/exporter-bundle/htdocs/PROD/internet_sprite_icons", "name" => "/tmp/testSprite.png", "width" => 100, "height" => 50));
            foreach ($params["spriteList"] as $sprite) {
                if (isset($sprite["directory"]) && isset($sprite["name"])) {
                    $process = $this->buildSpriteFromDirectory($sprite["directory"], $sprite["name"], $sprite["width"], $sprite["height"]);

                    if (!$process->isSuccessful()) {
                        $this->logger->err("Couldn't create Sprite [${sprite["name"]}]");
                        $this->logger->debug($process->getCommandLine());
                        $this->logger->debug($process->getErrorOutput());
                        return new ActionResult(ActionResult::STOP);
                    }
                }
            }

            return new ActionResult(ActionResult::OK);
        }
    }
}
