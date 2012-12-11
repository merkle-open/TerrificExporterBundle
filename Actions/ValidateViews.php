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

    /**
     *
     */
    class ValidateViews extends AbstractAction implements IAction {

        /**
         * @param $params
         * @return ActionResult
         */
        public function run(OutputInterface $output, $params = array()) {
            /** @var $tmpFileMgr TempFileManager */
            $tmpFileMgr = $this->container->get("terrific.exporter.tempfilemanager");

            /** @var $pageManager PageManager */
            $pageManager = $this->container->get("terrific.exporter.pagemanager");

            /** @var $w3Validator W3CValidator */
            $w3Validator = $this->container->get("terrific.exporter.w3validator");

            $error = false;

            /** @var $route Route */
            foreach ($pageManager->findRoutes(true) as $route) {
                $resp = $pageManager->dumpRoute($route);
                $file = $tmpFileMgr->putContent($resp->getContent());

                $results = $w3Validator->validateFile($file);

                if ($results->hasErrors()) {
                    $error = true;
                }
            }

            if ($error) {
                //return new ActionResult(ActionResult::STOP);
            }

            return new ActionResult(ActionResult::OK);
        }
    }
}
