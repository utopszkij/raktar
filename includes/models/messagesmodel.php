<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class MessagesModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('messages');
            $this->errorMsg = ''; 
        }

        /**
         * üres messages rekord
         */
        public function emptyRecord(): Record {
            $result = new Record();
            
			    $result->id = 0;
			    $result->sender_name = "";
			    $result->sender_email = "";
			    $result->txt = "";
			    $result->status = "active";
			    $result->received = date('Y-m-d');
			    $result->answered = "";
			    $result->closed = "";
                $result->comment = "";
			
            return $result;
        }

		/** a filter str alapján bőviti a Query -t
		 * rendszerint át kell definiálni a mező tipusoktól függően
		 * @param Query
		 * @param string $filter 'name|value...'
		 */ 
		protected function filterToQuery(Query &$db, string $filter) {
            if ($filter != '') {        
				$filter = explode('|',$filter);        
				$i=0;
				while ($i < count($filter)) {
					if ($filter[$i+1] != '') {
						if ($filter[$i] == 'sender_name') {
							$db->where($filter[$i],'like','%'.$filter[$i+1].'%');
						} else {	
							$db->where($filter[$i],'=',$filter[$i+1]);
						}
					}	
					$i = $i + 2;
				}
			}
		}

        /**
         * rekordok lapozható listája
         * rendszerint át kell definiálni a szükséges oszlopok, mezők
         * tábla összefüggések szerint
         * @param int $page
         * @param int $limit
         * @param string $filter 'name|value...' 
         * @param string $order 
         * @return array
         */
        public function getItems(int $page, int $limit, string $filter, string $order): array {
			if ($page <= 0) $page = 1;
            $db = new Query($this->table,'d');
            $db->select(['id','received','sender_name','txt','status'])
                    ->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy('received');
            $this->filterToQuery($db,$filter);        
            $result = $db->all();
            return $result;        
        }

        /**
         * Összes rekord száma
         * @return int
         */
        public function getTotal($filter = ''): int {
            $db = new Query($this->table);
            $db->select(['id']);
            $this->filterToQuery($db,$filter);
            return $db->count();
        }

}    
?>
