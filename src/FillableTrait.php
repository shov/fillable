<?php declare(strict_types=1);

namespace Shov\Helpers\Mixins;

/**
 * Help to fill host object by given values
 */
trait FillableTrait
{
    /** @var array */
    protected $__queryOnly = [];

    /** @var array */
    protected $__queryExclude = [];

    /**
     * Fill host by given entity
     * skip the query
     * @param array|object|mixed $source
     * @param string $name
     * @param bool $dynamicProps
     * @return self
     */
    public function fillBy($source, $name = 'data', $dynamicProps = false)
    {
        if (is_object($source)) {
            $source = get_object_vars($source);
        } elseif (!is_array($source)) {
            $source = [$name => $source];
        }

        $publicFields = array_keys(get_object_vars($this));

        foreach ($source as $key => $value) {
            if (!$this->allowedKeyByQuery($key)) {
                continue;
            }

            if (is_numeric($key)) {
                $this->{$name}[$key] = $value;
                continue;
            }

            $setterName = [$this, 'set' . ucfirst($key)];

            if (is_callable($setterName)) {
                call_user_func($setterName, $value);

            } else {
                if (in_array($key, $publicFields) || $dynamicProps) {
                    $this->{$key} = $value;
                    continue;
                }

                $this->{$name}[$key] = $value;
            }
        }

        $this->skipQuery();

        return $this;
    }

    /**
     * Fill host by given entity, aggressive trying
     * to fetch only properties which host need
     * skip the query
     * @param $source
     * @param null $default
     * @return self
     */
    public function fillPropsBy($source, $default = null)
    {
        $keysToFetch = array_filter(
            array_keys(get_object_vars($this)),
            function ($key) {
                return ('__' !== substr($key, 0, 2));
            });

        if (!is_object($source) && !is_array($source)) {
            $source = array_fill_keys($keysToFetch, $source);
        }

        foreach ($keysToFetch as $key) {
            if (!$this->allowedKeyByQuery($key)) {
                continue;
            }

            //Magic methods handling
            if (is_object($source)) {

                $getterName = [$source, 'get' . ucfirst($key)];

                if (is_callable($getterName)) {
                    $value = call_user_func($getterName);
                } else {
                    $value = $source->{$key} ?? $default;
                }

            } else {
                $value = $source[$key] ?? $default;
            }

            if (is_null($value)) {
                continue;
            }

            $setterName = [$this, 'set' . ucfirst($key)];

            if (is_callable($setterName)) {
                call_user_func($setterName, $value);
            } else {
                $this->{$key} = $value;
            }
        }

        $this->skipQuery();

        return $this;
    }

    /**
     * Set a query to fetch only given key(s)
     * will skipped after fetch
     * @param string|array $keys
     * @return self
     */
    public function only($keys)
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        if (!is_array($keys)) {
            throw new \InvalidArgumentException("Keys that only() expects must be string or array!");
        }

        $this->__queryOnly = array_merge($this->__queryOnly, $keys);

        return $this;
    }

    /**
     * Set a query to exclude some keys from the filling process
     * to take no data from them
     * @param string|array $keys
     * @return self
     */
    public function exclude($keys)
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        if (!is_array($keys)) {
            throw new \InvalidArgumentException("Keys that exclude() expects must be string or array!");
        }

        $this->__queryExclude = array_merge($this->__queryExclude, $keys);

        return $this;
    }

    /**
     * Immediately skip all queries
     * @return self
     */
    public function skipQuery()
    {
        $this->__queryOnly = [];
        $this->__queryExclude = [];
        return $this;
    }

    /**
     * Check is given key allowed with set query
     * @param $key
     * @return bool
     */
    protected function allowedKeyByQuery($key)
    {
        if (!empty($this->__queryOnly)) {
            if (!in_array($key, $this->__queryOnly)) {
                return false;
            }
        }

        if (!empty($this->__queryExclude)) {
            if (in_array($key, $this->__queryExclude)) {
                return false;
            }
        }

        return true;
    }
}