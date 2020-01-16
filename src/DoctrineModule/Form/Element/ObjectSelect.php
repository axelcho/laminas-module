<?php

namespace DoctrineModule\Form\Element;

use Laminas\Form\Element;
use Laminas\Form\Element\Select as SelectElement;
use Laminas\Stdlib\ArrayUtils;
use Traversable as TraversableAlias;
use Laminas\Form\ElementInterface;

class ObjectSelect extends SelectElement
{
    /**
     * @var Proxy
     */
    protected $proxy;

    /**
     * @return Proxy
     */
    public function getProxy()
    {
        if (null === $this->proxy) {
            $this->proxy = new Proxy();
        }
        return $this->proxy;
    }

    /**
     * @param  array|TraversableAlias $options
     * @return SelectElement|ElementInterface
     */
    public function setOptions($options)
    {
        $this->getProxy()->setOptions($options);
        return parent::setOptions($options);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Element
     */
    public function setOption($key, $value)
    {
        $this->getProxy()->setOptions([$key => $value]);
        return parent::setOption($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        $multiple = $this->getAttribute('multiple');

        if (true === $multiple || 'multiple' === $multiple) {
            if ($value instanceof TraversableAlias) {
                $value = ArrayUtils::iteratorToArray($value);
            } elseif ($value == null) {
                return parent::setValue([]);
            } elseif (! is_array($value)) {
                $value = (array) $value;
            }

            return parent::setValue(array_map([$this->getProxy(), 'getValue'], $value));
        }

        return parent::setValue($this->getProxy()->getValue($value));
    }

    /**
     * {@inheritDoc}
     */
    public function getValueOptions()
    {
        if (! empty($this->valueOptions)) {
            return $this->valueOptions;
        }

        $proxyValueOptions = $this->getProxy()->getValueOptions();

        if (! empty($proxyValueOptions)) {
            $this->setValueOptions($proxyValueOptions);
        }

        return $this->valueOptions;
    }
}
