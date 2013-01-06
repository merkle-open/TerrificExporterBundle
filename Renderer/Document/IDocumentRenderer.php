<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 06.01.13
 * Time: 13:15
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Renderer\Document {


    /**
     *s
     */
    interface IDocumentRenderer {

        /**
         * @param $text
         * @return IDocumentRenderer
         */
        public function section($text);

        /**
         * @param $text
         * @return IDocumentRenderer
         */
        public function subsection($text);

        /**
         * @param $text
         * @return IDocumentRenderer
         */
        public function subsubsection($text);

        /**
         * @param $text
         * @return IDocumentRenderer
         */
        public function paragraph($text);

        /**
         * @param $text
         * @return IDocumentRenderer
         */
        public function text($text);

        /**
         * @param $text
         * @return IDocumentRenderer
         */
        public function rawText($text);

        /**
         * @param array $items
         * @param $listMarker
         * @return IDocumentRenderer
         */
        public function addList(array $items, $listMarker);

        /**
         * @param $text
         * @return IDocumentRenderer
         */
        public function block($text);

        /**
         * @return IDocumentRenderer
         */
        public function clear();

        /**
         * @return string
         */
        public function getContent();

        /**
         * @param $file
         */
        public function save($file);

    }
}
