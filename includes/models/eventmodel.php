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
				->select(['e.id','e.direction','et.name event_type','e.product_id','e.quantity','e.event_date','p.sort_name','p.unit']);
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
                if ($w[0] == 'event_type') {
                    $db->where('e.event_type','like','%'.$w[1].'%');
                }	
                if ($w[0] == 'product_id') {
                    $db->where('e.product_id','=',$w[1]);
                }	
                if ($w[0] == 'event_date_min') {
                    $db->where('e.evant_date','>=',$w[1]);
                }	
                if ($w[0] == 'event_date_max') {
                    $db->where('e.evant_date','<=',$w[1]);
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
