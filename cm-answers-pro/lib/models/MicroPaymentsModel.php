<?php

class CMA_MicroPaymentsModel {
	
	const ACTION_DISABLE = 0;
	const ACTION_GRANT_POINTS = 1;
	const ACTION_CHARGE_POINTS = 2;
	
	
	public static function getActions() {
		return array(
			self::ACTION_DISABLE => 'disable',
			self::ACTION_GRANT_POINTS => 'grant',
			self::ACTION_CHARGE_POINTS => 'charge',
		);
	}
		
	
}