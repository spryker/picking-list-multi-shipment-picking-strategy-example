<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\PickingListMultiShipmentPickingStrategyExample\Business\Generator;

use Generated\Shared\Transfer\PickingListCollectionTransfer;
use Generated\Shared\Transfer\PickingListOrderItemGroupTransfer;

interface MultiShipmentPickingListGeneratorInterface
{
    public function generatePickingLists(PickingListOrderItemGroupTransfer $pickingListOrderItemGroupTransfer): PickingListCollectionTransfer;
}
