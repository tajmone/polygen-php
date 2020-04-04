<?php

namespace Polygen\Grammar;

use Polygen\Grammar\Interfaces\FrequencyModifiable;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Webmozart\Assert\Assert;

/**
 * Represents a Polygen production.
 */
class Production implements Node, FrequencyModifiable
{
    /**
     * @var FrequencyModifier
     */
    private $frequencyModifier;

    /**
     * @var Sequence
     */
    private $sequence;

    public function __construct(Sequence $sequence, FrequencyModifier $frequencyModifier = null)
    {
        $this->frequencyModifier = $frequencyModifier ?: new FrequencyModifier(0, 0);
        $this->sequence = $sequence;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @return mixed
     */
    public function traverse(AbstractSyntaxWalker $walker)
    {
        return $walker->walkProduction($this);
    }

    /**
     * @return Sequence
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @return FrequencyModifier
     */
    public function getFrequencyModifier()
    {
        Assert::notNull($this->frequencyModifier);
        return $this->frequencyModifier;
    }
}