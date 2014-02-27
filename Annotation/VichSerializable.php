<?php

namespace VichJmsIntegrationBundle\Annotation;

/**
 * VichSerializable
 *
 * @Annotation
 *
 * @author Jorge García (draco19892002@gmail.com)
 */
class VichSerializable {

    private $field;

    public function __construct($options){
        if(!isset($options['value']) && ! isset($options['name'])){
            throw new \Exception('VichSerializable annotation requires "name" attribute');
        }

        if(isset($options['value'])) { $this->setField($options['value']); }
        if(isset($options['name'])) { $this->setField($options['name']); }
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }


} 