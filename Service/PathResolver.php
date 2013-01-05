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
    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Finder\SplFileInfo;
    use Terrific\ExporterBundle\Helper\FileHelper;

    /**
     *
     */
    class PathResolver implements ContainerAwareInterface {
        const TYPE_IMAGE = 0x00;
        const TYPE_FONT = 0x01;
        const TYPE_VIEW = 0x02;
        const TYPE_CSS = 0x04;
        const TYPE_JS = 0x08;
        const TYPE_DIFF = 0x10;
        const TYPE_CHANGELOG = 0x20;

        const SCOPE_GLOBAL = 0x200;
        const SCOPE_MODULE = 0x100;

        /** @var ContainerInterface */
        protected $container;

        /** @var ModuleManager */
        protected $moduleManager;

        /** @var Array */
        protected $pathTemplate;

        /** @var Array */
        protected $modules = array();

        /** var LoggerInterface */
        protected $logger;

        /** @var FileLocator */
        protected $fileLocator;

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
                $moduleList = array();
                foreach ($this->moduleManager->getModules() as $module) {
                    $moduleList[] = $module->getName();
                }
                $this->setModules(array_unique($moduleList));
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
                $this->logger->debug("Validating Module ['${moduleName}'] => " . ($ret ? "true" : "false"));
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

            if ($container != null && $container->hasParameter("terrific_exporter")) {
                $config = $this->container->getParameter("terrific_exporter");
                if (isset($config["pathtemplates"])) {
                    $config = $config["pathtemplates"];

                    $a = array();
                    $a[(self::TYPE_IMAGE | self::SCOPE_GLOBAL)] = 'image';
                    $a[(self::TYPE_FONT | self::SCOPE_GLOBAL)] = 'font';
                    $a[(self::TYPE_CSS | self::SCOPE_GLOBAL)] = 'css';
                    $a[(self::TYPE_JS | self::SCOPE_GLOBAL)] = 'js';
                    $a[(self::TYPE_VIEW | self::SCOPE_GLOBAL)] = 'view';
                    $a[(self::TYPE_IMAGE | self::SCOPE_MODULE)] = 'module_image';
                    $a[(self::TYPE_FONT | self::SCOPE_MODULE)] = 'module_font';
                    $a[(self::TYPE_CSS | self::SCOPE_MODULE)] = 'module_css';
                    $a[(self::TYPE_JS | self::SCOPE_MODULE)] = 'module_js';
                    $a[(self::TYPE_VIEW | self::SCOPE_MODULE)] = 'module_view';
                    $a[(self::TYPE_VIEW | self::SCOPE_MODULE)] = 'module_view';
                    $a[(self::TYPE_CHANGELOG | self::SCOPE_GLOBAL)] = '/changelogs';
                    $a[(self::TYPE_DIFF | self::SCOPE_GLOBAL)] = '/changelogs/diff';


                    foreach ($a as $key => $val) {
                        if (!empty($config[$val])) {
                            $this->pathTemplate[$key] = $config[$val];
                        }
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
            $modName = "";

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
                    return self::TYPE_IMAGE;
                    break;

                case "EOT":
                case "TTF":
                case "WOFF":
                    return self::TYPE_FONT;
                    break;

                case "CSS":
                    return self::TYPE_CSS;
                    break;

                case "JS":
                    return self::TYPE_JS;
                    break;

                case "HTML":
                    return self::TYPE_VIEW;
                    break;

                case "DIFF":
                    return self::TYPE_DIFF;
                    break;

                case "MD":
                case "TXT":
                case "LOG":
                    return self::TYPE_CHANGELOG;
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
         * @param $file
         */
        public function locate($file, $assertedPath = "") {
            $this->initialize();

            $assertedPath = ltrim($assertedPath, ".");

            if (strpos($assertedPath, "/terrificmodule") !== false) {
                $modName = $this->getModuleName($assertedPath);

                $modPath = "/terrificmodule" . strtolower($modName) . "/";
                $pos = strpos($assertedPath, $modPath) + strlen($modPath);
                $path = substr($assertedPath, $pos);

                $nPath = "src/Terrific/Module/" . $modName . "/Resources/public/" . $path;

                $assertedPath = $nPath;
            }


            $locatedFiles = $this->fileLocator->locate($file, null, false);

            $ret = array();
            foreach ($locatedFiles as $file) {
                $file = realpath($file);
                $found = (strpos($file, $assertedPath) !== false);

                if ($this->logger) {
                    $this->logger->debug(sprintf("Search for '%s' in '%s' => %s", $assertedPath, $file, ($found ? 'true' : 'false')));
                }

                if ($found) {
                    $ret[] = $file;
                }
            }

            if (count($ret) == 1) {
                return $ret[0];
            }

            if ($this->logger) {
                $this->logger->err(print_r($locatedFiles, true));
            }
            throw new \Exception("Cannot identify single path for asset");
        }

        /**
         * Builds a new path for the given path. The Path is generated against configuration settings.
         *
         * @param $sourcePath string
         * @param $forcedScope int
         * @param $forcedType int
         * @return string
         */
        public function resolve($sourcePath, $forcedScope = null, $forcedType = null) {
            $ret = "";
            $sourcePath = $this->cleanPath($sourcePath);
            $scope = ($forcedScope != null ? $forcedScope : $this->getScope($sourcePath));
            $type = ($forcedType != null ? $forcedType : $this->getType($sourcePath));

            $tpl = $this->pathTemplate[($type | $scope)];

            if ($scope === self::SCOPE_MODULE) {
                $modName = $this->getModuleName($sourcePath);

                $ret = str_replace("%module%", $modName, $tpl);
            } elseif ($scope === self::SCOPE_GLOBAL) {
                $ret = $tpl;
            }

            if (FileHelper::isImage($sourcePath)) {
                $ret .= "/" . substr($sourcePath, strpos($sourcePath, "img/") + 4);
            } else {
                if (strpos($sourcePath, "dependencies") !== false) {
                    $ret .= "/dependencies/" . substr($sourcePath, strpos($sourcePath, "dependencies/") + 13);
                } else {
                    $ret .= "/" . basename($sourcePath);
                }
            }

            $ret = FileHelper::cleanPath($ret);

            if ($this->logger !== null) {
                $type = $this->getConstantName($type);
                $scope = $this->getConstantName($scope);
                $this->logger->debug("Resolve path ['${sourcePath}'] => ['${ret}'] type=${type}, scope=${scope}");
            }

            return $ret;
        }

        /**
         *
         */
        protected function initialize() {
            if ($this->fileLocator == null) {
                if ($this->container && count($this->modules)) {
                    $finder = new Finder();

                    /** @var $fs Filesystem */
                    $fs = $this->container->get("filesystem");

                    $root_dir = $this->container->getParameter("kernel.root_dir");
                    $locations = array($root_dir . "/../web/img", $root_dir . "/../web/font");


                    $root_dir .= "/../src/Terrific/Module";
                    foreach ($this->modules as $module) {
                        $locations[] = $root_dir . "/${module}/Resources/public/img";
                        $locations[] = $root_dir . "/${module}/Resources/public/font";
                    }

                    $locations = array_filter($locations, function ($itm) use ($fs) {
                        return $fs->exists($itm);
                    });

                    $finder->in($locations);

                    /** @var $dir SplFileInfo */
                    foreach ($finder->directories() as $dir) {
                        $locations[] = $dir->getPathname();
                    }

                    if ($this->logger) {
                        $this->logger->debug(sprintf("Use pathlist for location [ %s ].", implode(", ", $locations)));
                    }
                    $this->fileLocator = new FileLocator($locations);
                }
            }
        }

        /**
         * Constructor
         */
        public function __construct() {
            $this->pathTemplate = array();

            $this->pathTemplate[(self::TYPE_IMAGE | self::SCOPE_GLOBAL)] = '/img/common';
            $this->pathTemplate[(self::TYPE_FONT | self::SCOPE_GLOBAL)] = '/fonts';
            $this->pathTemplate[(self::TYPE_CSS | self::SCOPE_GLOBAL)] = '/css';
            $this->pathTemplate[(self::TYPE_JS | self::SCOPE_GLOBAL)] = '/js';
            $this->pathTemplate[(self::TYPE_VIEW | self::SCOPE_GLOBAL)] = '/views';
            $this->pathTemplate[(self::TYPE_IMAGE | self::SCOPE_MODULE)] = '/img/%module%';
            $this->pathTemplate[(self::TYPE_FONT | self::SCOPE_MODULE)] = '/fonts/%module%';
            $this->pathTemplate[(self::TYPE_CSS | self::SCOPE_MODULE)] = '/css/%module%';
            $this->pathTemplate[(self::TYPE_JS | self::SCOPE_MODULE)] = '/js/%module%';
            $this->pathTemplate[(self::TYPE_VIEW | self::SCOPE_MODULE)] = '/views/%module%';
            $this->pathTemplate[(self::TYPE_CHANGELOG | self::SCOPE_GLOBAL)] = '/changelogs';
            $this->pathTemplate[(self::TYPE_DIFF | self::SCOPE_GLOBAL)] = '/changelogs/diff';


            $this->modules = array();
            $this->moduleManager = null;
            $this->container = null;
        }
    }
}