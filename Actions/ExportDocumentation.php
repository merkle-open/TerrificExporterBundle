<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 03.01.13
 * Time: 20:06
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Terrific\ExporterBundle\Object\ActionRequirement;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ExporterBundle\Helper\ProcessHelper;
    use Terrific\ExporterBundle\Exception\ExporterException;
    use Terrific\ExporterBundle\Service\TempFileManager;
    use Terrific\ExporterBundle\Service\PageManager;
    use Terrific\ExporterBundle\Object\Route;
    use Terrific\ExporterBundle\Object\RouteLocale;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Finder\SplFileInfo;
    use Terrific\ExporterBundle\Service\PathResolver;
    use Terrific\ExporterBundle\Renderer\Document\DocumentRenderer;
    use Terrific\ExporterBundle\Renderer\Document\IDocumentRenderer;
    use Terrific\ExporterBundle\Object\RouteModule;
    use Terrific\ExporterBundle\Helper\JavascriptHelper;
    use Assetic\Asset\FileAsset;

    /**
     *
     */
    class ExportDocumentation extends AbstractExportAction {

        /** @var PageManager */
        private $pageManager;


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
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return true;
        }


        /**
         * @param $modName
         */
        protected function getDocumentationContent($modName) {
            $file = $this->container->getParameter("kernel.root_dir") . "/../src/Terrific/Module/${modName}/README.md";

            if (file_exists($file)) {
                return file_get_contents($file);
            }

            return "";
        }

        /**
         * @param \Terrific\ExporterBundle\Renderer\Document\DocumentRenderer $renderer
         */
        protected function generateModuleList(IDocumentRenderer $renderer) {
            $renderer->section("Modules");


            $alphaList = array();

            /** @var $module RouteModule */
            foreach ($this->pageManager->findAllRouteModules() as $module) {
                $alphaList[$module->getModule()] = $module;
            }


            ksort($alphaList);


            /** @var $module RouteModule */
            foreach ($alphaList as $module) {
                $renderer->subsection($module->getModule());

                $doc = $this->getDocumentationContent($module->getModule());
                if($doc != "") {
                    $renderer->rawText($doc);
                    $renderer->block("");
                }


                if (count($module->getSkins()) > 0) {
                    $renderer->subsubsection("Skins");
                    $renderer->addList($module->getSkins(), "-");
                }
            }

        }

        /**
         * @param \Terrific\ExporterBundle\Renderer\Document\DocumentRenderer $renderer
         */
        protected function generateModuleConnections(IDocumentRenderer $renderer) {
            $renderer->section("Module connections");
            foreach ($this->pageManager->findAllConnectedModules() as $connector => $modules) {
                $modNames = array();
                /** @var $mod RouteModule */
                foreach ($modules as $mod) {
                    $modNames[] = $mod->getModule();
                }

                $renderer->subsection("Connector '" . $connector . "'")->addList($modNames, "1");
            }
        }


        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $pageManager PageManager */
            $this->pageManager = $this->container->get("terrific.exporter.page_manager");

            /** @var $pathResolver PathResolver */
            $pathResolver = $this->container->get("terrific.exporter.pathresolver");

            /** @var $renderer IDocumentRenderer */
            $renderer = DocumentRenderer::factory('Terrific\ExporterBundle\Renderer\Document\MarkdownFile');

            /** @var $assetManager LazyAssetManager */
            $assetManager = $this->container->get("assetic.asset_manager");

            $this->generateModuleList($renderer);
            $this->generateModuleConnections($renderer);


//            foreach ($assetManager->getNames() as $name) {
//                /** @var $asset FileAsset */
//                $asset = $assetManager->get($name);
//
//                if (\Terrific\ExporterBundle\Helper\FileHelper::isJavascript($asset->getTargetPath())) {
//                    foreach ($asset as $leaf) {
//                        $ll = \Terrific\ExporterBundle\Helper\AsseticHelper::removeMinFilters($leaf);
//                        $content = $ll->dump();
//
//                        JavascriptHelper::retrieveTerrificEvents($content);
//                    }
//                }
//            }

            \Terrific\ExporterBundle\Helper\FileHelper::createPathRecursive($params["exportPath"]);
            $renderer->save($params["exportPath"] . "/Documentation.md");

            die();

            return new ActionResult(ActionResult::OK);
        }
    }
}
