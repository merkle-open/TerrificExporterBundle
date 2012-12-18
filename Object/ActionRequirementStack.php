<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 18.12.12
 * Time: 12:21
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Object {
    use Terrific\ExporterBundle\Object\ActionRequirement;

    /**
     *
     */
    class ActionRequirementStack {

        /** @var array */
        private $stack = array();

        /**
         * @param bool $grouped
         */
        public function getStack($grouped = false) {
            if (!$grouped) {
                return $this->stack;
            }

            $ret = array();

            /** @var $stack ActionRequirement */
            foreach ($this->stack as $stack) {
                $id = $stack->getName() . "::" . $stack->getType();
                if (!isset($ret[$id])) {
                    $ret[$id] = $stack;
                }
            }

            return array_values($ret);
        }

        /**
         * @param ActionRequirement $req
         */
        public function findAffectedActions(ActionRequirement $req) {
            $ret = array();

            /** @var $sReq ActionRequirement */
            foreach ($this->stack as $sReq) {
                if ($req->getName() == $sReq->getName() && $req->getType() == $sReq->getType()) {
                    $ret[] = $sReq->getAction();
                }
            }

            return $ret;
        }

        /**
         * @param array $stacks
         */
        public function addStacks(array $stacks) {
            $this->stack = array_merge($this->stack, $stacks);
        }

    }
}
