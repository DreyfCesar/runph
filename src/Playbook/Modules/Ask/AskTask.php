<?php

declare(strict_types=1);

namespace Runph\Playbook\Modules\Ask;

use Runph\Playbook\Contracts\ModuleInterface;
use Runph\Playbook\Modules\Ask\Exception\EmptyAnswerException;
use Runph\Playbook\Modules\Ask\Exception\InvalidAnswerException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AskTask implements ModuleInterface
{
    public function __construct(
        private QuestionHelper $questionHelper,
        private InputInterface $input,
        private OutputInterface $output,
        private string $message,
        private string $default = '',
        private bool $hidden = false,
    ) {}

    public function run(): void
    {
        $question = new Question("{$this->message}\n", $this->default);

        $question->setTrimmable(true);
        $question->setHidden($this->hidden);

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
        $this->questionHelper->ask($this->input, $this->output, $question);
    }
}
