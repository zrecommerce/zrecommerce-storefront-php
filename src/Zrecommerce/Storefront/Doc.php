<?php

namespace Zrecommerce\Storefront;

class Doc {
	static function getAvailabilityStatus($item) {
		$status = 'out_of_stock';
		if (empty($item)) return $status;
		
		$item = (object) $item;
		if ($item->is_available == true) {
			
			if (empty($item->availability_date)) {

				if ($item->is_finite) {

					if ((int)$item->finite_amount_available > 0) {
						$status = 'in_stock';
					} else {
						$status = 'out_of_stock';
					}

				} else {
					$status = 'in_stock';
				}
				
			} else {
				
				// Has an availability date.
				$availDate = strtotime($item->availability_date);
				$nowDate = strtotime(date('Y-m-d H:i:s'));
				
				if ($nowDate >= $availDate) {

					// Available date! Check the stock.

					if ($item->is_finite) {
						$qty = (int)$item->finite_amount_available;

						if ($qty > 0) {
							$status = 'in_stock';
						} else {
							$status = 'out_of_stock';
						}

					} else {
						$status = 'in_stock';
					}

				} else {
					// Not available yet.
					
					if ($item->is_finite) {
						$qty = (int)$item->finite_amount_available;

						if ($qty > 0) {
							$status = 'preorder';
						} else {
							$status = 'out_of_stock';
						}

					} else {
						$status = 'preorder';
					}
				}
			}

		} else {
			$status = 'out_of_stock';
		}
		
		return $status;
	}
}