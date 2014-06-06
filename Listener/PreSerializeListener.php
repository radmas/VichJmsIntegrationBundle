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
use Monolog\Logger;
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
    private $logger;

    function __construct(UploaderHelper $vich, FileCacheReader $annotations, Logger $logger)
    {
        $this->annotations = $annotations;
        $this->vich = $vich;
        $this->logger = $logger;
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
            $this->logger->debug('Detected vich-jms serializable class ==>' . $class->getName());

            foreach($class->getProperties() as $property){
                $property_annotation = $this->annotations->getPropertyAnnotation($property, self::$FIELD_ANNOTATION);

                if(!is_null($property_annotation)){
                    $this->logger->debug('Serializing ' . $property->getName() . ' property from ' . $class->getName());

                    $property->setAccessible(true);

                    if($property_annotation->getField() && !preg_match('/https{0,1}:\/\//', $property->getValue($event->getObject()))){

                        if($property->getValue($event->getObject())){
                            $url = $this->vich->asset($event->getObject(), $property_annotation->getField());
                            $this->logger->debug('AmazonS3 base URL is: ' . $url);

                            $property->setAccessible(true);
                            $property->setValue($event->getObject(), $url);
                        }
                    }
                }
            }
        }
    }
}
