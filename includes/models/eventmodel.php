<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class EventModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('events');
            $this->errorMsg = ''; 
        }

        /**
         * üres group rekord
         */
        public function emptyRecord(): Record {
            $result = new Record();
            $result->id = 0;
            $result->direction = 'INPUT';
            $result->event_type = '';
            $result->product_id = 0;
            $result->quantity = 0;
            $result->event_date = date('Y-m-d');
            $result->partner_id = 0;
            $result->document_id = '';
            $result->document_link = '';
            $result->description = '';
            $result->created_by = $_SESSION['loged'];
            $result->created_at = date('Y-m-d');
            return $result;
        }

        /**
         * rekordok lapozható listája
         * @param int $page
         * @param int $limit
         * @param string $filter 'field_name|fieldValue
         * @param string $order 
         * @return array
         */
        public function getItems(int $page, int $limit, string $filter, string $order): array {
			if ($page <= 0) $page = 1;
            $db = new Query($this->table,'e');
            $db->join('LEFT OUTER','products','p','p.id','=','e.product_id')
				->join('LEFT OUTER','eventtypes','et','et.id','=','e.event_type')
				->select(['e.id','e.direction','et.name event_type',
                'e.product_id','e.quantity','e.event_date','p.sort_name','p.unit']);
			if ($filter != '') {
				$w = explode('|',$filter);
				while (count($w) < 4) {
					$w[] = '';
				}
			} else {
				$w = ['','','',''];
			}	
            $i = 0;
            while ($i < count($w)) {
                if ($w[$i] == 'direction') {
                    if ($w[$i+1] != '') {
                        $db->where('e.direction','=',$w[$i+1]);
                    }    
                }	
                if ($w[$i] == 'sort_name') {
                    if ($w[$i+1] != '') {
                        $db->where('p.sort_name','like','%'.$w[$i+1].'%');
                    }    
                }	
                if ($w[$i] == 'product_id') {
                    if ($w[$i+1] != '') {
                        $db->where('e.product_id','=',$w[$i+1]);
                    }    
                }	
                if ($w[$i] == 'event_date_min') {
                    if ($w[$i+1] != '') {
                        $db->where('e.event_date','>=',$w[$i+1]);
                    }    
                }	
                if ($w[$i] == 'event_date_max') {
                    if ($w[$i+1] != '') {
                        $db->where('e.event_date','<=',$w[$i+1]);
                    }    
                }	
                $i = $i + 2;
            }
            $result = $db->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy('event_date')
                    ->all();
            return $result;        
        }

        /**
         * Összes rekord száma
         * @return int
         */
        public function getTotal($filter = ''): int {
			$res = $this->getItems(1, 99999999, $filter, '1');
			return count($res);
        }
        
        /**
         * product rekord olvasás
         * @param int $id
         * @raturn Record
         */  
        public function getProduct(int $id): Record {
			$q = new Query('products');
			return $q->where('id','=',$id)->first();
		}

        /**
         * eventtypes rekordok olvasása
         * @param string $direction
         * @raturn array
         */  
        public function getEventtypes(string $direction): array {
			$q = new Query('eventtypes');
			return $q->where('direction','=',$direction)->all();
		}

}    
?>
