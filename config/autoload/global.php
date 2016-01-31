<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'service_manager' => [
        'abstract_factories' => [
            'ImgMan\Service\ImageServiceAbstractFactory',
            'ImgMan\Storage\Adapter\Mongo\MongoDbAbstractServiceFactory',
            'ImgMan\Storage\Adapter\Mongo\MongoAdapterAbstractServiceFactory'
        ],
        'factories' => [
            'ImgMan\PluginManager' => 'ImgMan\Operation\HelperPluginManagerFactory',
        ],
        'invokables' => [
            'ImgMan\Adapter\Imagick'  => 'ImgMan\Core\Adapter\ImagickAdapter',
        ]
    ],
    'imgman_mongodb' => [
        'Mongo\Db\Image' => [
            'hosts' => 'mongo:27017',
            'database' => 'testprontopro-image',
        ],
    ],
    'imgman_adapter_mongo' => [
        'ImgMan\Mongo\ProductImage' => [
            'collection' => 'product-image',
            'database' => 'Mongo\Db\Image',
            'identifier' => 'identifier',
        ],
    ],
    'imgman_services' => [
        'ImgMan\Service\ProductImage' => [
            'adapter' => 'ImgMan\Adapter\Imagick',
            'storage' => 'ImgMan\Mongo\ProductImage',
            'helper_manager' => 'ImgMan\PluginManager',
            'renditions' => [
                'thumb' => [
                    'fitOut' => [
                        'width' => 30,
                        'height' => 30
                    ],
                    'format' => [
                        'format' => 'jpeg' // FIXME? png
                    ],
                ],
                'normal' => [
                    'fitOut' => [
                        'width' => 100,
                        'height' => 100,
                        'allowUpsample' => true,
                    ],
                    'format' => [
                        'format' => 'jpeg' // FIXME? png
                    ],
                ],
            ],
        ],
    ],
);
