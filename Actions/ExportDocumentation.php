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

    /**
     *
     */
    class ExportDocumentation extends AbstractExportAction {

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
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.page_manager");

            /** @var $pathResolver PathResolver */
            $pathResolver = $this->container->get("terrific.exporter.pathresolver");

            /** @var $renderer IDocumentRenderer */
            $renderer = DocumentRenderer::factory('Terrific\ExporterBundle\Renderer\Document\MarkdownFile');


            $renderer->section("Module connections");
            foreach ($pageManager->findAllConnectedModules() as $connector => $modules) {
                $modNames = array();
                /** @var $mod RouteModule */
                foreach ($modules as $mod) {
                    $modNames[] = $mod->getModule();
                }

                $renderer->subsection("Connector '" . $connector . "'")->addList($modNames, "1");
            }


            echo $renderer->getContent();
            die();

            return new ActionResult(ActionResult::OK);
        }
    }
}
