<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Modules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Modules\Ask\AskTask;
use Runph\Playbook\Modules\Ask\Exception\EmptyAnswerException;
use Runph\Playbook\Modules\Ask\Exception\InvalidAnswerException;
use stdClass;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AskTaskTest extends TestCase
{
    /** @var MockObject&QuestionHelper */
    private QuestionHelper $questionHelper;

    /** @var MockObject&InputInterface */
    private InputInterface $input;

    /** @var MockObject&OutputInterface */
    private OutputInterface $output;

    protected function setUp(): void
    {
        $this->questionHelper = $this->createMock(QuestionHelper::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testImplementsModuleInterface(): void
    {
        $task = $this->createAskTask('Test message');

        $this->assertInstanceOf(ModuleInterface::class, $task);
    }

    public function testAsksQuestionWithValidStringAnswer(): void
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('');

        $this->questionHelper
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->input,
                $this->output,
                $this->callback(function (Question $question): bool {
                    $this->assertSame("Test message\n", $question->getQuestion());
                    $this->assertSame('default value', $question->getDefault());
                    $this->assertTrue($question->isTrimmable());
                    $this->assertFalse($question->isHidden());
                    return true;
                })
            )
            ->willReturn('valid answer');

        $task = $this->createAskTask('Test message', 'default value');

        $task->run();
    }

    public function testAppendsNewlineToMessage(): void
    {
        $this->questionHelper
            ->method('ask')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (Question $question): bool {
                    $this->assertSame("Hello\n", $question->getQuestion());
                    return true;
                })
            )
            ->willReturn('answer');

        $task = $this->createAskTask('Hello');

        $task->run();
    }

    public function testSetsHiddenFlagWhenSpecified(): void
    {
        $this->questionHelper
            ->method('ask')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (Question $question): bool {
                    $this->assertTrue($question->isHidden());
                    return true;
                })
            )
            ->willReturn('secret');

        $task = $this->createAskTask('Password', '', true);

        $task->run();
    }

    public function testThrowsExceptionWhenAnswerIsNotString(): void
    {
        $this->questionHelper
            ->method('ask')
            ->willReturnCallback(function ($input, $output, Question $question): mixed {
                $validator = $question->getValidator();
                $this->assertNotNull($validator);
                return $validator(123);
            });

        $task = $this->createAskTask('Test');

        $this->expectException(InvalidAnswerException::class);

        $task->run();
    }

    public function testThrowsExceptionWhenAnswerIsEmptyString(): void
    {
        $this->questionHelper
            ->method('ask')
            ->willReturnCallback(function ($input, $output, Question $question): mixed {
                $validator = $question->getValidator();
                $this->assertNotNull($validator);
                return $validator('');
            });

        $task = $this->createAskTask('Test');

        $this->expectException(EmptyAnswerException::class);

        $task->run();
    }

    #[DataProvider('invalidAnswerTypesProvider')]
    public function testThrowsInvalidAnswerExceptionForNonStringTypes(mixed $invalidAnswer): void
    {
        $this->questionHelper
            ->method('ask')
            ->willReturnCallback(function ($input, $output, Question $question) use ($invalidAnswer): mixed {
                $validator = $question->getValidator();
                $this->assertNotNull($validator);
                return $validator($invalidAnswer);
            });

        $task = $this->createAskTask('Test');

        $this->expectException(InvalidAnswerException::class);

        $task->run();
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function invalidAnswerTypesProvider(): array
    {
        return [
            'integer' => [42],
            'float' => [3.14],
            'boolean true' => [true],
            'boolean false' => [false],
            'array' => [['value']],
            'object' => [new stdClass()],
            'null' => [null],
        ];
    }

    #[DataProvider('emptyStringVariationsProvider')]
    public function testThrowsEmptyAnswerExceptionForEmptyStrings(string $emptyValue): void
    {
        $this->questionHelper
            ->method('ask')
            ->willReturnCallback(function ($input, $output, Question $question) use ($emptyValue): mixed {
                $validator = $question->getValidator();
                $this->assertNotNull($validator);
                return $validator($emptyValue);
            });

        $task = $this->createAskTask('Test');

        $this->expectException(EmptyAnswerException::class);

        $task->run();
    }

    /**
     * @return array<string, string[]>
     */
    public static function emptyStringVariationsProvider(): array
    {
        return [
            'empty string' => [''],
            'zero string' => ['0'],
        ];
    }

    public function testConfiguresQuestionWithAllProperties(): void
    {
        $capturedQuestion = null;

        $this->questionHelper
            ->method('ask')
            ->willReturnCallback(function ($input, $output, Question $question) use (&$capturedQuestion): string {
                $capturedQuestion = $question;
                return 'answer';
            });

        $task = $this->createAskTask('Enter password', 'default_pass', true);

        $task->run();

        /** @var Question $capturedQuestion */
        $this->assertInstanceOf(Question::class, $capturedQuestion);
        $this->assertSame("Enter password\n", $capturedQuestion->getQuestion());
        $this->assertSame('default_pass', $capturedQuestion->getDefault());
        $this->assertTrue($capturedQuestion->isHidden());
        $this->assertTrue($capturedQuestion->isTrimmable());
        $this->assertNotNull($capturedQuestion->getValidator());
    }

    public function testWritesEmptyLineBeforeAskingQuestion(): void
    {
        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with('');

        $this->questionHelper
            ->method('ask')
            ->willReturn('answer');

        $task = $this->createAskTask('Test');

        $task->run();
    }

    public function testUsesEmptyStringAsDefaultWhenNotProvided(): void
    {
        $this->questionHelper
            ->method('ask')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (Question $question): bool {
                    $this->assertSame('', $question->getDefault());
                    return true;
                })
            )
            ->willReturn('answer');

        $task = $this->createAskTask('Test');

        $task->run();
    }

    public function testDoesNotHideInputByDefault(): void
    {
        $this->questionHelper
            ->method('ask')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (Question $question): bool {
                    $this->assertFalse($question->isHidden());
                    return true;
                })
            )
            ->willReturn('answer');

        $task = $this->createAskTask('Test');

        $task->run();
    }

    private function createAskTask(string $message, string $default = '', bool $hidden = false): AskTask
    {
        return new AskTask(
            $this->questionHelper,
            $this->input,
            $this->output,
            $message,
            $default,
            $hidden,
        );
    }
}
