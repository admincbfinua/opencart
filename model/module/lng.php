<?php
class ModelModuleLng {
	private $zone = array(3482,3486,3488,3490,3491,3493,3497,3500,3501,3502,3505);
	public function getEmaillng($id_zone) {
		
		if(in_array($id_zone,$this->zone))
		{
			//ua - lng
			return 3;
		}
		else
		{
			return 2;
		}
		
	}
	
}