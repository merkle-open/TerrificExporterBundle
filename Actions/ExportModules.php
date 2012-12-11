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

    /**
     *
     */
    class ExportModules extends AbstractAction implements IAction {


        public function doDump(Route $route, Module $module) {
            $url = $route->getUrl();

            $url = str_replace("{module}", $module->getName(), $url);
            $url = str_replace("{template}", $module->getTemplates()[0]->getName(), $url);
            $url = str_replace("{skins}", "", $url);

            /** @var $http HttpKernel */
            $req = Request::create($url);

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

            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.pagemanager");

            $route = $pageManager->findRoute("Module", "detailsAction");

            /** @var $module Module */
            foreach ($moduleManager->getModules() as $module) {
                $module = $moduleManager->getModuleByName($module->getName());

                $content = $this->doDump($route, $module);
                var_dump($content);
            }


            return new ActionResult(ActionResult::OK);
        }
    }
}
