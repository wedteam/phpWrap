     require('classes/core/core.php');

     $data = array(
	     'err' => 'ok',
	     'data' => array(
		     array(
			     'key1' => 1,
			     'key2' => 'abc',
			     'key3' => array(1,2,3)
		     ),
		     array(
			     'key1' => 2,
			     'key2' => 'def',
			     'key3' => array(1,2,3)
		     ),
		     array(
			     'key1' => 3,
			     'key2' => '1aab',
			     'key3' => array(1,2,3)
		     ),
	     ),
     );

     W($data)->query('#data key1')->each(function($a){
	 return $a = $a + 1;
     });
     W($data)->query('#data key2')->each(function($a){
	return $a = strtoupper($a);
     });
     W($data)->query('#data key3')->each(function($a){
	array_push($a, 4);
	return $a;
     });

     print_r($data);