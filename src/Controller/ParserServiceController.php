<?php

namespace App\Controller;

use App\Entity\Seller;
use App\Entity\Product;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Doctrine\ORM\EntityManagerInterface;

class ParserServiceController extends AbstractController
{
    /*
    #[Route('/parser/service', name: 'app_parser_service')]
    public function index(): Response
    {
        return $this->render('parser_service/index.html.twig', [
            'controller_name' => 'ParserServiceController',
        ]);
    }*/
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
//        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    public function getGoods($url) {
        //$entityManager = $doctrine->getManager();
        $client = new Client();
        $response = $client->request('get', $url);
        while($response->getStatusCode() !== 200) {
            $response = $client->request('get', $url);
        }
        $response = $response->getBody()->getContents();
        $crawler = new Crawler($response);
        $test = $crawler->filterXPath('//*[@id="state-searchResultsV2-252189-default-1"]')->outerHtml();
        $encodeData = stristr($test, '{"items');
        $encodeData = stristr($encodeData, '\'></div>', true);
        $encodeData = json_decode($encodeData, true);
        $goodsData =[];
        foreach ($encodeData['items'] as $itemData) {
            if(isset($itemData['multiButton']['ozonSubtitle'])) {
                $goodData = [
                    'seller' => $this->getSeller(strip_tags($itemData['multiButton']['ozonSubtitle']['textAtomWithIcon']['text'])),
                    'productName' => $this->getProductName($itemData['mainState']),
                    'price' => $this->getPrise($itemData['mainState'][0]['atom']['price']['price']),
                    'countOfReviews' => $this->getCountOfReview($itemData['mainState']),
                    'sku' => $itemData['topRightButtons'][0]['favoriteProductMolecule']['sku'],
                ];
                array_push($goodsData, $goodData);
            }

        }
        $this->addProductDataToDB($goodsData);

    }
        //file_put_contents('2.txt', $html);
        //echo $html;
        //return $goodsArray;

    public function addProductDataToDB(array $productsArray) {
        foreach ($productsArray as $productData) {
            $seller = new Seller();
            $seller->setName($productData['seller']);
            $this->entityManager->persist($seller);

            // действительно выполните запросы (например, запрос INSERT)
            $this->entityManager->flush();
            $product = new Product($seller);
            $product->setName($productData['productName']);
            $product->setPrice($productData['price']);
            $product->setSku($productData['sku']);
            $product->setReviewsCount($productData['countOfReviews']);
            $product->setCreatedDateValue();
            $product->setUpdatedDateValue();
            $product->setSellerId($seller);
            $this->entityManager->persist($product);

            // действительно выполните запросы (например, запрос INSERT)
            $this->entityManager->flush();
        }
    }

    public function getSeller($string) :?string
    {
        if(mb_strpos($string, 'продавец') === false)
            return null;
        return mb_substr($string, mb_strpos($string, 'продавец') + 9);
    }

    public function getPrise($string) :?int
    {
        $matches=[];
        $string = str_replace(' ',' ',$string);
        if(preg_match_all('/[\d]*\s?[\d]{0,3}/', $string, $matches)===0)
            return null;
        $price =str_replace(' ','',$matches[0][0]);
        return intval($price);
    }

    public function getCountOfReview($dataArray) :?int
    {
        if(!isset($dataArray[3]['atom']['rating']['count']))
            return 0;
        $string=$dataArray[3]['atom']['rating']['count'];
        $matches = [];
        if(preg_match('/\d*/',$string,$matches) === 0)
            return null;
        return intval($matches[0]);
    }

    public function getProductName($dataArray) :?string
    {
        foreach ($dataArray as $atomArray) {
            if($atomArray['id'] === 'name'){
                return $atomArray['atom']['textAtom']['text'];
            }
        }
        return null;
    }

    public function getSKU($link) :?int
    {
        $matches =[];
        if(preg_match('/\d{5,}/', $link, $matches) === false)
            return null;
        return intval($matches[0]);
    }
}
