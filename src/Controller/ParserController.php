<?php

namespace App\Controller;

use App\Controller\Validator;
use App\Entity\Product;
use App\Form\SearchFormType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use GuzzleHttp\Handler;
use GuzzleHttp\Client;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;

class ParserController extends AbstractDashboardController
{
    //private $twig;
    private $entityManager;

    public function __construct( EntityManagerInterface $entityManager) {
//        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->redirect('/parser');
        //return $this->render('bundles/EasyAdminBundle/layout.html.twig', ['form' => $form->createView()]);

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        //return $this->render('Parser/parserUrl.html.twig');
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
            //$this->getGoods($formData['query']);
            $validator = new Validator();
            $errors = $validator->ValidateUrl($formData['query']);
            if (count($errors) != 0)
                return $this->render('bundles/EasyAdminBundle/page/content.html.twig', ['form' => $form->createView(), 'responseInfo' => $responseInfo, 'errors' => $errors]);
            $parserService = new ParserServiceController($this->entityManager);
            $responseInfo = $parserService->collect($formData['query']);

            //cho $formData['query'];
        }
        $errors = null;
        //return $this->redirect($url);
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
        //yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
        //yield MenuItem::section('PARSER','fas fa-comments' );
        yield MenuItem::linkToRoute('Parser', 'fas fa-comments', 'parser');
        yield MenuItem::linkToRoute('Products', 'fas fa-home', 'products');
    }
}
