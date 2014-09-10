<?php

class phpfix{
	public static function is_empty($val)
	{
		if(is_array($val))
		{
			return empty($val);
		}
		else if(is_string($val))
		{
			return $val === '';
		}

		throw new Kohana_Exception("core.invalid_parameter", 'val', __CLASS__, __FUNCTION__);
	}
}