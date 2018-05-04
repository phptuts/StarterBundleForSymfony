<?php


namespace StarterKit\StartBundle\Entity;

/**
 * This is used by the SaveService to see if we need to call merge or persist.
 *
 * Interface SaveEntityInterface
 * @package StarterKit\StartBundle\Entity
 */
interface SaveEntityInterface
{
    /**
     * Returns the id associated with the entity
     *
     * @return null|string|integer
     */
    public function isNew();
}