<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 25/02/14
 * Time: 18:14
 */

namespace Radmas\VichJmsIntegrationBundle\Listener;

use Doctrine\Common\Annotations\FileCacheReader;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Metadata\ClassMetadata;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class PreSerializeListener
 *
 * @author Jorge García (draco19892002@gmail.com)
 */
class PreSerializeListener implements EventSubscriberInterface {

    private static $FIELD_ANNOTATION = 'Radmas\VichJmsIntegrationBundle\Annotation\VichSerializable';
    private static $CLASS_ANNOTATION = 'Radmas\VichJmsIntegrationBundle\Annotation\VichSerializableClass';

    private $vich;
    private $annotations;

    function __construct(UploaderHelper $vich, FileCacheReader $annotations)
    {
        $this->annotations = $annotations;
        $this->vich = $vich;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize')
        );
    }

    public function onPreSerialize(ObjectEvent $event){
        $serializable = $this->annotations->getClassAnnotation(
            new \ReflectionClass($event->getObject()),
            self::$CLASS_ANNOTATION
        );

        if(!is_null($serializable)) {
            $class = new \ReflectionClass($event->getObject());

            foreach($class->getProperties() as $property){
                $property_annotation = $this->annotations->getPropertyAnnotation($property, self::$FIELD_ANNOTATION);

                if(!is_null($property_annotation)){
                    $field = $property_annotation->getField();
                    $setMethod = 'set' . $this->fieldToMethod($property->getName());
                    $getMethod = 'get' . $this->fieldToMethod($property->getName());

                    if($field && $event->getObject()->$getMethod()){
                        $url = $this->vich->asset($event->getObject(), $property_annotation->getField());

                        $event->getObject()->$setMethod($url);
                    }
                }
            }
        }
    }

    private function fieldToMethod($field){
        return str_replace(' ', '',ucwords(str_replace('_', ' ', $field)));
    }
}
