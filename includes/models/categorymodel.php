<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class CategoryModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('categories');
            $this->errorMsg = ''; 
        }

        public function emptyRecord(): Record {
            $result = new Record();
            $result->id = 0;
            $result->name = '';
            $result->parent = 0;
            $result->parentName = '';
			$db = new Query($this->table);
			$result->all = $db->orderBy('name')->all();
            return $result;
        }

        /**
		 * teljes fa szerkezet beolvasása
		 * @param int $page
		 * @param inr $limit
		 * @param mixed $filter 
		 * @param string $order
		 * @return [{id, parent, szint, name}, ...]
		 */ 
		public function getItems(int $page,int $limit,$filter,string $order): array {
			$result = [];
			$this->getItems1(0,0,$result);
			return $result;
		}
		
		/**
		 * rekurziv eljárás adott tulajdonos alrekordjait olvassa
		 * a $result tömbbe, kiegészítve a $level adattal
		 * @param int $owner
		 * @param int $level
		 * @param array &$result [{id, parent, szint, name}, ...]
		 * @return void
		 */ 
		public function getItems1(int $owner, int $level, array &$result) {
			$q = new \RATWEB\DB\Query($this->table);
			$recs = $q->where('parent','=',$owner)->orderBy('name')->all();
			foreach($recs as $rec) {
				$rec->szint = $level;
				$result[] = $rec;
				$this->getItems1($rec->id, $level+1, $result);
			} 
		}


        /**
         * Összes rekord száma
         * @param $filter
         * @return int
         */
        public function getTotal($filter): int {
            $db = new Query($this->table);
            $recs = $db->all();
            return count($recs);
        }
        
        /** 
         * category record (kiegészitve az all, és parentName adattal)
         * @param int id
         */ 
        public function getById(int $id):Record {
			$result = parent::getById($id);
			$db = new Query($this->table);
			$result->all = $db->orderBy('name')->all();
			$parent  = $db->where('id','=',$result->parent)->first();
			if (isset($parent->name)) {
				$result->parentName = $parent->name;
			} else {
				$result->parentName = '';
			}
			return $result;
		}

  }    
?>
