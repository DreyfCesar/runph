<?php

declare(strict_types=1);

namespace Tests\Unit\Playbook\Modules;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Modules\Ask\AskTask;
use Runph\Playbook\Modules\Ask\Exception\EmptyAnswerException;
use Runph\Playbook\Modules\Ask\Exception\InvalidAnswerException;
use Runph\Services\Memory\Contracts\MemoryInterface;
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

    /** @var MockObject&MemoryInterface */
    private MemoryInterface $memory;

    protected function setUp(): void
    {
        $this->questionHelper = $this->createMock(QuestionHelper::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->memory = $this->createMock(MemoryInterface::class);
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

    public function testSavesAnswerInMemory(): void
    {
        $answer = 'something';
        $saveKey = 'some_key';

        $this->questionHelper
            ->method('ask')
            ->willReturn($answer);

        $this->memory
            ->expects($this->once())
            ->method('set')
            ->with($saveKey, $answer);

        $task = $this->createAskTask('Test', save: $saveKey);

        $task->run();
    }

    public function testIgnoresSaveIfKeyIsEmpty(): void
    {
        $this->memory
            ->expects($this->never())
            ->method('set');

        $task = $this->createAskTask('Test', save: '');

        $task->run();
    }

    public function testSetsAutocompleterValuesWhenProvided(): void
    {
        $autocomplete = ['option1', 'option2', 'option3'];

        $this->questionHelper
            ->method('ask')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (Question $question) use ($autocomplete): bool {
                    $this->assertSame($autocomplete, $question->getAutocompleterValues());
                    return true;
                })
            )
            ->willReturn('option1');

        $task = $this->createAskTask('Select option', autocomplete: $autocomplete);

        $task->run();
    }

    public function testDoesNotSetAutocompleterWhenNull(): void
    {
        $this->questionHelper
            ->method('ask')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (Question $question): bool {
                    $this->assertNull($question->getAutocompleterValues());
                    return true;
                })
            )
            ->willReturn('answer');

        $task = $this->createAskTask('Test', autocomplete: null);

        $task->run();
    }

    public function testDoesNotSetAutocompleterWhenEmptyArray(): void
    {
        $this->questionHelper
            ->method('ask')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function (Question $question): bool {
                    $this->assertNull($question->getAutocompleterValues());
                    return true;
                })
            )
            ->willReturn('answer');

        $task = $this->createAskTask('Test', autocomplete: []);

        $task->run();
    }

    public function testThrowsExceptionWhenAutocompleteContainsNonString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Autocomplete values must be string or Stringable, got integer');

        $this->createAskTask('Test', autocomplete: ['valid', 123]);
    }

    /**
     * @param mixed[] $invalidAutocomplete
     */
    #[DataProvider('invalidAutocompleteTypesProvider')]
    public function testThrowsExceptionForInvalidAutocompleteTypes(array $invalidAutocomplete, string $expectedType): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Autocomplete values must be string or Stringable, got ' . $expectedType);

        $this->createAskTask('Test', autocomplete: $invalidAutocomplete);
    }

    /**
     * @return array<string, array{invalidAutocomplete: array<mixed>, expectedType: string}>
     */
    public static function invalidAutocompleteTypesProvider(): array
    {
        return [
            'integer in array' => [
                'invalidAutocomplete' => ['valid', 42],
                'expectedType' => 'integer',
            ],
            'float in array' => [
                'invalidAutocomplete' => ['valid', 3.14],
                'expectedType' => 'double',
            ],
            'boolean in array' => [
                'invalidAutocomplete' => ['valid', true],
                'expectedType' => 'boolean',
            ],
            'array in array' => [
                'invalidAutocomplete' => ['valid', ['nested']],
                'expectedType' => 'array',
            ],
            'object in array' => [
                'invalidAutocomplete' => ['valid', new stdClass()],
                'expectedType' => 'object',
            ],
            'null in array' => [
                'invalidAutocomplete' => ['valid', null],
                'expectedType' => 'NULL',
            ],
        ];
    }

    public function testValidatesAutocompleteOnConstruction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createAskTask('Test', autocomplete: [1, 2, 3]);
    }

    /**
     * @param mixed[] $autocomplete
     */
    private function createAskTask(string $message, string $default = '', bool $hidden = false, string $save = '', ?array $autocomplete = null): AskTask
    {
        return new AskTask(
            $this->questionHelper,
            $this->input,
            $this->output,
            $this->memory,
            $message,
            $save,
            $default,
            $hidden,
            $autocomplete,
        );
    }
}
