<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 11.12.12
 * Time: 22:26
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Helper {

    /**
     *
     */
    abstract class StringHelper {
        private static $replace = array("/" => "_", "&" => "_", "#" => "_", "@" => "_", "?" => "_", "\\" => "_");

        /**
         * Removes all symbols from a filename.
         *
         * @param $label
         */
        public static function escapeFileLabel($label) {
            foreach (self::$replace as $char => $replacement) {
                $label = str_replace($char, $replacement, $label);
            }

            return $label;
        }

    }
}
