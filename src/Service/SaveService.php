<?php


namespace StarterKit\StartBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use StarterKit\StartBundle\Entity\SaveEntityInterface;

/**
 * The point of the class is that sometimes symfony will create multiple instances of the entity manager for doctrine.
 * It's hard to know when to call persist or merge.  So what we do is call merge on non new entities so that entity manager that is working
 * on the them knows about them.  We call persist on new entities because that is the only option.
 *
 * Class SaveService
 * @package StarterKit\StartBundle\Service
 */
class SaveService implements SaveServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * SaveService constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param SaveEntityInterface $entity
     * @return object|SaveEntityInterface
     */
    public function persist(SaveEntityInterface $entity)
    {
        if (!$entity->isNew()) {
            return $this->em->merge($entity);
        }

        $this->em->persist($entity);

        return $entity;
    }

    /**
     * This will save all the entities that are ready to be saved.
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * Saves an entity to the database
     *
     * @param SaveEntityInterface $entity
     *
     * @return SaveEntityInterface
     */
    public function save(SaveEntityInterface $entity)
    {
        $entity = $this->persist($entity);
        $this->flush();

        return $entity;
    }

}