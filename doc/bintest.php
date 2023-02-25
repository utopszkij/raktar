<?php
	// bináris afadt tárolás, adat elérés algoritmus tesztelése

	global $nodeCount, $limit;
	$nodeCount = 4;
	$limit = 0;

    function getData($self, $min, $max) {
		global $nodeCount, $limit;
		echo 'Lekérdezés '.$self.' '.$min.' '.$max."\n";
		$limit++;
		if ($limit > 100) { echo 'LIMIT '.$limit; exit; }  
		if ($self >= $max) {
			return;
		}
		$a = $self+1;
		$b = round($self + (($max-$min)/2));

		if (($b < $nodeCount) & ($b > $a)) {
			getData($a,$a+1,$b-1);
			getData($b,$b+1,$max);
		} else if ($a <= $max) {
			getData($a,$a+1, $max);
		}
	}

	getData(0,1,$nodeCount-1);

?>