<?php

/**
 * SuperCache.php
 *
 * @copyright      MIT
 * @author         Shabeer Ali M https://github.com/shabeer-ali-m
 * @since          0.0.9
 *
 */

namespace SuperCache;

/**
 * SuperCache Class
 */
class SuperCache
{

    /**
     * @var string
     */
    const EXT = '.tmp';

    /**
     * @var string
     */
    const OPTIONS = '.attr';

    /**
     * @var SuperCache
     */
    private static $instance;

    /**
     * @var SuperCache
     */
    private $attr_instance;

    /**
     * @var string
     */
    static $static_path = 'tmp/';

    /**
     * @var bolean
     */
    static $is_pretty = true;

    /**
     * @var string
     */
    private $path;

    /**
     * @var Cache Key
     */
    private $key;

    /**
     * @var array
     */
    private $options;

    /**
     * __construct
     * @param [string] $key cache key
     */
    private function __construct($key, $is_super = true)
    {
        if (defined('SuperCache_PATH')) {
            self::$static_path = SuperCache_PATH;
        }

        $this->path = self::$static_path;
        $this->key = $key;
        $this->options = [
            'expiry' => -1,
            'lock' => false,
        ];
        if ($is_super) {
            $this->attr_instance = new self($this->key . SuperCache::OPTIONS, false);
            if ($this->attr_instance->isExists()) {
                $this->options = $this->attr_instance->get();
            }

        }
    }

    /**
     * attrSave Save attributes
     */
    private function attrSave()
    {
        $this->attr_instance->set($this->options);
    }

    /**
     * cache
     * @param  [sting] $key cache key
     * @return SuperCache
     */
    public static function cache($key)
    {
        if (!isset(self::$instance[$key])) {
            self::$instance[$key] = new self($key);
        }
        return self::$instance[$key];
    }

    /**
     * Set File Path
     * @param  [sting] $path file path
     */
    public static function setPath($path)
    {
        self::$static_path = $path;
    }

    /**
     * Get File Path
     * @return  [sting] $path file path
     */
    public static function getPath()
    {
        return self::$static_path;
    }

    /**
     *
     * @param  [bolean] $flag
     */
    public static function setPretty($val)
    {
        self::$is_pretty = (boolean) $val;
    }

    /**
     * Saving cache
     * @param [mixed] $val Cache Value
     * @return SuperCache
     */
    public function set($val)
    {
        $key = $this->key;
        if ($this->options['lock']) {
            return $this;
        }

        $val = var_export($val, true);

        //is_pretty
        if (!self::$is_pretty) {
            $val = str_replace(["\n",",  '"," => "],["",",'","=>"], $val);
        }


        // HHVM fails at __set_state, so just use object cast for now
        $val = str_replace('stdClass::__set_state', '(object)', $val);
        // Write to temp file first to ensure atomicity
        $tmp = $this->path . "$key." . uniqid('', true) . SuperCache::EXT;
        $file = fopen($tmp, 'x');
        fwrite ($file, '<?php $val=' . $val . ';');
        fclose ($file);
        rename($tmp, $this->path . $key);
        return $this;
    }

    /**
     * Retrieving cache value
     * @return [mixed]
     */
    public function get()
    {
        if (!$this->isValid()) {
            return;
        }

        @include $this->path . "$this->key";
        return isset($val) ? $val : false;
    }

    /**
     * getOptions Get Super Cache Options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * isExists
     * @return boolean
     */
    private function isExists()
    {
        if (file_exists($this->path . "$this->key")) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * isValid To check for valid key
     * @return boolean
     */
    public function isValid()
    {
        if ($this->options['expiry'] != -1 && $this->options['expiry'] < time()) {
            return false;
        }

        if (!$this->isExists()) {
            return false;
        }

        return true;
    }

    /**
     * clearAll Removing all cache keys and values
     */
    public static function clearAll()
    {
        $files = glob(self::$static_path . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }

        }
    }

    /**
     * lock specific cache
     * @return SuperCache
     */
    public function lock()
    {
        $this->options['lock'] = true;
        $this->attrSave();
        return $this;
    }

    /**
     * unlock specific cache
     * @return SuperCache
     */
    public function unlock()
    {
        $this->options['lock'] = false;
        $this->attrSave();
        return $this;
    }

    /**
     * options
     * @param  array $options
     * @return  SuperCache
     */
    public function options($options)
    {
        $this->options = array_merge($this->options, $options);
        $this->attrSave();
        return $this;
    }

    /**
     * destroy Destroy a specific key
     */
    public function destroy()
    {
        @unlink($this->path . "$this->key");
        @unlink($this->path . "$this->key" . SuperCache::OPTIONS);
    }
}
