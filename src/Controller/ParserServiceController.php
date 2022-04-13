<?php

namespace App\Controller;


use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;

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
    public function getGoods($url) {
        $client = new Client();

        $crawler = new Crawler();
        $crawler -> addHtmlContent(file_get_contents($url));
        //echo $crawler->html();
        $goodsArray =[];
        $converter = new CssSelectorConverter();
        $product = $crawler->filter('.n4i');
        $links = $crawler->filter('.im7')->extract(['attr' => 'href']);
        $prices = $crawler->filter('.ui-t');
        foreach ($product as $domElement) {
            $this->getCountOfReview($domElement->textContent).' ';
            //echo $domElement->textContent.'____';
            //array_push($goodsArray, $domElement->textContent);
            //var_dump($domElement);
            //var_dump($domElement);
            /*foreach($domElement->childNodes as $node) {
                $html .= $domElement->ownerDocument->saveHTML($node);
                $html .='\n';
            }*/
        }
        //file_put_contents('2.txt', $html);
        //echo $html;
        return $goodsArray;
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
        $string = str_replace(' ','  ',$string);
        if(preg_match_all('/[\d]*\s\s[\d]{3}\s\s/', $string, $matches)===0)
            return null;
        $price =str_replace(' ','',$matches[0][0]);
        return intval($price);
    }

    public function getCountOfReview($string) :int
    {
        $finishIndex = mb_strpos($string, 'отз')-2;
        echo $string=mb_substr($string,0,$finishIndex+1);
        $matches = [];
        preg_match('/\d+$/',$string,$matches);
        var_dump($matches);
        return intval($matches[0]);
    }

    public function getProductName() {

    }
}
