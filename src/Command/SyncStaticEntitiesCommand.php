<?php

namespace Simoeboe\StaticEntitiesBundle\Command;

use Simoeboe\StaticEntitiesBundle\StaticEntityCreatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'static-entities:sync',
    description: 'Sync static entities',
)]
class SyncStaticEntitiesCommand extends Command
{

    public function __construct(
        /** @var StaticEntityCreatorInterface[] $staticEntityCreators */
        private readonly iterable $staticEntityCreators
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->staticEntityCreators as $staticEntityCreator) {
            $staticEntityCreator->create();
            if ($output->isVerbose()) {
                $io->info(sprintf("%s executed", get_class($staticEntityCreator)));
            }
        }

        $io->success(sprintf("%s static entity creator(s) executed", count($this->staticEntityCreators)));

        return 0;
    }
}
