<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 06.01.13
 * Time: 13:18
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Renderer\Document {


    abstract class AbstractDocumentRenderer implements IDocumentRenderer{

        /**
         * @param $file
         */
        public function save($file) {
            $data = $this->getContent();
            file_put_contents($file, $data);
        }
    }
}
