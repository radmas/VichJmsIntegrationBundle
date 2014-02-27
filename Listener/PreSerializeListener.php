<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 25/02/14
 * Time: 18:14
 */

namespace VichJmsIntegrationBundle\Listener;

use Doctrine\Common\Annotations\FileCacheReader;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Metadata\ClassMetadata;
use Radmas\Open010Bundle\Annotation\VichSerializable;
use Radmas\Open010Bundle\Annotation\VichSerializableClass;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class PreSerializeListener
 *
 * @author Jorge García (draco19892002@gmail.com)
 */
class PreSerializeListener implements EventSubscriberInterface {

    private static $FIELD_ANNOTATION = 'Radmas\Open010Bundle\Annotation\VichSerializable';

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
            get_class(new VichSerializableClass())
        );

        if(!is_null($serializable)) {
            $class = new \ReflectionClass($event->getObject());

            foreach($class->getProperties() as $property){
                $property_annotation = $this->annotations->getPropertyAnnotation($property, self::$FIELD_ANNOTATION);

                if(!is_null($property_annotation) && $property_annotation instanceof VichSerializable){
                    $field = $property_annotation->getField();

                    if($field){
                        $url = $this->vich->asset($event->getObject(), $property_annotation->getField());

                        $method = 'set' . str_replace(' ', '',ucwords(str_replace('_', ' ', $property->getName())));

                        $event->getObject()->$method($url);
                    }
                }
            }
        }
    }
}