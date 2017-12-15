<?php

namespace StarterKit\StartBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;

/**
 * Class LoadData
 * @package StarterKit\StartBundle\DataFixtures\ORM
 */
class LoadData implements FixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $loader = new NativeLoader();
        $objectSet = $loader->loadFile(__DIR__ . '/users.yml');
        foreach ($objectSet->getObjects() as $object) {
            $manager->persist($object);
        }
        $manager->flush();
    }
}