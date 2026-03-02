<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Zed\PickingListMultiShipmentPickingStrategyExample;

use Codeception\Actor;
use DateTime;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\DataBuilder\ShipmentBuilder;
use Generated\Shared\DataBuilder\StockBuilder;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\StockTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class PickingListMultiShipmentPickingStrategyExampleBusinessTester extends Actor
{
    use _generated\PickingListMultiShipmentPickingStrategyExampleBusinessTesterActions;

    /**
     * @var string
     */
    public const DEFAULT_OMS_PROCESS_NAME = 'Test01';

    public function expandOrderItemsWithShipment(
        OrderTransfer $orderTransfer,
        ?ShipmentTransfer $shipmentTransfer = null
    ): OrderTransfer {
        $shipmentTransfer = $shipmentTransfer ?? $this->createShipmentTransfer();
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $itemTransfer->setShipment($shipmentTransfer);
        }

        return $orderTransfer;
    }

    public function createPersistedOrderTransfer(): OrderTransfer
    {
        $saveOrderTransfer = $this->haveOrder(
            [],
            static::DEFAULT_OMS_PROCESS_NAME,
        );

        return $this->getOrderTransfer($saveOrderTransfer->getIdSalesOrder());
    }

    public function createPersistedOrderTransferFromQuote(QuoteTransfer $quoteTransfer): OrderTransfer
    {
        $saveOrderTransfer = $this->haveOrderFromQuote(
            $quoteTransfer,
            static::DEFAULT_OMS_PROCESS_NAME,
        );

        return $this->getOrderTransfer(
            $saveOrderTransfer->getIdSalesOrder(),
        );
    }

    public function createQuoteTransferWithThreeItems(): QuoteTransfer
    {
        return (new QuoteBuilder())
            ->withStore()
            ->withItem()
            ->withItem()
            ->withItem()
            ->withCustomer()
            ->withTotals()
            ->withShippingAddress()
            ->withBillingAddress()
            ->withCurrency()
            ->build();
    }

    /**
     * @param array<string, mixed> $seedData
     *
     * @return \Generated\Shared\Transfer\StockTransfer
     */
    public function createStockTransfer(array $seedData = []): StockTransfer
    {
        return (new StockBuilder($seedData))->build();
    }

    public function createShipmentTransfer(): ShipmentTransfer
    {
        return (new ShipmentTransfer())
            ->setRequestedDeliveryDate(
                (new DateTime())->format('Y-m-d'),
            );
    }

    public function updateSalesOrderItemWithIdSalesShipment(int $idSalesOrderItem, int $idSalesShipment): void
    {
        $salesOrderItemEntity = $this->getSalesOrderItemQuery()
            ->filterByIdSalesOrderItem($idSalesOrderItem)
            ->findOne();

        $salesOrderItemEntity->setFkSalesShipment($idSalesShipment);
        $salesOrderItemEntity->save();
    }

    public function getShipmentTransfer(): ShipmentTransfer
    {
        $shipmentTransfer = (new ShipmentBuilder())
            ->withShippingAddress()
            ->withCarrier()
            ->withMethod()
            ->build();

        $countryTransfer = $this->haveCountry();
        $addressTransfer = $shipmentTransfer->getShippingAddress()
            ->setCountry($countryTransfer)
            ->setFkCountry($countryTransfer->getIdCountry());

        $addressTransfer = $this->haveSalesOrderAddress($addressTransfer);

        return $shipmentTransfer->setShippingAddress($addressTransfer);
    }

    public function expandOrderItemsWithShipments(
        OrderTransfer $orderTransfer,
        ShipmentTransfer $shipmentTransfer1,
        ShipmentTransfer $shipmentTransfer2
    ): void {
        $itemsIterator = $orderTransfer->getItems()->getIterator();

        $this->expandItemTransferWithPersistedShipment($itemsIterator->current(), $shipmentTransfer1);
        $itemsIterator->next();
        $this->expandItemTransferWithPersistedShipment($itemsIterator->current(), $shipmentTransfer2);
        $itemsIterator->next();
        $this->expandItemTransferWithPersistedShipment($itemsIterator->current(), $shipmentTransfer2);
    }

    protected function getSalesOrderItemQuery(): SpySalesOrderItemQuery
    {
        return SpySalesOrderItemQuery::create();
    }

    protected function getOrderTransfer(int $idSalesOrder): OrderTransfer
    {
        return $this->getLocator()->sales()->facade()->findOrderByIdSalesOrder($idSalesOrder);
    }

    protected function expandItemTransferWithPersistedShipment(ItemTransfer $itemTransfer, ShipmentTransfer $shipmentTransfer): void
    {
        $this->updateSalesOrderItemWithIdSalesShipment(
            $itemTransfer->getIdSalesOrderItemOrFail(),
            $shipmentTransfer->getIdSalesShipmentOrFail(),
        );
        $itemTransfer->setShipment(
            (new ShipmentTransfer())->setIdSalesShipment($shipmentTransfer->getIdSalesShipmentOrFail()),
        );
    }
}
