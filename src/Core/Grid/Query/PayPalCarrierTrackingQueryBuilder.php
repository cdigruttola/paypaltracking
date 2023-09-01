<?php
/**
 * Copyright since 2007 Carmine Di Gruttola
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    cdigruttola <c.digruttola@hotmail.it>
 * @copyright Copyright since 2007 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

declare(strict_types=1);

namespace cdigruttola\Module\PaypalTracking\Core\Grid\Query;

use Context;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class PayPalCarrierTrackingQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var DoctrineSearchCriteriaApplicatorInterface
     */
    private $criteriaApplicator;

    /**
     * @param Connection $connection
     * @param string $dbPrefix
     * @param DoctrineSearchCriteriaApplicatorInterface $criteriaApplicator
     */
    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $criteriaApplicator
    ) {
        parent::__construct($connection, $dbPrefix);

        $this->criteriaApplicator = $criteriaApplicator;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $searchQueryBuilder = $this->getPayPalCarrierTrackingQueryBuilder($searchCriteria)
            ->select('c.*, cl.name as carrier_name, country_lang.name as country_name');

        $this->applySorting($searchQueryBuilder, $searchCriteria);

        $this->criteriaApplicator->applyPagination(
            $searchCriteria,
            $searchQueryBuilder
        );

        return $searchQueryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $countQueryBuilder = $this->getPayPalCarrierTrackingQueryBuilder($searchCriteria)
            ->select('COUNT(*)');

        return $countQueryBuilder;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    private function getPayPalCarrierTrackingQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->from($this->dbPrefix . 'paypal_carrier_tracking', 'c')
            ->leftJoin(
                'c',
                $this->dbPrefix . 'carrier',
                'cl',
                'c.id_carrier = cl.id_carrier'
            )
            ->leftJoin(
                'c',
                $this->dbPrefix . 'country',
                'country',
                'c.id_country = country.id_country'
            )
            ->leftJoin(
                'country',
                $this->dbPrefix . 'country_lang',
                'country_lang',
                'country.id_country = country_lang.id_country and country_lang.id_lang = ' . Context::getContext()->language->id
            );

        $this->applyFilters($searchCriteria->getFilters(), $queryBuilder);

        return $queryBuilder;
    }

    /**
     * Apply filters to PayPalCarrierTracking query builder.
     *
     * @param array $filters
     * @param QueryBuilder $qb
     */
    private function applyFilters(array $filters, QueryBuilder $qb)
    {
        $allowedFilters = [
            'id_paypal_carrier_tracking',
            'carrier_name',
            'country_name',
            'paypal_carrier_enum',
        ];

        foreach ($filters as $filterName => $filterValue) {
            if (!in_array($filterName, $allowedFilters)) {
                continue;
            }
            if ($filterName == 'id_paypal_carrier_tracking') {
                $qb->andWhere('c.`' . $filterName . '` = :' . $filterName);
                $qb->setParameter($filterName, $filterValue);
                continue;
            }
            if ($filterName == 'paypal_carrier_enum') {
                $qb->andWhere('c.`' . $filterName . '` LIKE :' . $filterName);
                $qb->setParameter($filterName, '%' . $filterValue . '%');
                continue;
            }
            if ($filterName == 'carrier_name') {
                $qb->andWhere('cl.`' . $filterName . '` LIKE :' . $filterName);
                $qb->setParameter($filterName, '%' . $filterValue . '%');
            }
            if ($filterName == 'country_name') {
                $qb->andWhere('country_lang.`' . $filterName . '` LIKE :' . $filterName);
                $qb->setParameter($filterName, '%' . $filterValue . '%');
            }
        }
    }

    /**
     * Apply sorting so search query builder for PayPalCarrierTracking.
     *
     * @param QueryBuilder $searchQueryBuilder
     * @param SearchCriteriaInterface $searchCriteria
     */
    private function applySorting(QueryBuilder $searchQueryBuilder, SearchCriteriaInterface $searchCriteria)
    {
        switch ($searchCriteria->getOrderBy()) {
            case 'id_paypal_carrier_tracking':
            case 'paypal_carrier_enum':
                $orderBy = 'c.' . $searchCriteria->getOrderBy();
                break;
            case 'carrier_name':
                $orderBy = 'cl.' . $searchCriteria->getOrderBy();
                break;
            case 'country_name':
                $orderBy = 'country_lang.' . $searchCriteria->getOrderBy();
                break;
            default:
                return;
        }
        $searchQueryBuilder->orderBy($orderBy, $searchCriteria->getOrderWay());
    }
}
