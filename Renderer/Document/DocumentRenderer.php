<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 06.01.13
 * Time: 13:23
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Renderer\Document {

    /**
     *
     */
    abstract class DocumentRenderer {

        /**
         * @param $type
         * @return IDocumentRenderer
         */
        public static function factory($type) {
            if (class_exists($type)) {
                return new $type();
            }

            throw new \InvalidArgumentException(sprintf("No document renderer [%s] found.", $type));
        }
    }
}
