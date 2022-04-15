<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Seller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ProductController extends AbstractDashboardController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/products', name: 'products')]
    public function products(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(ProductCrudController::class)->generateUrl();

        return $this->redirect($url);
    }

    #[Route('/product/{id}', name: 'product')]
    public function product(int $id = 0): Response
    {
        $productRepository = $this->entityManager->getRepository(Product::class);
        $productData = $productRepository->findOneBy(['id' => $id]);
        $sellerRepository = $this->entityManager->getRepository(Seller::class);
        $seller = $sellerRepository->findOneBy(['id' => $productData->getSellerId()]);
        $param = [
            'id' => $productData->getId(),
            'name' => $productData->getName(),
            'price' => $productData->getPrice(),
            'seller' => $sellerRepository->findOneBy(['id' => $productData->getSellerId()])->getName(),
            'countOfReviews' => $productData->getReviewsCount(),
            'createDate' => $productData->getCreatedDate()->format('Y-m-d H:i:s'),
            'updated_date' => $productData->getUpdatedDate()->format('Y-m-d H:i:s'),
            'ozonLink' => $productData->getOzonLink(),
            'sku' => $productData->getSku(),
        ];
        return $this->render('bundles/EasyAdminBundle/page/index.html.twig', $param);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('URL Parser');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Back to parser', 'fas fa-home', 'parser');
        yield MenuItem::linkToRoute('Product', 'fas fa-comments', 'products');
        yield MenuItem::linkToCrud('Sellers', 'fas fa-home', Seller::class);

    }
}
