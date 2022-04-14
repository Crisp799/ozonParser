<?php

namespace App\Controller;

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

    #[Route('/parser', name: 'admin')]
    public function searchByURL(Request $request) : Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(ProductCrudController::class)->generateUrl();

        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);

        $goodsArray=[];
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            //$this->getGoods($formData['query']);
            $controller = new ParserServiceController($this->entityManager);
            $controller->getGoods($formData['query']);

            //cho $formData['query'];
        }
        //return $this->redirect($url);
        return $this->render('bundles/EasyAdminBundle/page/content.html.twig', ['form' => $form->createView(), 'goods' => $goodsArray]);
    }
/*
    public function  getHTML($url = '') {
        $client = new Client();
        $res = $client->request('GET', $url);

        echo $res->getStatusCode();
        if ($res->getStatusCode() == 200) {
            //$this->getHTML($res->getBody());
            $htmlData = $res->getBody();
            //echo $res->getBody();
            $this->getGoods($htmlData);
        }
        //echo $res->getHeader('content-type')[0];
// 'application/json; charset=utf8'
        echo $res->getBody();
    }

    public function getGoods($url) {
        $client = new Client();

        $crawler = new Crawler();
        $crawler -> addHtmlContent(file_get_contents($url));
        //echo $crawler->html();
        $goodsArray =[];
        //echo count($crawler);
        $html = '';
        //$product = $crawler->filter('.n4i');
        $converter = new CssSelectorConverter();
        //$product = $crawler->filterXPath($converter->toXPath('html > body > div#__ozon > div.a0 > div.g2s > div.g3s > div.gt0 > div.s6g >div.i6p >div >div.pi9 >div.p9i >div.n4i'));
        //$product = $crawler->filter('.widget-search-result-container pi9 > div >div');
        //$links = $crawler ->filter('.n4i > a');
        //echo $product->count();
        var_dump($product);
        //echo $product->html();
        $product = $crawler->filter('.n4i');
        //echo $product->html();
        //забираем весь блок с кроссовками
        foreach ($product as $domElement) {
            array_push($goodsArray, $domElement->textContent);
            //var_dump($domElement);
            /*foreach($domElement->childNodes as $node) {
                $html .= $domElement->ownerDocument->saveHTML($node);
                $html .='\n';
            }
        }
        //file_put_contents('2.txt', $html);
        //echo $html;
        return $goodsArray;
    }*/

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
        yield MenuItem::linkToCrud('Product', 'fas fa-comments', Product::class);
    }
}
