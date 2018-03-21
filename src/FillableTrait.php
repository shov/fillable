<?php declare(strict_types=1);

namespace Shov\Helpers\Mixins;

/**
 * Help to fill host object by given values
 */
trait FillableTrait
{
    /**
     * Fill host by given entity
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

        return $this;
    }

    /**
     * Fill host by given entity, aggressive trying
     * to fetch only properties which host need
     * @param $source
     * @param null $default
     * @return self
     */
    public function fillPropsBy($source, $default = null)
    {
        $keysToFetch = array_keys(get_object_vars($this));

        if(!is_object($source) && !is_array($source)) {
            $source = array_fill_keys($keysToFetch, $source);
        }

        foreach ($keysToFetch as $key) {

            //Magic methods handling
            if (is_object($source)) {
                $value = $source->{$key} ?? $default;
            } else {
                $value = $source[$key] ?? $default;
            }

            if(is_null($value)) {
                continue;
            }

            $setterName = [$this, 'set' . ucfirst($key)];

            if (is_callable($setterName)) {
                call_user_func($setterName, $value);
            } else {
                $this->{$key} = $value;
            }
        }

        return $this;
    }
}