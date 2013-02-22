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
    use Symfony\Component\HttpKernel\HttpKernel;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Terrific\ExporterBundle\Annotation\Export;
    use Terrific\ExporterBundle\Helper\StringHelper;
    use Terrific\ExporterBundle\Helper\FileHelper;
    use Terrific\ExporterBundle\Object\RouteModule;
    use Terrific\ExporterBundle\Annotation\LocaleExport;
    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\Translation\Translator;
    use Terrific\ComposerBundle\Service\ModuleManager;


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

        /** @var HttpKernel */
        private $http_kernel = null;

        /** @var array */
        private $assetCache = array();

        /** @var ModuleManager */
        protected $moduleManager;


        /**
         * @param \Terrific\ExporterBundle\Service\ModuleManager $moduleManager
         */
        public function setModuleManager($moduleManager) {
            $this->moduleManager = $moduleManager;
        }

        /**
         * @return \Terrific\ExporterBundle\Service\ModuleManager
         */
        public function getModuleManager() {
            return $this->moduleManager;
        }

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
         * @param \Twig_Environment $twig
         */
        public function setTwig($twig) {
            $this->twig = $twig;
        }

        /**
         * @return \Twig_Environment
         */
        public function getTwig() {
            return $this->twig;
        }

        /**
         * @param \Symfony\Component\HttpKernel\HttpKernel $http_kernel
         */
        public function setHttpKernel($http_kernel) {
            $this->http_kernel = $http_kernel;
        }

        /**
         * @return \Symfony\Component\HttpKernel\HttpKernel
         */
        public function getHttpKernel() {
            return $this->http_kernel;
        }

        /**
         * @param $modName
         */
        protected function findModuleName($modName) {
            $modList = $this->moduleManager->getModules();

            foreach ($modList as $mod) {
                if (strtolower($mod->getName()) == strtolower($modName)) {
                    $modName = $mod->getName();
                    break;
                }
            }

            return $modName;
        }


        /**
         *
         */
        protected function findAssetsByFile($filePath, array $in = array()) {
            $this->initialize();

            $md5 = md5($filePath);

            if (isset($this->assetCache[$md5]) && is_array($this->assetCache[$md5])) {
                return $this->assetCache[$md5];
            }

            $content = file_get_contents($filePath);

            /** @var $stream \Twig_Token_Stream */
            $stream = $this->twig->tokenize($content, basename($filePath));

            $inBlock = null;
            $nextOutput = false;

            while (!$stream->isEOF()) {
                $token = $stream->next();

                $skins = array();
                $connectors = array();

                if (($this->moduleManager != null) && ($token instanceof \Twig_Token)) {


                    if (strpos($token->getValue(), "<div") !== false) {
                        $classMatches = array();
                        $conMatches = array();
                        $skinMatches = array();
                        $modName = "";

                        preg_match('#class=[\'"]([^\'"]*)#i', $token->getValue(), $classMatches);
                        preg_match('#data-connectors=[\'"]([^\'"]*)#i', $token->getValue(), $conMatches);

                        if (isset($classMatches[1])) {
                            $mm = explode(" ", $classMatches[1]);

                            foreach ($mm as $m) {
                                switch (true) {
                                    case (substr($m, 0, 4) == "mod-"):
                                        $modName = substr($m, 4);
                                        break;

                                    case (substr($m, 0, 5) == "skin-"):
                                        $skins[] = substr($m, 5);
                                        break;
                                }
                            }

                            $connectors = (isset($conMatches[1]) ? explode(",", $conMatches[1]) : array());

                            if ($modName != "") {
                                $modName = $this->findModuleName($modName);
                                $routeModule = new RouteModule($modName, basename($filePath), $skins, $connectors);
                                $in[] = $routeModule;
                            }
                        }
                    }
                }


                if ($token->getValue() === "extends") {
                    $newTpl = $stream->getCurrent()->getValue();
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

                if ($token->getValue() === "asset") {
                    $url = $stream->look()->getValue();

                    $in[] = $url;
                }

                if ($token->getValue() == "tc" && $stream->getCurrent()->getValue() == "." && $stream->look()->getValue() == "module") {
                    while ($token->getValue() != "(") {
                        $token = $stream->next();
                    }
                    $token = $stream->next();

                    $module = $token->getValue();

                    if ($stream->getCurrent()->getValue() != ")") {
                        $token = $stream->next();
                        $token = $stream->next();

                        $view = $token->getValue();

                        if ($view == "") {
                            $view = strtolower($module);
                        } else {
                            $view = $token->getValue();
                        }
                        $view .= ".html.twig";


                        $token = $stream->next();
                        $token = $stream->next();

                        if ($token->getValue() == "[") {
                            $token = $stream->next();
                            while ($token->getValue() != "]") {
                                if ($token->getValue() != ",") {
                                    $skins[] = $token->getValue();
                                }
                                $token = $stream->next();
                            }
                        }

                        if ($stream->next()->getValue() === ",") {
                            $token = $stream->next();
                            if ($token->getValue() == "[") {
                                $token = $stream->next();
                                while ($token->getValue() != "]") {
                                    if (trim($token->getValue()) !== "") {
                                        $connectors[] = $token->getValue();
                                    }
                                    $token = $stream->next();
                                }
                            }
                        }

                    } else {
                        $view = strtolower($module) . ".html.twig";
                    }


                    $routeModule = new RouteModule($module, $view, $skins, $connectors);
                    $in[] = $routeModule;

                    try {
                        $tpl = $this->kernel->locateResource(sprintf("@TerrificModule%s/Resources/views/%s", $routeModule->getModule(), $routeModule->getTemplate()));
                        $moduleIn = $this->findAssetsByFile($tpl);
                        $routeModule->setAssets($moduleIn);
                    } catch (InvalidArgumentException $ex) {

                    }
                }


                if ($inBlock) {
                    if ($token->getValue() === "output") {
                        $in[] = $stream->look()->getValue();
                    }
                }
            }

            if ($this->logger) {
                $this->logger->debug(sprintf("Found assets [ %s ] for template '%s':", implode(", ", $in), basename($filePath)));
            }


            $this->assetCache[$md5] = $in;
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
         * @param $connector
         * @return array
         */
        public function findConnectedModules($connector) {
            $this->initialize();

            $ret = array();

            /** @var $route Route */
            foreach ($this->findRoutes(true) as $route) {

                /** @var $route RouteModule */
                foreach ($route->getModules() as $module) {
                    if ($module->isConnectedTo($connector)) {
                        $ret[$module->getId()] = $module;
                    }
                }
            }

            return array_values($ret);
        }

        /**
         * @return array
         */
        public function findAllConnectedModules() {
            $connectors = array();

            /** @var $route Route */
            foreach ($this->findRoutes(true) as $route) {

                /** @var $route RouteModule */
                foreach ($route->getModules() as $module) {
                    $connectors = array_merge($connectors, $module->getConnectors());
                }
            }

            $connectors = array_unique($connectors);

            $ret = array();
            foreach ($connectors as $c) {
                $ret[$c] = $this->findConnectedModules($c);
            }

            return $ret;
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
         * @param String $controller
         * @param String $method
         */
        public function findRoute($controllerName, $methodName) {
            $this->initialize();

            if (strpos($methodName, "Action") === false) {
                $methodName .= "Action";
            }

            if (strpos($controllerName, "Controller") === false) {
                $controllerName .= "Controller";
            }

            /** @var $route Route */
            foreach ($this->routeList as $route) {
                $method = $route->getMethod();
                $class = $method->getDeclaringClass();

                if ($method->getName() == $methodName && $class->getShortName() == $controllerName) {
                    return $route;
                }
            }

            return null;
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
         * Returns the response object.
         *
         * @param \Terrific\ExporterBundle\Object\Route $route
         * @param String $locale
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function dumpRoute(Route $route, $locale = null) {
            $this->initialize();

            /** @var $http HttpKernel */
            $req = Request::create($route->getUrl(array("_locale" => $locale)));

            // Setup locale
            if ($locale !== null) {
                /** @var $translator Translator */
                $translator = $this->getKernel()->getContainer()->get("translator");

                $backupLocale = $translator->getLocale();
                $translator->setLocale($locale);
            }

            /** @var $resp Response */
            $resp = $this->http_kernel->handle($req);

            if ($locale != null) {
                $translator->setLocale($backupLocale);
            }

            return $resp;
        }

        /**
         * @param \Terrific\ExporterBundle\Object\Route $route
         * @return string
         */
        protected function findNameByRoute(Route $route) {
            $ret = "";

            /** @var $exportAnnotation Export */
            $exportAnnotation = $this->reader->getMethodAnnotation($route->getMethod(), 'Terrific\ExporterBundle\Annotation\Export');

            if ($exportAnnotation != null) {
                $ret = $exportAnnotation->getName();
            }

            $tmpName = "";
            if ($ret == "") {
                /** @var $composerAnnotation  \Terrific\ComposerBundle\Annotation\Composer */
                $composerAnnotation = $this->reader->getMethodAnnotation($route->getMethod(), 'Terrific\ComposerBundle\Annotation\Composer');

                if ($composerAnnotation != null) {
                    $tmpName = $composerAnnotation->getName();
                } else {
                    /** @var $routeAnnotation \Symfony\Component\Routing\Annotation\Route */
                    $routeAnnotation = $this->reader->getMethodAnnotation($route->getMethod(), '');

                    if ($routeAnnotation != null) {
                        $tmpName = $routeAnnotation->getName();
                    }
                }
                $ret = StringHelper::escapeFileLabel($tmpName);
            }

            if ($ret != "" && strpos(strtolower($ret), ".html") === false) {
                $ret .= ".html";
            }


            if ($ret == "") {
                $ret = sprintf("view%s.html", ucfirst($route->getMethod()->getShortName()));
            }

            return $ret;
        }

        /**
         * @param $name
         * @return RouteModule
         */
        public function &findRouteModule($name) {
            $this->initialize();


            /** @var $route Route */
            foreach ($this->routeList as $route) {

                /** @var $mod RouteModule */
                foreach ($route->getModules() as $mod) {
                    if ($mod->getModule() === $name) {
                        return $mod;
                    }
                }
            }

            return null;
        }


        /**
         * @param $name
         * @return array
         */
        public function findAllRouteModules($name = null) {
            $this->initialize();

            $ret = array();

            /** @var $route Route */
            foreach ($this->routeList as $route) {

                /** @var $mod RouteModule */
                foreach ($route->getModules() as $mod) {
                    if ($mod->getModule() === $name || $name === null) {
                        $ret[] = & $mod;
                    }
                }
            }

            return $ret;
        }

        /**
         * @param \Terrific\ExporterBundle\Object\Route $route
         */
        protected function buildUrlParameters(Route $route) {
            $url = $route->getUrl();

            $matches = array();
            $count = preg_match_all('/{([^}]+)}/', $url, $matches);

            if ($count !== false) {
                foreach ($matches[1] as $param) {
                    $route->addUrlParameter($param);
                }
            }
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

            /** @var $sRoute \Symfony\Component\Routing\Route */
            foreach ($this->router->getRouteCollection()->all() as $sRoute) {
                $route = new Route($sRoute);

                /** @var $exportAnnotation Export */
                $exportAnnotation = $this->reader->getMethodAnnotation($route->getMethod(), 'Terrific\ExporterBundle\Annotation\Export');

                if ($exportAnnotation) {
                    // Check if current export environment matches controller annotation environment settings 
                    if (!$exportAnnotation->matchEnvironment($this->kernel->getEnvironment())) {
                        continue;
                    }

                    $route->setExportable(true);

                    // Check if languages are set in controller annotation
                    if (count($exportAnnotation->getLocales()) > 0) {
                        /** @var $locale LocaleExport */
                        foreach ($exportAnnotation->getLocales() as $locale) {
                            // Check if current locale matches controller annotation locale settings 
                            if ($locale->matchEnvironment($this->kernel->getEnvironment())) {
                                $route->addLocale($locale->getLocale(), $locale->getName());
                            }
                        }
                    }
                }

                $route->setExportName($this->findNameByRoute($route));
                $this->buildUrlParameters($route);
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
