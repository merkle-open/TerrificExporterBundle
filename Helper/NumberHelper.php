<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 28.12.12
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */

namespace Terrific\ExporterBundle\Helper {


    /**
     *
     */
    abstract class NumberHelper {
        private static $unit = array("Byte", "kByte", "MByte");

        /**
         * @param $bytes
         * @return String
         */
        public static function formatBytes($bytes) {

            $unitval = 0;
            while ($bytes >= 1024) {
                $bytes = $bytes / 1024;
                ++$unitval;
            }

            if ($unitval === 0) {
                return sprintf("%d %s", $bytes, self::$unit[$unitval]);
            }

            return sprintf("%.2f %s", $bytes, self::$unit[$unitval]);
        }
    }

}
