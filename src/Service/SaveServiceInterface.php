<?php


namespace StarterKit\StartBundle\Service;


use StarterKit\StartBundle\Entity\SaveEntityInterface;

/**
 *
 * Interface SaveServiceInterface
 * @package StarterKit\StartBundle\Service
 */
interface SaveServiceInterface
{
    /**
     * This will be set the entity up to be saved
     *
     * @param SaveEntityInterface $entity
     * @return SaveEntityInterface
     */
    public function persist(SaveEntityInterface $entity);

    /**
     * This will save all the entities that have been
     *
     * @return void
     */
    public function flush();


    /**
     * Saves a single entity to the database
     *
     * @param SaveEntityInterface $entity
     * @return SaveEntityInterface
     */
    public function save(SaveEntityInterface $entity);
}