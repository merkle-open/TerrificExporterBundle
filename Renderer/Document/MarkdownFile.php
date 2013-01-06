<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 06.01.13
 * Time: 08:34
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Renderer\Document {
    use Terrific\ExporterBundle\Helper\StringHelper;

    /**
     *
     */
    class MarkdownFile extends AbstractDocumentRenderer {

        private $content = "";

        /**
         *
         */
        public function getContent() {
            return $this->content;
        }

        /**
         * @param $text
         */
        public function text($text) {
            $this->rawText(StringHelper::lineWrap($text, 80));
            return $this;
        }


        /**
         * @param $text
         */
        public function rawText($text) {
            $this->content .= $text;

            return $this;
        }

        /**
         * @param $text
         */
        public function block($text) {
            return $this->rawText(StringHelper::lineWrap($text, 80, "\n    ") . "\n\n");
        }

        /**
         * @param array $list
         */
        public function addList(array $list, $listMarker = "-") {
            if (!in_array($listMarker, array("*", "-", "+", "1"))) {
                throw new \InvalidArgumentException("Valid listmarkers (+,*,-).");
            }

            $i = 0;
            foreach ($list as $item) {
                ++$i;

                if ($listMarker == 1) {
                    $this->rawText(str_pad("${i}.", 4, " ") . StringHelper::lineWrap($item, 80, "\n    ") . "\n");
                } else {
                    $this->rawText(str_pad("${listMarker}", 4, " ") . StringHelper::lineWrap($item, 80, "\n    ") . "\n");
                }
            }

            $this->rawText("\n");

            return $this;
        }

        /**
         * @param $link
         * @param $title
         */
        public function link($link, $title = null) {
            if ($title === null) {
                $title = $link;
            }
            return $this->rawText("[${title}](${link})");
        }

        /**
         * @param $img
         * @param $altText
         */
        public function image($img, $altText) {
            return $this->rawText("![${altText}](${img})");
        }

        /**
         * @param $text
         */
        public function section($text) {
            return $this->rawText("# ${text}\n\n");
        }

        /**
         * @param $text
         * @return MarkdownFile
         */
        public function subsection($text) {
            return $this->rawText("## ${text}\n\n");
        }

        /**
         * @param $text
         * @return MarkdownFile
         */
        public function subsubsection($text) {
            return $this->rawText("### ${text}\n\n");
        }

        /**
         * @param $text
         * @return MarkdownFile
         */
        public function paragraph($text) {
            return $this->rawText("#### ${text}\n\n");
        }

        /**
         * @return MarkdownFile
         */
        public function clear() {
            $this->content = "";
            return $this;
        }


        /**
         *
         */
        public function __construct() {

        }
    }
}
