<?php

namespace FM\Feeder;

use FM\Feeder\Event\FailedItemModificationEvent;
use FM\Feeder\Event\InvalidItemEvent;
use FM\Feeder\Event\ItemModificationEvent;
use FM\Feeder\Event\ItemNotModifiedEvent;
use FM\Feeder\Exception\FilterException;
use FM\Feeder\Exception\ModificationException;
use FM\Feeder\Exception\ValidationException;
use FM\Feeder\Modifier\Item\Filter\FilterInterface;
use FM\Feeder\Modifier\Item\Mapper\MapperInterface;
use FM\Feeder\Modifier\Item\ModifierInterface;
use FM\Feeder\Modifier\Item\Transformer\TransformerInterface;
use FM\Feeder\Modifier\Item\Validator\ValidatorInterface;
use FM\Feeder\Reader\ReaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class Feed
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ModifierInterface[]
     */
    protected $modifiers = [];

    /**
     * @var array
     */
    protected $continues = [];

    /**
     * @param ReaderInterface          $reader
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ReaderInterface $reader, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->reader = $reader;
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
    }

    /**
     * @return ReaderInterface
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return ModifierInterface[]
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @param FilterInterface $filter
     * @param integer|null    $position
     */
    public function addFilter(FilterInterface $filter, $position = null)
    {
        $this->addModifier($filter, $position);
    }

    /**
     * @param MapperInterface $mapper
     * @param integer|null    $position
     */
    public function addMapper(MapperInterface $mapper, $position = null)
    {
        $this->addModifier($mapper, $position);
    }

    /**
     * @param TransformerInterface $transformer
     * @param integer|null         $position
     */
    public function addTransformer(TransformerInterface $transformer, $position = null)
    {
        $this->addModifier($transformer, $position);
    }

    /**
     * @param ValidatorInterface $validator
     * @param integer|null       $position
     */
    public function addValidator(ValidatorInterface $validator, $position = null)
    {
        $this->addModifier($validator, $position);
    }

    /**
     * @param ModifierInterface $modifier
     * @param integer           $position
     * @param boolean           $continueOnException
     *
     * @throws \InvalidArgumentException
     */
    public function addModifier(ModifierInterface $modifier, $position = null, $continueOnException = false)
    {
        if (null === $position) {
            $position = sizeof($this->modifiers) ? (max(array_keys($this->modifiers)) + 1) : 0;
        }

        if (!is_numeric($position)) {
            throw new \InvalidArgumentException('Position must be a number');
        }

        if (array_key_exists($position, $this->modifiers)) {
            throw new \InvalidArgumentException(sprintf('There already is a modifier at position %d', $position));
        }

        $this->modifiers[$position] = $modifier;
        $this->continues[$position] = $continueOnException;

        ksort($this->modifiers);
    }

    /**
     * @return ParameterBag|null
     */
    public function getNextItem()
    {
        while ($item = $this->reader->read()) {
            try {
                $this->eventDispatcher->dispatch(FeedEvents::PRE_MODIFICATION, new ItemModificationEvent($item));
                $item = $this->modify($item);
                $this->eventDispatcher->dispatch(FeedEvents::POST_MODIFICATION, new ItemModificationEvent($item));

                return $item;
            } catch (FilterException $e) {
                $this->eventDispatcher->dispatch(
                    FeedEvents::ITEM_FILTERED,
                    new ItemNotModifiedEvent($item, $e->getMessage())
                );
            } catch (ValidationException $e) {
                $this->eventDispatcher->dispatch(
                    FeedEvents::ITEM_INVALID,
                    new InvalidItemEvent($item, $e->getMessage())
                );
            } catch (ModificationException $e) {
                if ($e->getPrevious()) {
                    $e = $e->getPrevious();
                }

                $this->eventDispatcher->dispatch(
                    FeedEvents::ITEM_MODIFICATION_FAILED,
                    new ItemNotModifiedEvent($item, $e->getMessage())
                );
            }
        }

        return null;
    }

    /**
     * @param ParameterBag $item
     *
     * @return ParameterBag
     *
     * @throws FilterException
     * @throws ModificationException
     * @throws ValidationException
     */
    protected function modify(ParameterBag &$item)
    {
        foreach ($this->modifiers as $position => $modifier) {
            try {
                if ($modifier instanceof FilterInterface) {
                    $modifier->filter($item);
                }

                if ($modifier instanceof MapperInterface) {
                    $item = $modifier->map($item);
                }

                if ($modifier instanceof TransformerInterface) {
                    $modifier->transform($item);
                }

                if ($modifier instanceof ValidatorInterface) {
                    $modifier->validate($item);
                }
            } catch (FilterException $e) {
                // filter exceptions don't get to continue
                throw $e;
            } catch (ValidationException $e) {
                // validation exceptions don't get to continue
                throw $e;
            } catch (ModificationException $e) {
                // notify listeners of this failure, give them the option to stop propagation
                $event = new FailedItemModificationEvent($item, $modifier, $e);
                $event->setContinue($this->continues[$position]);

                $this->eventDispatcher->dispatch(FeedEvents::ITEM_MODIFICATION_FAIL, $event);

                if (!$event->getContinue()) {
                    throw $e;
                }
            }
        }

        return $item;
    }
}
