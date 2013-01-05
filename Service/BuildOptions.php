<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blorenz
 * Date: 13.07.12
 * Time: 09:46
 * To change this template use File | Settings | File Templates.
 */
namespace Terrific\ExporterBundle\Service {
    use ArrayAccess;
    use ArrayObject;

    class BuildOptions implements ArrayAccess {

        private $data = null;
        private $file = null;

        /**
         * @param $file
         */
        public function setFile($file) {
            $this->file = $file;
            if (!file_exists($this->file)) {
                copy(__DIR__ . "/../Resources/config/build.ini", $file);
            }

            $this->data = new ArrayObject(parse_ini_file($this->file, true));
        }

        /**
         * @return null
         */
        public function getFile() {
            return $this->file;
        }

        /**
         *
         * @param $file
         */
        public function __construct() {
        }

        /**
         *
         */
        public function __destruct() {
            $this->save();
        }

        /**
         * @param $path
         */
        private function rebuildPath($path, $parent = ".") {
            $ret = "";
            foreach ($path as $key => $val) {
                if (is_array($val)) {
                    $ret .= $this->rebuildPath($val, $parent . "." . $key);
                } else {
                    if ($val != null) {
                        $ret .= ltrim($parent . "." . $key . "=" . $val . "\n", ".");
                    }
                }
            }


            return $ret;
        }

        /**
         *
         */
        public function save() {
            if (is_array($this->data)) {
                $ret = "";
                foreach ($this->data as $key => $group) {
                    $ret .= sprintf("[%s]", $key) . "\n";
                    if (is_array($group)) {
                        $ret .= $this->rebuildPath($group);
                    }
                    $ret .= "\n\n";
                }

                return (file_put_contents($this->file, $ret) !== false);
            }
        }


        /**
         * @param $offset
         * @return array
         */
        private function buildPath($offset, $root = null) {
            if ($root == null) {
                $root = $this->data;
            }

            $ret = array();
            if (isset($root[$offset])) {
                $ret[] = $offset;
            } else if (trim($offset) != "") {
                $l = explode(".", $offset);

                if (count($l) > 0) {
                    $ret[] = $l[0];
                    $newRoot = (isset($root[$l[0]]) ? $root[$l[0]] : array());
                    $ret = array_merge($ret, $this->buildPath(implode(".", array_slice($l, 1)), $newRoot));
                }
            }

            return $ret;
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Whether a offset exists
         * @link http://php.net/manual/en/arrayaccess.offsetexists.php
         * @param mixed $offset <p>
         * An offset to check for.
         * </p>
         * @return boolean true on success or false on failure.
         * </p>
         * <p>
         * The return value will be casted to boolean if non-boolean was returned.
         */
        public function offsetExists($offset) {
            return ($this->offsetGet($offset) != null);
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to retrieve
         * @link http://php.net/manual/en/arrayaccess.offsetget.php
         * @param mixed $offset <p>
         * The offset to retrieve.
         * </p>
         * @return mixed Can return all value types.
         */
        public function offsetGet($offset) {
            $path = $this->buildPath($offset);

            $root = $this->data;
            foreach ($path as $p) {
                if (isset($root[$p])) {
                    $root = $root[$p];
                } else {
                    $root = null;
                    break;
                }
            }

            return $root;
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to set
         * @link http://php.net/manual/en/arrayaccess.offsetset.php
         * @param mixed $offset <p>
         * The offset to assign the value to.
         * </p>
         * @param mixed $value <p>
         * The value to set.
         * </p>
         * @return void
         */
        public function offsetSet($offset, $value) {
            $path = $this->buildPath($offset);
            $root = & $this->data;

            foreach ($path as $p) {
                if (!isset($root[$p])) {
                    $root[$p] = array();
                }

                $root = & $root[$p];
            }

            $root = $value;
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Offset to unset
         * @link http://php.net/manual/en/arrayaccess.offsetunset.php
         * @param mixed $offset <p>
         * The offset to unset.
         * </p>
         * @return void
         */
        public function offsetUnset($offset) {
            if ($this->offsetExists($offset)) {
                $this[$offset] = null;
            }
        }
    }
}
