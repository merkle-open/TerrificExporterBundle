<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 26.02.13
 * Time: 09:23
 * To change this template use File | Settings | File Templates.
 */

namespace Terrific\ExporterBundle\Helper {

    /**
     *
     */
    abstract class SymfonyHelper {

        private static $ignoredBundles = array("TerrificComposerBundle", "TerrificCoreBundle");


        /**
         * @param $bundleName
         * @param $classPath
         * @return bool
         */
        private static function approveBundleName($bundleName, $classPath) {
            if (!in_array($bundleName, self::$ignoredBundles)) {
                while (strlen($classPath) > 0) {
                    $path = $classPath . DIRECTORY_SEPARATOR . $bundleName . ".php";

                    if (file_exists($path)) {
                        return true;
                    }

                    $classPath = substr($classPath, 0, strrpos($classPath, DIRECTORY_SEPARATOR));
                }
            }

            return false;
        }

        /**
         * Returns the Symfonybundle for given ReflectionClass
         *
         * @param \ReflectionClass $clazz
         * @return string
         */
        public static function getBundleFromNamespace(\ReflectionClass $clazz) {
            $classPath = dirname($clazz->getFileName());
            $ns = $clazz->getNamespaceName();

            $nsData = explode('\\', $ns);

            if (count($nsData) < 2) {
                throw new \InvalidArgumentException("Cannot extrakt Bundlename from " . $ns);
            }

            $bundleName = $nsData[0] . $nsData[1];
            $bundleNameApproved = false;


            if (self::approveBundleName($bundleName, $classPath)) {
                return $bundleName;
            }

            return null;
        }

    }
}
