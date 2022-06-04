<?php

use Doctrine\DBAL\Driver\PDOStatement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\DBAL\Connection;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Article;
use Shopware\Models\Config\Form;

class Shopware_Controllers_Backend_PriceCopyModulePlainHtml extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * @var OrderRepo
     */
    protected $orderRepository = null;

    protected $logger = null;
    const LOG_LEVEL = 200;

    public function preDispatch()
    {
        $this->get('template')->addTemplateDir(__DIR__ . '/../../Resources/views/');
    }

//    public function postDispatch()
//    {
//        $csrfToken = $this->container->get('BackendSession')->offsetGet('X-CSRF-Token');
//        $this->View()->assign([ 'csrfToken' => $csrfToken ]);
//    }

    private function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = $this->container->get('price_copy.logger');
        }

        return $this->logger;
    }

    public function indexAction()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['groups'])->from(\Shopware\Models\Customer\Group::class, 'groups');
        $groups = $builder->getQuery()->getArrayResult();
        $this->View()->assign(['groups' => $groups]);
    }

    public function copyPricesAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $fromGroup = $this->Request()->getParam("fromGroup");
        $toGroups = $this->Request()->getParam("toGroups");
        $code = 400;
        $message = "Error. Invalid data!";
        
        if($fromGroup && $toGroups && !in_array($fromGroup, $toGroups)){
            $sqlSelect = "SELECT
                    * 
                FROM
                    s_articles_prices 
                WHERE
                    pricegroup = '$fromGroup'
                ;";

            $selectStatement = Shopware()->Db()->prepare(
                $sqlSelect
            );
            $selectStatement->execute();
            $prices = $selectStatement->fetchAll();
            $selectStatement->closeCursor();

            if($prices){
                $toGroupsSqlIn = implode("','",$toGroups);
                $sqlDelete = "DELETE FROM s_articles_prices WHERE pricegroup IN ('".$toGroupsSqlIn."'); ";

                $pricesDeleteStatement = Shopware()->Db()->prepare(
                    $sqlDelete
                );
                $pricesDelete = $pricesDeleteStatement->execute();
                $pricesDeleteStatement->closeCursor();

                $sqlInsert = [];
                foreach ($prices as $k => $price){
                    foreach ($toGroups as $group){
                        $sqlInsert[] = '("'.$group.'", '.(int)$price['from'].', "'.$price['to'].'", '.(int)$price['articleID'].', '.(int)$price['articledetailsID'].', '.(float)$price['price'].', '.(float)$price['pseudoprice'].', '.(float)$price['baseprice'].', '.(float)$price['percent'].')';
                    }
                }

                $queryInsert = " INSERT INTO s_articles_prices 
                    (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`) 
                VALUES ".implode(',', $sqlInsert). "; ";

                $pricesInsertStatement = Shopware()->Db()->prepare(
                    $queryInsert
                );
                $pricesInsert = $pricesInsertStatement->execute();
                $pricesInsertStatement->closeCursor();

                $code = 200;
                $message = ["pricesDelete" => $pricesDelete, "pricesInsert" => $pricesInsert];
            }
        }

        echo json_encode(["result" => $code, "message" => $message]);
        die();
    }

    public function roundPricesAction()
    {
        $customerTaxCollection = $this->getConfig();


//// Array with \Shopware\Models\Article\Article objects
//        $objectData = $builder->getQuery()->getResult();
//
//// Array with arrays
//        $arrayData = $builder->getQuery()->getArrayResult();
//echo __FILE__ . ' on line ' . __LINE__ . PHP_EOL . '<pre>'.print_r($arrayData,true).'</pre>';
//exit(PHP_EOL . __FILE__ . ' on line ' . __LINE__ . PHP_EOL);

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        /** @var  Detail $articles */
        $articles = $this->getModelManager()->getRepository(Detail::class)->findBy(['unit' => [6,8,9,10,11,12,13,14,15,16]]);


        foreach ($articles as $articleDetail) {
            foreach ($articleDetail->getPrices()->toArray() as $priceGroup) {
                if ($articleDetail->getUnit()->getId()){
                    $articleID = $articleDetail->getArticleId();
                    $purchaseUnit = $articleDetail->getPurchaseUnit();
                    $customerGroup = $priceGroup->getCustomerGroup()->getKey();
                    $nettoPrice = $priceGroup->getPrice();
                    $taxRateUp = $customerTaxCollection[$customerGroup];
                    $taxRateDown =  ($taxRateUp/10)*0.1+1;
                    $unitId = $articleDetail->getUnit()->getId();
                    $nettoPriceRounded =0;


                    if ($unitId == 10) {
                        echo('articleID - '.$articleID.
                            '<br/> purchase unit - '.$purchaseUnit.
                            '<br/> customerGroup - '.$customerGroup.
                            '<br/> nettoPrice - '.$nettoPrice.
                            '<br/> taxRateUp - '.$taxRateUp.
                            '<br/> taxRateDown - '.$taxRateDown
                            );
                        $pricePerSquareNetto = $nettoPrice / $purchaseUnit;
                        echo(
                            '<br/> price per square netto - '.$pricePerSquareNetto
                        );
                        $pricePerSquareGross = $pricePerSquareNetto*($taxRateUp/100) + $pricePerSquareNetto;
                        echo(
                            '<br/> price per square gross before ceil()- '.$pricePerSquareGross
                        );
                        if (ctype_digit(strval($pricePerSquareGross)) ) {
                            $pricePerSquareGross += 0.01;
                            echo(
                                '<br/> price per square gross (round number) - '.$pricePerSquareGross
                            );
                        }

                        $pricePerSquareGross = ceil($pricePerSquareGross)-0.10;
                        echo(
                            '<br/> price per square gross after ceil()- '.$pricePerSquareGross
                        );
                        $nettoPerSquareRounded = $pricePerSquareGross/$taxRateDown;
                        echo(
                            '<br/> price per square netto rounded '.$nettoPerSquareRounded
                        );
                        $nettoPriceRounded = $nettoPerSquareRounded*$purchaseUnit;
                        echo(
                            '<br/> price netto rounded  '.$nettoPriceRounded . '<br/> ------------- <br/>'
                        );

                        $connection = $this->container->get('dbal_connection');
                        $sql = 'UPDATE s_articles_prices SET price = :nettoPrice WHERE articleID = :articleId AND pricegroup = :priceGroup';
                        $data = $connection->fetchAll($sql,[':nettoPrice' => $nettoPriceRounded,':priceGroup'=>$customerGroup,':articleId'=>$articleID]);


                    }
                    else  {
                        echo(
                            '<br/> netto price  '.$nettoPrice . '<br/> ------------- <br/>'
                        );
                        $grossPrice = $nettoPrice*($taxRateUp/100) + $nettoPrice;
                        echo(
                            '<br/> gross price  '.$grossPrice . '<br/> ------------- <br/>'
                        );
                        if (ctype_digit(strval($grossPrice)) ) {
                            $grossPrice += 0.01;
                        }

                        $grossPriceRounded = ceil($grossPrice)-0.10;
                        $nettoPriceRounded = $grossPriceRounded/$taxRateDown;
                        
                        $connection = $this->container->get('dbal_connection');
                        $sql = 'UPDATE s_articles_prices SET price = :nettoPrice WHERE articleID = :articleId AND pricegroup = :priceGroup';
                        $data = $connection->fetchAll($sql,[':nettoPrice' => $nettoPriceRounded,':priceGroup'=>$customerGroup,':articleId'=>$articleID]);
                    }




                }

            }
        }


        echo json_encode(true);
        exit;



    }


    private function getConfig(): array
    {
        $connection = $this->container->get('dbal_connection');
        $configData = [];

        $sql = 'SELECT
	s_core_tax_rules.tax, 
	s_core_customergroups.groupkey
FROM
	s_core_tax_rules,
	s_core_customergroups
WHERE
	s_core_tax_rules.customer_groupID = s_core_customergroups.id';

        $configData = $connection->fetchAll($sql, [':active' => true]);

        $collection = [];
        foreach ($configData as $group) {
            $collection[$group['groupkey']] = $group['tax'];
        }

        return $collection;
    }

    public function getWhitelistedCSRFActions()
    {
        return ['index', 'test', 'copyPrices', 'roundPrices'];
    }
}
