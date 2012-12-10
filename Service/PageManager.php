<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 10:56
 * To change this template use File | Settings | File Templates.
 */

namespace Terrific\ExporterBundle\Service {
    use Symfony\Component\Routing\RouterInterface;
    use Doctrine\Common\Annotations\Reader;
    use Terrific\ExporterBundle\Object\Route;

    /**
     *
     */
    class PageManager {

        /** @var Reader */
        private $reader;

        /** @var RouterInterface */
        private $router;

        /**
         * @param \Doctrine\Common\Annotations\Reader $reader
         */
        public function setReader($reader) {
            $this->reader = $reader;
        }

        /**
         * @return \Doctrine\Common\Annotations\Reader
         */
        public function getReader() {
            return $this->reader;
        }

        /**
         * @param \Symfony\Component\Routing\RouterInterface $router
         */
        public function setRouter($router) {
            $this->router = $router;
        }

        /**
         * @return \Symfony\Component\Routing\RouterInterface
         */
        public function getRouter() {
            return $this->router;
        }


        /**
         * Returns a set of route's.
         * If $exportablesOnly is set to true only routes which are exportable are returned.
         *
         * @param bool $exportablesOnly
         * @return array
         */
        public function findRoutes($exportablesOnly = false) {
            $ret = array();

            $routes = array();
            foreach ($this->router->getRouteCollection()->all() as $sRoute) {
                $route = new Route($sRoute);

                $exportAnnotation = $this->reader->getMethodAnnotation($route->getMethod(), 'Terrific\ExporterBundle\Annotation\Export');
                $route->setExportable(($exportAnnotation != null));

                if (!$exportablesOnly || $route->isExportable()) {
                    $routes[] = $route;
                }
            }

            return $routes;
        }


        /**
         *
         */
        public function __construct() {
        }
    }
}
