<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Ask;

use InvalidArgumentException;
use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Modules\Ask\Exception\EmptyAnswerException;
use Runph\Playbook\Modules\Ask\Exception\InvalidAnswerException;
use Runph\Services\Memory\Contracts\MemoryInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AskTask implements ModuleInterface
{
    /**
     * @param mixed[] $autocomplete
     */
    public function __construct(
        private QuestionHelper $questionHelper,
        private InputInterface $input,
        private OutputInterface $output,
        private MemoryInterface $memory,
        private string $message,
        private string $save,
        private string $default = '',
        private bool $hidden = false,
        private ?array $autocomplete = null,
    ) {
        $this->validateAutocomplete();
    }

    public function run(): void
    {
        $question = new Question("{$this->message}\n", $this->default);

        $question->setTrimmable(true);
        $question->setHidden($this->hidden);

        if ($this->autocomplete) {
            $question->setAutocompleterValues($this->autocomplete);
        }

        $question->setValidator(function ($answer) {
            if (! is_string($answer)) {
                throw new InvalidAnswerException($answer);
            }

            if (empty($answer)) {
                throw new EmptyAnswerException();
            }

            return $answer;
        });

        $this->output->writeln('');
        $answer = $this->questionHelper->ask($this->input, $this->output, $question);

        if (! empty($this->save)) {
            $this->memory->set($this->save, $answer);
        }
    }

    private function validateAutocomplete(): void
    {
        if ($this->autocomplete) {
            foreach ($this->autocomplete as $value) {
                if (! is_string($value)) {
                    throw new InvalidArgumentException('Autocomplete values must be string or Stringable, got ' . gettype($value));
                }
            }
        }
    }
}
