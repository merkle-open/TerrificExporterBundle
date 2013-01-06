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


        /**
         *
         */
        public static function lineWrap($content, $maxChars = 80, $lineEnding = "\n") {
            $words = explode(" ", $content);

            $ret = "";
            $cLine = 0;

            foreach ($words as $w) {
                $len = mb_strlen($w);
                if ($cLine + $len > $maxChars) {
                    $ret = rtrim($ret, " ");
                    $ret .= $lineEnding;
                    $cLine = 0;
                }

                $ret .= $w . " ";
                $cLine += $len;
            }

            return rtrim($ret, " ");
        }
    }
}
