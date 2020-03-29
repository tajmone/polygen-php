<?php

namespace Tests\Polygen\Integration\Stream;

use Mockery;
use Polygen\Language\Lexing\Lexer;
use Polygen\Language\Token\Token;
use Polygen\Stream\CachingStream;
use Polygen\Stream\SavePointStream;
use Polygen\Stream\TokenStream;

class SavePointStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_forwards_advance_calls_to_the_decorated_stream()
    {
        $cachingStream = Mockery::mock(CachingStream::class);
        $subject = new SavePointStream($cachingStream);

        $cachingStream->shouldReceive('advance')
            ->once();

        $subject->advance();
    }

    /**
     * @test
     */
    public function it_forwards_nextToken_calls_to_the_decorated_stream()
    {
        $cachingStream = Mockery::mock(CachingStream::class);
        $subject = new SavePointStream($cachingStream);

        $cachingStream->shouldReceive('nextToken')
            ->once()
            ->andReturn($expectedToken = Token::semicolon());

        $result = $subject->nextToken();

        $this->assertSame($expectedToken, $result);
    }

    /**
     * @test
     */
    public function it_forwards_isEOF_calls_to_the_decorated_stream()
    {
        $cachingStream = Mockery::mock(CachingStream::class);
        $subject = new SavePointStream($cachingStream);

        $cachingStream->shouldReceive('isEOF')
            ->once()
            ->andReturn(true);

        $result = $subject->isEOF();

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_allows_rewinding_to_the_beginning_of_the_stream()
    {
        $tokens = [
            Token::leftBracket(),
            Token::pipe(),
            Token::rightBracket(),
            Token::endOfFile(),
        ];

        $subject = $this->given_a_caching_stream($tokens);

        $subject->createSavePoint();
        $this->assertFalse($subject->isEof());
        $subject->advance();
        $this->assertFalse($subject->isEof());
        $subject->advance();
        $this->assertFalse($subject->isEof());
        $subject->advance();
        $this->assertTrue($subject->isEof());
        $subject->rollback();
        $this->assertFalse($subject->isEof());
        $this->assertEquals(Token::leftBracket(), $subject->nextToken());
        $subject->advance();
        $this->assertEquals(Token::pipe(), $subject->nextToken());
        $subject->advance();
        $this->assertEquals(Token::rightBracket(), $subject->nextToken());
        $subject->advance();
        $this->assertTrue($subject->isEof());
    }

    /**
     * @test
     */
    public function it_does_not_allow_rolling_back_a_savepoint_if_none_was_created()
    {
        $tokens = [
            Token::leftBracket(),
            Token::pipe(),
            Token::rightBracket(),
            Token::endOfFile(),
        ];
        $subject = $this->given_a_caching_stream($tokens);

        $this->expectExceptionMessage('Cannot rollback stream: no savepoints defined.');

        $subject->rollback();
    }

    /**
     * @test
     */
    public function it_does_not_allow_rolling_back_a_savepoint_if_they_were_already_rolled_back()
    {
         $tokens = [
            Token::leftBracket(),
            Token::pipe(),
            Token::rightBracket(),
            Token::endOfFile(),
        ];
        $subject = $this->given_a_caching_stream($tokens);


        $subject->advance();
        $subject->advance();
        $subject->createSavePoint();
        $subject->rollback();
        $this->expectExceptionMessage('Cannot rollback stream: no savepoints defined.');
        $subject->rollback();
    }

    /**
     * @return \Mockery\MockInterface|\Polygen\Stream\SavePointStream
     */
    private function given_a_caching_stream(array $tokens)
    {
        return new SavePointStream(
            new CachingStream(
                new TokenStream(
                    Mockery::mock(Lexer::class, ['getTokens' => new \ArrayIterator($tokens)])
                )
            )
        );
    }
}
