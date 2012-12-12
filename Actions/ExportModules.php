<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 12.11.12
 * Time: 14:08
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ComposerBundle\Service\ModuleManager;
    use Terrific\ComposerBundle\Entity\Module;
    use Terrific\ExporterBundle\Service\PageManager;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Object\Route;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request;
    use Terrific\ExporterBundle\Service\TempFileManager;
    use Terrific\ExporterBundle\Service\PathResolver;
    use Terrific\ExporterBundle\Helper\FileHelper;


    /**
     *
     */
    class ExportModules extends AbstractAction implements IAction {

        /**
         * @param $tmpFile String
         * @param $targetFile String
         */
        protected function saveToPath($tmpFile, $targetFile) {
            /** @var $fs Filesystem */
            $fs = $this->container->get("filesystem");

            $targetPath = dirname($targetFile);

            try {
                FileHelper::createPathRecursive(dirname($targetFile));
                $fs->copy($tmpFile, $targetFile);

                return true;
            } catch (IOException $ex) {
                $this->logger->err($ex->getMessage());
                $this->logger->err($ex->getTraceAsString());
            }

            return false;
        }


        /**
         * @param \Terrific\ExporterBundle\Object\Route $route
         * @param \Terrific\ComposerBundle\Entity\Module $module
         * @return string
         */
        public function doDump(Route $route, Module $module, $template) {
            $url = $route->getUrl(array("module" => $module->getName(), "template" => $template, "skins" => ""));

            /** @var $http HttpKernel */
            $req = Request::create($url);
            $req->headers->set("X-Requested-With", "XMLHttpRequest");

            /** @var $resp Response */
            $resp = $this->container->get("http_kernel")->handle($req);

            return $resp->getContent();
        }

        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {

            /** @var $moduleManager ModuleManager */
            $moduleManager = $this->container->get("terrific.composer.module.manager");

            if ($moduleManager != null) {
                /** @var $tmpFileMgr TempFileManager */
                $tmpFileMgr = $this->container->get("terrific.exporter.tempfilemanager");

                /** @var $pageManager PageManager */
                $pageManager = $this->container->get("terrific.exporter.pagemanager");

                /** @var $pathResolver PathResolver */
                $pathResolver = $this->container->get("terrific.exporter.pathresolver");

                $route = $pageManager->findRoute("Module", "detailsAction");

                /** @var $module Module */
                foreach ($moduleManager->getModules() as $module) {
                    $module = $moduleManager->getModuleByName($module->getName());

                    /** @var $tpl \Terrific\ComposerBundle\Entity\Template */
                    foreach ($module->getTemplates() as $tpl) {
                        $content = $this->doDump($route, $module, $tpl->getName());
                        $file = $tmpFileMgr->putContent($content);

                        $path = $pathResolver->resolve(sprintf("/src/Terrific/Module/%s/Resource/views/%s.html", $module->getName(), $tpl->getName()));
                        $this->saveToPath($file, $params["exportPath"] . "/" . $path);
                    }
                }

                return new ActionResult(ActionResult::OK);
            } else if ($this->logger) {
                $this->logger->debug("Cannot find Terrific Modulemanager in DIC.");
            }


            return new ActionResult(ActionResult::STOP);
        }
    }
}
