<?php

namespace Polygen\Grammar\Unfoldable;

use Polygen\Grammar\FoldingModifier;
use Polygen\Grammar\Interfaces\Node;
use Polygen\Language\AbstractSyntaxWalker;
use Polygen\Language\Token\Token;
use Polygen\Language\Token\Type;
use Webmozart\Assert\Assert;

/**
 * Non terminating symbol unfoldable.
 * This has been separated since it's the only one for which a "getType" method did not make sense and for which a
 * "getSubproduction" method also did not made sense. Although this last claim might be disproved when we get to the
 * actual production phase, since we still have to go through validation, I will reconsider it if necessary,
 */
class NonTerminatingSymbol extends Unfoldable implements Node
{
    /**
     * @var Token
     */
    private $nonTerminatingSymbol;

    public function __construct(
        Token $nonTerminatingSymbol,
        FoldingModifier $foldingModifier = null
    ) {
        Assert::eq($nonTerminatingSymbol->getType(), Type::nonTerminatingSymbol());
        $this->nonTerminatingSymbol = $nonTerminatingSymbol;
        parent::__construct($foldingModifier);
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->nonTerminatingSymbol;
    }

    /**
     * Allows a node to pass itself back to the walker using the method most appropriate to walk on it.
     *
     * @param mixed|null $context Data that you want to be passed back to the walker.
     * @return mixed|null
     */
    public function traverse(AbstractSyntaxWalker $walker, $context = null)
    {
        return $walker->walkNonTerminating($this, $context);
    }

    public function __sleep()
    {
        $this->nonTerminatingSymbol = $this->nonTerminatingSymbol->toSerializableArray();
        return ['nonTerminatingSymbol'];
    }

    public function __wakeup()
    {
        /** @noinspection PhpParamsInspection */
        $this->nonTerminatingSymbol = Token::fromSerializableArray($this->nonTerminatingSymbol);
    }
}
