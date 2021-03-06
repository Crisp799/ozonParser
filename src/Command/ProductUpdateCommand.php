<?php

namespace App\Command;


use App\Controller\ParserServiceController;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->addArgument('sellerID', InputArgument::OPTIONAL, 'The Seller ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('sellerID');
        $service = new ParserServiceController($this->entityManager);
        $repository = $this->entityManager->getRepository(Product::class);
        if (empty($id)) {
            $dbData = $repository->findAll();//получил данные из базы данных
        } else if ($service->isSellerExistInTable($id) === true) {
            $dbData = $repository->findBy(['seller' => $id]);
        } else {
            $output->writeln('Продавец с указанным ID не найден');
            return Command::FAILURE;
        }

        $count = 0;

        foreach ($dbData as $productData) {

            $service->updateProduct($productData);
            ++$count;
        }
        $output->writeln("$count updated");
        return Command::SUCCESS;
    }
}
