<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class TagModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('tags');
            $this->errorMsg = ''; 
        }

        public function emptyRecord(): Record {
            $result = new Record();
            $result->id = 0;
            $result->name = '';
            $result->parent = 0;
            return $result;
        }

        /**
		 * teljes fa szerkezet beolvasása
		 * @param int $page
		 * @param inr $limit
		 * @param mixed $filter 
		 * @param string $order
		 * @return [{id, tulaj, szint, cimke}, ...]
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
		 * @param array &$result [{id, tulaj, szint, cimke}, ...]
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
         * @return int
         */
        public function getTotal($filter): int {
            $db = new Query($this->table);
            $recs = $db->all();
            return count($recs);
        }
        
		protected function usortFun($a, $b) {
			$result = 0;
			$order = $this->order;
			if ($a->$order < $b->$order) {
				$result = -1;
			}
			if ($a->$order > $b->$order) {
				$result = 1;
			}
			return $result;
		}
				
		/**
		 * rekurziv eljárás adott tulajdonos alrekordjait olvassa
		 * a $result tömbbe, kiegészítve a $level adattal
		 * ez a result felhasználható fa szerkezetű megjelenitéshez
		 * @param int $parentId
		 * @param int $level
		 * @param array &$result [{id, parent, level, name}, ...]
		 * @return void
		 */ 
		public function getSubItems(int $parentId, int $level, array &$result) {
			$q = new \RATWEB\DB\Query($this->table);
			$recs = $q->where('parent','=',$parentId)->orderBy('name')->all();
			foreach($recs as $rec) {
				$rec->level = $level;
				$result[] = $rec;
				$this->getSubItems($rec->id, $level+1, $result);
			} 
		}
		
		/**
		 * where tag in lista használathou tag lista előállítása
		 * @param int $parentId
		 * @return string 'tagName,tagNae,....'
		 */ 
		public function getSubList(int $parentId): string {
			$recs = [];
			$this->getSubItems($parentId,0,$recs);
			$w = [];
			foreach ($recs as $rec) {
				$w[] = $rec->name;
			}
			return implode(',',$w);
		}

  }    
?>
