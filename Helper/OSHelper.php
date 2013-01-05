<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 02.01.13
 * Time: 13:42
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Helper {

    /**
     *
     */
    abstract class OSHelper {

        /**
         *
         */
        public static function isWin() {
            $os = php_uname("s");
            return (stristr(strtoupper($os), "WIN"));
        }
    }
}
