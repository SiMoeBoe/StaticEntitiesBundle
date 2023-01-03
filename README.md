StaticEntitiesBundle
====================

This package helps you with data that is needed for production but to complex to add this data by migration files.

It is similar to fixtures but for production data instead of test data.

You can define new data using real entities and run the sync command after running your migrations.

The defined data will be updated, removed or new created if necessary.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require simoeboe/simoeboe-static-entities-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require simoeboe/simoeboe-static-entities-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Simoeboe\StaticEntitiesBundle\SimoeboeStaticEntitiesBundle::class => ['all' => true],
];
```

How to use
==========

Add new creator classes that extends `Simoeboe\StaticEntitiesBundle\StaticEntityCreator` and fill the protected functions:

```php
<?php

namespace App\StaticEntityCreators;

use App\Entity\Role;
use Simoeboe\StaticEntitiesBundle\StaticEntityCreator;

final class Roles extends StaticEntityCreator
{

    /** 
     * Returns the FQCN (fully-qualified class name) of a Doctrine ORM entity
     */
    protected function getEntityFqcn(): string
    {
        return Role::class;
    }
    
    /**
     * Returns getter method which should be used to compare existing with new elements to check if the element should be updated or created
     */
    protected function getIdentifierMethod(): string
    {
        return 'getName';
    }

    /**
     * Merge to elements together to update existing elements instead of recreate them
     *
     * @param object $persistElem The existing element
     * @param object $newData The new element
     */
    protected function merge(object $persistElem, object $newData): void
    {
        /**
         * @var Role $persistElem
         * @var Role $newData
         */
        $persistElem
            ->setDescription($newData->getDescription())
        ;
    }

    /**
     * Returns an array of configured entities of the same type as the $this->getEntityFqcn() function
     */
    protected function getElements(): array
    {
        return [
            (new Role())
                ->setName('Admin')
                ->setDescription('Can more role'),
            (new Role())
                ->setName('Superadmin')
                ->setDescription('Can all role')
        ];
    }
}
```

After creating the creator files you can execute the sync command to insert the data to your database:

```shell
php bin/console static-entities:sync
```