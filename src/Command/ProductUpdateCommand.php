<?php

namespace App\Command;

use App\Entity\Product;
use App\Controller\ParserServiceController;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'ProductUpdateCommand',
    description: 'Add a short description for your command',
)]
class ProductUpdateCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        $this->entityManager = $entityManager;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Command for update')
            //->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            //->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$io = new SymfonyStyle($input, $output);
        $repository = $this->entityManager->getRepository(Product::class);
        $dbData = $repository->findAll();//получил данные из базы данных

        foreach ($dbData as $productData) {
            $service = new ParserServiceController($this->entityManager);
            $service -> updateProduct($productData);
        }
        return Command::SUCCESS;
    }
}
