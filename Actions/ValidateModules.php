<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 12.11.12
 * Time: 14:16
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Actions {
    use Symfony\Component\Console\Output\OutputInterface;
    use Terrific\ExporterBundle\Object\ActionResult;
    use Terrific\ExporterBundle\Service\TempFileManager;
    use Terrific\ExporterBundle\Service\PageManager;
    use Terrific\ExporterBundle\Object\Route;
    use Symfony\Bundle\FrameworkBundle\HttpKernel;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Terrific\ExporterBundle\Service\W3CValidator;
    use Terrific\ComposerBundle\Service\ModuleManager;
    use Terrific\ComposerBundle\Entity\Module;
    use Terrific\ExporterBundle\Object\RouteModule;

    /**
     *
     */
    class ValidateModules extends AbstractAction implements IAction {

        /**
         * Return true if the action should be runned false if not.
         *
         * @param array $params
         * @return bool
         */
        public function isRunnable(array $params) {
            return (isset($params["validate_html"]) && $params["validate_html"] && isset($params["export_views"]) && $params["export_views"]);
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

                /** @var $w3Validator W3CValidator */
                $w3Validator = $this->container->get("terrific.exporter.w3validator");

                $done = array();

                /** @var $route Route */
                foreach ($pageManager->findRoutes(true) as $route) {

                    /** @var $module RouteModule */
                    foreach ($route->getModules() as $module) {
                        if (!in_array($module->getId(), $done)) {
                            $content = $this->doDump($module);
                            $file = $tmpFileMgr->putContent($content);

                            $results = $w3Validator->validateFile($file);

                            $done[] = $module->getId();
                        }
                    }
                }

                return new ActionResult(ActionResult::OK);
            } else if ($this->logger) {
                $this->logger->debug("Cannot find Terrific Modulemanager in DIC.");
            }
        }
    }
}
