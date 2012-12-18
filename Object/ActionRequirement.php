<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 18.12.12
 * Time: 10:29
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Object {
    use ReflectionClass;

    /**
     *
     */
    class ActionRequirement {
        const TYPE_PROCESS = 0;
        const TYPE_RUNNEDACTION = 1;
        const TYPE_SETTING = 2;
        const TYPE_PHPEXT = 3;

        /** @var String */
        private $name;

        /** @var  ReflectionClass */
        private $action;

        /** @var Integer */
        private $type;

        /**
         * @return \ReflectionClass
         */
        public function getAction() {
            return $this->action;
        }

        /**
         * @return String
         */
        public function getName() {
            return $this->name;
        }

        /**
         * @return int
         */
        public function getType() {
            return $this->type;
        }


        /**
         *
         * @param String $name
         * @param Integer $type
         * @param Object $object
         */
        public function __construct($name, $type, $object) {
            $this->name = $name;
            $this->type = $type;
            $this->action = new ReflectionClass($object);
        }

    }
}
