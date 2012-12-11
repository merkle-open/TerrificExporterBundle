<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 11.11.12
 * Time: 01:13
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Service {
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\DependencyInjection\ContainerAwareInterface;
    use Terrific\ComposerBundle\Service\ModuleManager;
    use Terrific\ExporterBundle\Exception\PathResolveException;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;

    /**
     *
     */
    class PathResolver implements ContainerAwareInterface {
        const TYPE_IMAGE = 0x00;
        const TYPE_FONT = 0x01;
        const TYPE_VIEW = 0x02;
        const TYPE_CSS = 0x04;
        const TYPE_JS = 0x08;

        const SCOPE_GLOBAL = 0x20;
        const SCOPE_MODULE = 0x10;

        /**
         * @var ContainerInterface
         */
        protected $container;

        /**
         * @var ModuleManager
         */
        protected $moduleManager;

        /**
         * @var Array
         */
        protected $pathTemplate;

        /**
         * @var Array
         */
        protected $modules;

        /**
         * @var LoggerInterface
         */
        protected $logger;

        /**
         * Setter for logger.
         *
         * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
         * @return void
         */
        public function setLogger($logger) {
            $this->logger = $logger;
        }

        /**
         * Setter for modulemanager.
         *
         * @param \Terrific\ComposerBundle\Service\ModuleManager $moduleManager
         * @return void
         */
        public function setModuleManager(ModuleManager $moduleManager) {
            $this->moduleManager = $moduleManager;

            if ($this->moduleManager != null) {
                $this->setModules($this->moduleManager->getModules());
            }
        }

        /**
         * Setter for modulelist.
         *
         * @param Array $modules
         * @return void
         */
        public function setModules($modules) {
            $this->modules = $modules;
            if ($this->logger !== null) {
                $this->logger->debug("Set modulelist : " . implode(",", $this->modules));
            }
        }

        /**
         * Getter for modulelist.
         *
         * @return Array
         */
        public function getModules() {
            return $this->modules;
        }

        /**
         * Setter for pathtemplates.
         *
         * @param Array $pathTemplate
         * @return void
         */
        public function setPathTemplate($pathTemplate) {
            $this->pathTemplate = $pathTemplate;
            if ($this->logger !== null) {
                $this->logger->debug("Set path templates : " . implode(",", $this->modules));
            }
        }

        /**
         * Getter for pathtemplates.
         *
         * @return Array
         */
        public function getPathTemplate() {
            return $this->pathTemplate;
        }

        /**
         * Check the modulename against the given ModuleList.
         *
         * @param $moduleName String
         * @return boolean
         */
        public function isValidModule($moduleName, $matchOnLower = false) {
            $ret = false;

            if ($moduleName != "") {
                if (in_array($moduleName, $this->modules)) {
                    $ret = true;
                }

                if (!$ret && $matchOnLower) {
                    foreach ($this->modules as $mod) {
                        if (strtolower($mod) === $moduleName) {
                            $ret = true;
                            break;
                        }
                    }
                }
            }

            if ($this->logger != null) {
                $this->logger->debug("Validating Module ['${$moduleName}'] => " . ($ret ? "true" : "false"));
            }

            return $ret;
        }

        /**
         * Sets the Container.
         *
         * @param ContainerInterface $container A ContainerInterface instance
         * @return void
         *
         * @api
         */
        public function setContainer(ContainerInterface $container = null) {
            $this->container = $container;

            if ($container != null) {
                $a = array((self::TYPE_IMAGE | self::SCOPE_GLOBAL) => 'terrific_exporter.pathtemplates.image', (self::TYPE_FONT | self::SCOPE_GLOBAL) => 'terrific_exporter.pathtemplates.font', (self::TYPE_CSS | self::SCOPE_GLOBAL) => 'terrific_exporter.pathtemplates.css', (self::TYPE_JS | self::SCOPE_GLOBAL) => 'terrific_exporter.pathtemplates.js', (self::TYPE_VIEW | self::SCOPE_GLOBAL) => 'terrific_exporter.pathtemplates.view',

                    (self::TYPE_IMAGE | self::SCOPE_MODULE) => 'terrific_exporter.pathtemplates.module_image', (self::TYPE_FONT | self::SCOPE_MODULE) => 'terrific_exporter.pathtemplates.module_font', (self::TYPE_CSS | self::SCOPE_MODULE) => 'terrific_exporter.pathtemplates.module_css', (self::TYPE_JS | self::SCOPE_MODULE) => 'terrific_exporter.pathtemplates.module_js', (self::TYPE_VIEW | self::SCOPE_MODULE) => 'terrific_exporter.pathtemplates.module_view');

                foreach ($a as $key => $val) {
                    if ($this->container->hasParameter($val)) {
                        $this->pathTemplate[$key] = $this->container->getParameter($val);
                    }
                }
            }
        }

        /**
         * Cleans the given url.
         *
         * @param $sourcePath String
         * @return String
         */
        public function cleanPath($sourcePath) {
            $ret = $sourcePath;

            $pos = strpos($sourcePath, "?");
            if ($pos !== false) {
                $ret = substr($sourcePath, 0, $pos);
            }

            return $ret;
        }

        /**
         * Retrieves the modulename from the given path.
         *
         * @param $sourcePath String
         * @return String
         */
        public function getModuleName($sourcePath) {
            $dir = dirname($sourcePath);
            $ret = null;

            $matches = array();
            if (preg_match('#/terrificmodule([^/]+)#', $dir, $matches)) {
                $modName = trim($matches[1]);

                foreach ($this->modules as $mod) {
                    if (strtolower($mod) === $modName) {
                        $ret = $mod;
                        break;
                    }
                }

                if ($ret == null) {
                    throw new PathResolveException("Found a modulename but cannot find the correct spelling for module ['${modName}']");
                }
            }

            if ($ret === null) {
                $matches = array();
                if (preg_match('#src/Terrific/Module/([^/]+)#', $dir, $matches)) {
                    $modName = trim($matches[1]);
                    $ret = $modName;
                }
            }

            if ($this->logger !== null) {
                $this->logger->debug("Retrieving modulename from ['${sourcePath}'] => " . $modName);
            }

            return $ret;
        }

        /**
         * Retrieves the Scrope from the path.
         *
         * @param $sourcePath String
         * @return int
         */
        public function getScope($sourcePath) {
            $modName = $this->getModuleName($sourcePath);

            if ($modName != null) {
                $validMod = $this->isValidModule($modName, true);

                if ($modName != "" && !$validMod) {
                    throw new \Exception("Found an invalid Modulename");
                } else if ($modName != "" && $validMod) {
                    return self::SCOPE_MODULE;
                }
            }


            return self::SCOPE_GLOBAL;
        }

        /**
         * Returns the type of the Resource.
         *
         * @param $sourcePath String
         * @return int
         */
        public function getType($sourcePath) {
            $file = basename($sourcePath);

            $ext = strtoupper(pathinfo($file, PATHINFO_EXTENSION));

            switch ($ext) {
                case "GIF":
                case "JPG":
                case "PNG":
                    return PathResolver::TYPE_IMAGE;
                    break;

                case "EOT":
                case "TTF":
                case "WOFF":
                    return PathResolver::TYPE_FONT;
                    break;

                case "CSS":
                    return PathResolver::TYPE_CSS;
                    break;

                case "JS":
                    return PathResolver::TYPE_JS;
                    break;

                case "HTML":
                    return PathResolver::TYPE_VIEW;
                    break;
            }
        }

        /**
         * @param $value
         */
        private function getConstantName($value) {
            $ref = new \ReflectionClass($this);

            foreach ($ref->getConstants() as $name => $val) {
                if ($val == $value) {
                    return $name;
                }
            }

            return "N/A";
        }

        /**
         * Builds a new path for the given path. The Path is generated against configuration settings.
         *
         * @param $sourcePath string
         * @return string
         */
        public function resolve($sourcePath) {
            $ret = "";
            $sourcePath = $this->cleanPath($sourcePath);
            $type = $this->getType($sourcePath);
            $scope = $this->getScope($sourcePath);

            $tpl = $this->pathTemplate[($type | $scope)];

            if ($scope === self::SCOPE_MODULE) {
                $modName = $this->getModuleName($sourcePath);

                $ret = str_replace("%module%", $modName, $tpl);
            } elseif ($scope === self::SCOPE_GLOBAL) {
                $ret = $tpl;
            }

            $ret .= "/" . basename($sourcePath);

            if ($this->logger !== null) {
                $type = $this->getConstantName($type);
                $scope = $this->getConstantName($scope);
                $this->logger->debug("Resolve path ['${sourcePath}'] => ['${ret}'] type=${type}, scope=${scope}");
            }

            return $ret;
        }

        /**
         * Constructor
         */
        public function __construct() {
            $this->pathTemplate = array((self::TYPE_IMAGE | self::SCOPE_GLOBAL) => '/img/common', (self::TYPE_FONT | self::SCOPE_GLOBAL) => '/fonts', (self::TYPE_CSS | self::SCOPE_GLOBAL) => '/css', (self::TYPE_JS | self::SCOPE_GLOBAL) => '/js', (self::TYPE_VIEW | self::SCOPE_GLOBAL) => '/views',

                (self::TYPE_IMAGE | self::SCOPE_MODULE) => '/img/%module%', (self::TYPE_FONT | self::SCOPE_MODULE) => '/fonts/%module%', (self::TYPE_CSS | self::SCOPE_MODULE) => '/css/%module%', (self::TYPE_JS | self::SCOPE_MODULE) => '/js/%module%', (self::TYPE_VIEW | self::SCOPE_MODULE) => '/views/%module%');

            $this->modules = array();
            $this->moduleManager = null;
            $this->container = null;
        }
    }
}
