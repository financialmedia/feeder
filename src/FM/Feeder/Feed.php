<?php

namespace FM\Feeder;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use FM\Feeder\Event\FailedItemModificationEvent;
use FM\Feeder\Event\ItemNotModifiedEvent;
use FM\Feeder\Event\ItemModificationEvent;
use FM\Feeder\Exception\FilterException;
use FM\Feeder\Exception\ModificationException;
use FM\Feeder\Item\ModifierInterface;
use FM\Feeder\Item\Filter\FilterInterface;
use FM\Feeder\Item\Mapper\MapperInterface;
use FM\Feeder\Item\Normalizer\NormalizerInterface;
use FM\Feeder\Item\Transformer\TransformerInterface;
use FM\Feeder\Reader\ReaderInterface;

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
     * @param ReaderInterface $reader
     */
    public function __construct(ReaderInterface $reader, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->reader = $reader;
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
    }

    public function getReader()
    {
        return $this->reader;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function addNormalizer(NormalizerInterface $normalizer, $position = null)
    {
        $this->addModifier($normalizer, $position);
    }

    public function addFilter(FilterInterface $filter, $position = null)
    {
        $this->addModifier($filter, $position);
    }

    public function addMapper(MapperInterface $mapper, $position = null)
    {
        $this->addModifier($mapper, $position);
    }

    public function addTransformer(TransformerInterface $transformer, $position = null)
    {
        $this->addModifier($transformer, $position);
    }

    /**
     * @param ModifierInterface $modifier
     * @param integer           $position
     * @param boolean           $continueOnException
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

    public function getNextItem()
    {
        while ($item = $this->reader->read()) {
            try {
                $event = new ItemModificationEvent($item);

                $this->eventDispatcher->dispatch(FeedEvents::PRE_MODIFICATION, $event);
                $item = $this->modify($item);
                $this->eventDispatcher->dispatch(FeedEvents::POST_MODIFICATION, $event);

                return $item;
            } catch (FilterException $e) {
                $this->eventDispatcher->dispatch(FeedEvents::ITEM_FILTERED, new ItemNotModifiedEvent($item, $e->getMessage()));
            } catch (ModificationException $e) {
                if ($e->getPrevious()) {
                    $e = $e->getPrevious();
                }

                $this->eventDispatcher->dispatch(FeedEvents::ITEM_MODIFICATION_FAILED, new ItemNotModifiedEvent($item, $e->getMessage()));
            }
        }

        return null;
    }

    protected function modify(ParameterBag $item)
    {
        foreach ($this->modifiers as $position => $modifier) {
            try {
                if ($modifier instanceof FilterInterface) {
                    $modifier->filter($item);
                }

                if ($modifier instanceof NormalizerInterface) {
                    $modifier->normalize($item);
                }

                if ($modifier instanceof MapperInterface) {
                    $item = $modifier->map($item);
                }

                if ($modifier instanceof TransformerInterface) {
                    $modifier->transform($item);
                }
            } catch (ModificationException $e) {
                // filter exceptions don't get to continue
                if ($e instanceof FilterException) {
                    throw $e;
                }

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
