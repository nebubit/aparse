<?php

namespace AParse\Commands;

use AParse\Exceptions\InvalidArgumentException;
use Psy\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Using a file.
 *
 */
class UseFileCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('use')
            ->setAliases(array('use-file', 'u'))
            ->setDefinition(array(
                new InputArgument('file_name', InputArgument::OPTIONAL, 'The file name', null),
            ))
            ->setDescription('Using a file for query.')
            ->setHelp(
                <<<'HELP'
Using a file for query.

e.g.
<return>>>> use access.log</return>
HELP
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('file_name');
        if (empty($fileName)) {
            throw new InvalidArgumentException('Please input the file name.');
        }

        return \AParse\useFile($fileName);
    }
}
