<?php

namespace DoctrineModule\Form\Element;

use Laminas\Form\Element;
use Laminas\Form\Element\MultiCheckbox;
use Laminas\Form\ElementInterface;
use Laminas\Stdlib\ArrayUtils;
use Traversable as TraversableAlias;

class ObjectMultiCheckbox extends MultiCheckbox
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
     * @param array|TraversableAlias $options
     * @return MultiCheckbox|ElementInterface
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
        if ($value instanceof TraversableAlias) {
            $value = ArrayUtils::iteratorToArray($value);
        } elseif ($value == null) {
            return parent::setValue([]);
        } elseif (! is_array($value)) {
            $value = (array)$value;
        }

        return parent::setValue(array_map([$this->getProxy(), 'getValue'], $value));
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
