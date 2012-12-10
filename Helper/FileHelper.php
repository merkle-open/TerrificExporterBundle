<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 10.12.12
 * Time: 08:06
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Helper {


    /**
     *
     */
    abstract class FileHelper {

        /**
         * Returns true if the given filename is a javascript file.
         *
         * @param String $file
         * @return Boolean
         */
        public static function isJavascript($file) {
            return (strtolower(substr($file, -3)) == ".js");
        }

        /**
         * Returns true if the given filename is a cascading style sheet.
         *
         * @param String $file
         * @return Boolean
         */
        public static function isStylesheet($file) {
            return (strtolower(substr($file, -4)) == ".css");
        }
    }
}
