<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class EventtypeModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('eventtypes');
            $this->errorMsg = ''; 
        }

        /**
         * üres group rekord
         */
        public function emptyRecord(): Record {
            $result = new Record();
            $result->id = 0;
            $result->name = '';
			$result->direction = 'INPUT';
			$result->partner_requied = 0;
			$result->document_id_requied = 0;
            return $result;
        }

        /**
         * rekordok lapozható listája
         * @param int $page
         * @param int $limit
         * @param string $filter - nincs használva
         * @param string $order - nincs használva
         * @return array
         */
        public function getItems(int $page, int $limit, string $filter, string $order): array {
			if ($page <= 0) $page = 1;
            $db = new Query($this->table,'d');
            $result = $db->select(['id','name','direction'])
                    ->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy('name')
                    ->all();
            return $result;        
        }

        /**
         * Összes rekord száma
         * @return int
         */
        public function getTotal($filter = ''): int {
            $db = new Query($this->table);
            return $db->count();
        }

}    
?>
