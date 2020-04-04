<?php

namespace Tests\Integration\Language\Parsing;

use PHPUnit\Framework\TestCase;
use Polygen\Document;
use Polygen\Grammar\Atom;
use Polygen\Grammar\AtomSequence;
use Polygen\Grammar\FrequencyModifier;
use Polygen\Grammar\Label;
use Polygen\Grammar\Production;
use Polygen\Grammar\Sequence;
use Polygen\Grammar\SubProduction;
use Polygen\Grammar\Unfoldable;
use Polygen\Language\Token\Token;
use Tests\DocumentUtils;
use Tests\StreamUtils;

class DocumentParserTest extends TestCase
{
    use DocumentUtils;
    use StreamUtils;

    /**
     * @test
     */
    public function it_can_parse_a_valid_file_without_blowing_up()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_file(__DIR__ . '/../../../files/incredible-commit.grm')
        );
        $document = $subject->parse();

        $this->assertInstanceOf(Document::class, $document);
    }


    /**
     * @test
     */
    public function it_parses_differently_atoms_interlevead_by_a_space_and_interleaved_by_a_comma()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_stream(
                <<< GRAMMAR
                A ::= test1, test2 and 3, test4 and 5, test6;
                B ::= test1 test2 and 3 test4 and 5 test6;
GRAMMAR
            )
        );
        $document = $subject->parse();

        $this->assertNotEquals(
            $document->getDefinition('A')->getProductions(),
            $document->getDefinition('B')->getProductions()
        );
    }

    /**
     * @test
     */
    public function it_parses_mixed_plus_minus_modifiers()
    {
        $subject = $this->given_a_parser(
            $this->given_a_source_stream(
                <<< GRAMMAR
            A ::= C.(+-label1 | -label2 | +label3);
            B ::= +-term1 | -term2 | +term3;
GRAMMAR

            )
        );

        $document = $subject->parse();

        $expectedProductionA = new Production(
            new Sequence(
                [
                    new AtomSequence(
                        [
                            Unfoldable::nonTerminating(
                               Token::nonTerminatingSymbol('C')
                            )->withLabels(
                                [
                                    new Label(Token::terminatingSymbol('label1'), new FrequencyModifier(1,1)),
                                    new Label(Token::terminatingSymbol('label2'), new FrequencyModifier(0,1)),
                                    new Label(Token::terminatingSymbol('label3'), new FrequencyModifier(1,0)),
                                ]
                            )
                        ]
                    )
                ]
            )
        );

        $this->assertEquals($expectedProductionA, $document->getDefinition('A')->getProductions()[0]);

        $expectedProductionB = [
            new Production(
                new Sequence(
                    [
                        new AtomSequence(
                            [
                                Atom::simple(Token::terminatingSymbol('term1'))
                            ]
                        ),
                    ]
                ),
                new FrequencyModifier(1, 1)
            ),
            new Production(
                new Sequence(
                    [
                        new AtomSequence(
                            [
                                Atom::simple(Token::terminatingSymbol('term2'))
                            ]
                        ),
                    ]
                ),
                new FrequencyModifier(0, 1)
            ),
            new Production(
                new Sequence(
                    [
                        new AtomSequence(
                            [
                                Atom::simple(Token::terminatingSymbol('term3'))
                            ]
                        ),
                    ]
                ),
                new FrequencyModifier(1, 0)
            ),
        ];
        $this->assertEquals($expectedProductionB, $document->getDefinition('B')->getProductions());
    }

    // TODO: for every new parsing bug, remember to add a dedicated test.
}