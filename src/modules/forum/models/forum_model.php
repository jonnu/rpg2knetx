<?php

class Forum_Model extends Model_NestedSet {
	
	public function __construct() {
		parent::__construct();
	}


	public function temp_getlist() {

		$forums = $this->retrieve_nested(null, 0, 0);

		var_dump($forums);




	}

}


class Forum_Object extends Nestable_Object {
}