<?php

namespace Simoeboe\StaticEntitiesBundle;

use Doctrine\ORM\EntityManagerInterface;
use Simoeboe\StaticEntitiesBundle\Filter\KnownByNameFilter;
use Simoeboe\StaticEntitiesBundle\Filter\UnknownByFilter;
use ArrayIterator;
use Simoeboe\StaticEntitiesBundle\Exception\UnsupportedElementException;

abstract class StaticEntityCreator implements StaticEntityCreatorInterface
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
        $this->validate();
    }

    /**
     * @throws UnsupportedElementException
     */
    private function validate(): void
    {
        $elements = $this->getElements();
        foreach ($elements as $element) {
            if (get_class($element) !== $this->getEntityFqcn()) {
                throw new UnsupportedElementException(
                    sprintf(
                        "%s is not the same type as %s. Please only return elements with the specified type.",
                        get_class($element),
                        $this->getEntityFqcn()
                    )
                );
            }
        }
    }

    final public function create(): void
    {
        $elementRepository = $this->entityManager->getRepository($this->getEntityFqcn());
        $existingElements = $elementRepository->findAll();
        $wantedElements = $this->getElements();

        $removableElements = new UnknownByFilter(
          new ArrayIterator($existingElements),
            $wantedElements,
            $this->getIdentifierMethod()
        );

        $updatableElements = new KnownByNameFilter(
            new ArrayIterator($existingElements),
            $wantedElements,
            $this->getIdentifierMethod()
        );

        $newElements = new UnknownByFilter(
            new ArrayIterator($wantedElements),
            $existingElements,
            $this->getIdentifierMethod()
        );

        foreach ($removableElements as $removableElement) {
            $this->entityManager->remove($removableElement);
        }

        foreach ($updatableElements as $updatableElement) {
            foreach ($wantedElements as $wantedElement) {
                if ($updatableElement->{$this->getIdentifierMethod()}() === $wantedElement->{$this->getIdentifierMethod()}()) {
                    $this->merge($updatableElement, $wantedElement);
                    break;
                }
            }
        }

        foreach ($newElements as $newElement) {
            $this->entityManager->persist($newElement);
        }

        $this->entityManager->flush();
    }

    /** Returns the FQCN (fully-qualified class name) of a Doctrine ORM entity */
    abstract protected function getEntityFqcn(): string;
    /** Returns getter method which should be used to compare existing with new elements to check if the element should be updated or created */
    abstract protected function getIdentifierMethod(): string;

    /**
     * Merge to elements together to update existing elements instead of recreate them
     *
     * @param object $persistElem The existing element
     * @param object $newData The new element
     */
    abstract protected function merge(object $persistElem, object $newData): void;

    /** Returns an array of configured entities of the same type as the $this->getEntityFqcn() function */
    abstract protected function getElements(): array;
}