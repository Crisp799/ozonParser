<?php

namespace App\Controller;


use App\Entity\Seller;
use App\Form\SearchFormType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParserController extends AbstractDashboardController
{
    private $entityManager;

    public function __construct( EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->redirect('/parser');
    }
    #[Route('/admin', name: 'admin')]
    public function showAdminPage(): Response
    {
        return $this->redirect('/parser');
    }

    #[Route('/parser', name: 'parser')]
    public function searchByURL(Request $request) : Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(ProductCrudController::class)->generateUrl();

        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);

        $responseInfo = [
            'collectDataCount' => 0,
            'addDataCount' => 0,
            ];

        if ($form->isSubmitted()) {
            $formData = $form->getData();
            $validator = new Validator();
            $errors = $validator->ValidateUrl($formData['query']);
            if (count($errors) > 0)
                return $this->render('bundles/EasyAdminBundle/page/content.html.twig', ['form' => $form->createView(), 'responseInfo' => $responseInfo, 'errors' => $errors]);
            $parserService = new ParserServiceController($this->entityManager);
            $responseInfo = $parserService->collect($formData['query']);

        }
        $errors = null;
        return $this->render('bundles/EasyAdminBundle/page/content.html.twig', ['form' => $form->createView(), 'responseInfo' => $responseInfo, 'errors' => $errors]);
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('URL Parser');
    }

    public function form() {
        $form = $this->createFormBuilder()
            ->add('url', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task']);
        return $form;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Parser', 'fas fa-comments', 'parser');
        yield MenuItem::linkToRoute('Products', 'fas fa-home', 'products');
        yield MenuItem::linkToCrud('Sellers', 'fas fa-home', Seller::class);
    }
}
