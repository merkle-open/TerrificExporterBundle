<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 12.11.12
 * Time: 10:47
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Object {


    /**
     *
     */
    class ActionResult
    {
        const OK = 0x00;
        const TRY_AGAIN = 0x01;
        const STOP = 0x02;

        /**
         * @var int
         */
        protected $resultCode = 0;

        /**
         * @return int
         */
        public function getResultCode()
        {
            return $this->resultCode;
        }

        /**
         *
         */
        public function __construct($resultCode)
        {
            $this->resultCode = $resultCode;
        }
    }
}
