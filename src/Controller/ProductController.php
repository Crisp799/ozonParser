<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ProductController extends AbstractDashboardController
{
    private $entityManager;

    public function __construct( EntityManagerInterface $entityManager) {
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
        var_dump($productData = $productRepository->findOneBy(['id'=>$id]));
        $sellerRepository = $this->entityManager->getRepository(Seller::class);
        $seller = $sellerRepository->findOneBy(['id'=>$productData['seller_id']]);
        $param =[
            'id' => $productData['id'],
            'name' => $productData['name'],
            'price' => $productData['price'],
            'seller' => $sellerRepository->findOneBy(['id'=>$productData['seller_id']])['name'],
            'countOfReviews' => $productData['reviews_count'],
            'createDate' => $productData['created_date'],
        ];
        return $this->render('bundles/EasyAdminBundle/page/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('URL Parser');
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
        //yield MenuItem::section('PARSER','fas fa-comments' );
        yield MenuItem::linkToUrl('Back to parser', 'fas fa-home', 'parser');
        yield MenuItem::linkToUrl('Product', 'fas fa-comments', 'products');
    }
}