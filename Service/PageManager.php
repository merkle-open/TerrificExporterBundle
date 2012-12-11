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
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;
    use Symfony\Component\HttpKernel\Kernel;
    use InvalidArgumentException;
    use Terrific\ExporterBundle\Object\Route;

    /**
     *
     */
    class PageManager {

        /** @var Reader */
        private $reader;

        /** @var RouterInterface */
        private $router;

        /** @var Kernel */
        private $kernel;

        /** @var LoggerInterface */
        private $logger = null;

        /** @var Twig_Environment */
        private $twig = null;

        /** @var array */
        private $routeList = array();

        /** @var bool */
        private $initialized = false;

        /**
         * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
         */
        public function setLogger($logger) {
            $this->logger = $logger;
        }

        /**
         * @return \Symfony\Component\HttpKernel\Log\LoggerInterface
         */
        public function getLogger() {
            return $this->logger;
        }


        /**
         * @param \Symfony\Component\HttpKernel\Kernel $kernel
         */
        public function setKernel($kernel) {
            $this->kernel = $kernel;
        }

        /**
         * @return \Symfony\Component\HttpKernel\Kernel
         */
        public function getKernel() {
            return $this->kernel;
        }


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
         * @param \Terrific\ExporterBundle\Service\Twig_Environment $twig
         */
        public function setTwig($twig) {
            $this->twig = $twig;
        }

        /**
         * @return \Terrific\ExporterBundle\Service\Twig_Environment
         */
        public function getTwig() {
            return $this->twig;
        }


        /**
         *
         */
        protected function findAssetsByFile($filePath, array $in = array()) {
            $this->initialize();
            $ret = $in;

            $content = file_get_contents($filePath);

            /** @var $stream \Twig_Token_Stream */
            $stream = $this->twig->tokenize($content, basename($filePath));

            $inBlock = null;
            $nextOutput = false;

            while (!$stream->isEOF()) {
                $token = $stream->next();

                if ($token->getValue() === "extends") {
                    $newTpl = $stream->getCurrent()->getValue();
                    $this->logger->debug("Found Parent Template :" . $newTpl);
                    list($bundle, $controller, $view) = explode(":", $newTpl);

                    $pTpl = $this->kernel->locateResource(sprintf("@%s/Resources/views/%s/%s", $bundle, $controller, $view));

                    $in = $this->findAssetsByFile($pTpl, $in);
                }

                if ($token->getValue() === "javascripts" || $token->getValue() === "stylesheets") {
                    $inBlock = true;
                }

                if ($token->getValue() === "endjavascripts" || $token->getValue() === "endstylesheets") {
                    $inBlock = false;
                }

                if ($inBlock) {
                    if ($token->getValue() === "output") {
                        $in[] = $stream->look()->getValue();
                    }
                }
            }

            return $in;
        }


        /**
         * @param \Symfony\Component\Routing\Route $route
         */
        protected function findAssets(\Symfony\Component\Routing\Route $sRoute, Route $route) {
            /** @var $tplAnnotation Template */
            $tplAnnotation = $this->reader->getMethodAnnotation($route->getMethod(), 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Template');

            $tpl = $tplAnnotation->getTemplate();

            if ($tpl == "") {
                $tpl = str_replace("Action", "", $route->getMethod()->getName());
                $tplDir = str_replace("Controller", "", $route->getMethod()->getDeclaringClass()->getShortName());

                try {
                    $tpl = $this->kernel->locateResource(sprintf("@TerrificComposition/Resources/views/%s/%s.html.twig", $tplDir, $tpl));
                    $route->setTemplate($tpl);
                } catch (InvalidArgumentException $ex) {
                    if ($this->logger) {
                        $this->logger->debug(sprintf("Cannot locate template for %s::%s", $route->getMethod()->getDeclaringClass()->getShortName(), $route->getMethod()->getName()));
                    }
                }
            }

            // search assets in templates
            if ($route->getTemplate() !== "") {
                $assets = $this->findAssetsByFile($route->getTemplate());
                $route->addAssets($assets);
            }
        }


        /**
         * Returns a set of route's.
         * If $exportablesOnly is set to true only routes which are exportable are returned.
         *
         * @param bool $exportablesOnly
         * @return array
         */
        public function findRoutes($exportablesOnly = false) {
            $this->initialize();
            $ret = array();

            if ($exportablesOnly) {
                /** @var $route Route */
                foreach ($this->routeList as $route) {
                    if ($exportablesOnly && $route->isExportable()) {
                        $ret[] = $route;
                    }
                }
            } else {
                $ret = $this->routeList;
            }

            return $ret;
        }


        /**
         * @param bool $exportablesOnly
         */
        public function retrieveAllAssets($exportablesOnly = false) {
            $this->initialize();
            $ret = array();

            /** @var $route Route */
            foreach ($this->routeList as $route) {
                if (!$exportablesOnly || ($exportablesOnly && $route->isExportable())) {
                    $ret = array_merge($ret, $route->getAssets());
                }
            }

            return array_unique($ret);
        }


        /**
         *
         */
        protected function initialize() {
            if ($this->initialized) {
                return;
            }

            $this->initialized = true;

            if ($this->logger) {
                $this->logger->info("PageManager init");
            }

            foreach ($this->router->getRouteCollection()->all() as $sRoute) {
                $route = new Route($sRoute);

                $exportAnnotation = $this->reader->getMethodAnnotation($route->getMethod(), 'Terrific\ExporterBundle\Annotation\Export');
                $route->setExportable(($exportAnnotation != null));

                $this->findAssets($sRoute, $route);
                $this->routeList[] = $route;
            }
        }


        /**
         *
         */
        public function __construct() {
        }
    }
}
