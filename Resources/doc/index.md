VichJmsIntegrationBundle
==================

VichJmsIntegrationBundle provides easy integration in Symfony2 between [VichUploaderBundle](https://github.com/dustin10/VichUploaderBundle)
and [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle) by creating two class annotations in order to generate
the file URL in the serialization event.

## Usage

To use the bundle functionality you must add two annotations to the serialized class:

``` php
<?php

namespace Acme\DemoBundle\Entity;

use VichJmsIntegrationBundle\Annotation as VichJMS;

/**
 * @VichJMS\VichSerializableClass
 */
class Product
{
    /**
     * @VichJMS\VichSerializable("image_file")
     */
    private $image;

    /**
     * @JMS\Exclude
     * @Vich\UploadableField(mapping="vich_mapping_fs", fileNameProperty="image")
     */
    private $image_file;

}

```
