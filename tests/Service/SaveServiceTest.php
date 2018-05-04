<?php

namespace StarterKit\StartBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Service\SaveService;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;

class SaveServiceTest extends BaseTestCase
{
    /**
     * @var SaveService
     */
    protected $saveService;

    /**
     * @var EntityManagerInterface|Mock
     */
    protected $em;

    public function setUp()
    {
        $this->em = \Mockery::mock(EntityManagerInterface::class);
        $this->saveService = new SaveService($this->em);
    }

    /**
     * This tests that merge is called for non new entities
     */
    public function testPreSaveCallsPersistOnNewEntity()
    {
        $user = new User();
        $this->setObjectId($user, 15);

        $this->em->shouldReceive('merge')->once()->with($user)->andReturn($user);

        Assert::assertEquals($user, $this->saveService->persist($user));
    }

    /**
     * This tests that persist is called for new entities
     */
    public function testPersistCallsMergeOnOldEntity()
    {
        $user = new User();

        $this->em->shouldReceive('persist')->once()->with($user)->andReturn($user);

        Assert::assertEquals($user, $this->saveService->persist($user));

    }

    /**
     * Tests flush
     */
    public function testFlush()
    {
        $this->em->shouldReceive('flush')->once()->andReturnNull();

        $this->saveService->flush();
    }

    /**
     * Tests that save is calls merge and flush for an old entity.
     */
    public function testSave()
    {
        $user = new User();
        $this->setObjectId($user, 15);

        $this->em->shouldReceive('merge')->once()->with($user)->andReturn($user);
        $this->em->shouldReceive('flush')->once()->andReturnNull();

        $this->saveService->save($user);

    }
}