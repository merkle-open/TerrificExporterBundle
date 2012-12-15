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
    use Terrific\ExporterBundle\Object\RouteModule;


    /**
     *
     */
    class ExportModules extends AbstractExportAction implements IAction {

        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return (isset($params["export_modules"]) && $params["export_modules"]);
        }


        /**
         * @param \Terrific\ExporterBundle\Object\Route $route
         * @param \Terrific\ComposerBundle\Entity\Module $module
         * @return string
         */
        public function doDump(RouteModule $module) {
            $url = $module->getUrl(array("module" => $module->getModule(), "template" => $module->getTemplate(), "skins" => implode(" ", $module->getSkins())));

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

                /** @var $route Route */
                foreach ($pageManager->findRoutes(true) as $route) {

                    /** @var $module RouteModule */
                    foreach ($route->getModules() as $module) {
                        $content = $this->doDump($module);
                        $file = $tmpFileMgr->putContent($content);

                        $path = $pathResolver->resolve(sprintf("/src/Terrific/Module/%s/Resource/views/%s", $module->getModule(), $module->getExportingPath()));
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
