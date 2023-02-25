<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class ProductModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('products');
            $this->errorMsg = ''; 
        }

        /**
         * üres product rekord
         */
        public function emptyRecord(): Record {
            $result = new Record();
			$result->id = 0;
			$result->sort_name = '';
			$result->description = '';
			$result->unit = 'db'; 
			$result->document_id ='';
			$result->document_link = ''; 
			$result->image_link = '';
			$result->qrcode = ''; 
			$result->storage_id = 1; 
			$result->tags = '';  // tags.name vesszővel szeparát lista
			$result->unit_price = 0; 
			$result->created_by = $_SESSION['loged']; 
			$result->created_at = date('Y-m-d');
            return $result;
        }

        /**
         * rekordok lapozható listája
         * @param int $page
         * @param int $limit
         * @param string $filter  name|storage|tag vagy üres
         * @param string $order 
         * @return array
         */
        public function getItems(int $page, int $limit, string $filter, string $order = 'sort_name'): array {
			if ($page <= 0) $page = 1;
			
            $db1 = new Query($this->table,'p');
            $db1->join('LEFT OUTER','events','e','e.product_id','=','p.id')
				->join('LEFT OUTER','storages','s','s.id','=','p.storage_id')
				->select(['p.id','p.sort_name','p.image_link','s.code','p.tags','sum(e.quantity) stock'])
				->groupBy(['p.id','p.sort_name','p.tags','s.code']);
			if ($filter != '') {
				$w = explode('|',$filter);
				while (count($w) < 3) {
					$w[] = '';
				}
			} else {
				$w = ['','',''];
			}	
			if ($w[0] != '') {
				$db1->where('p.sort_name','like','%'.$w[0].'%');
			}	
			if ($w[1] != '') {
				$db1->where('s.code','=',$w[1]);
			}	
			if ($w[2] != '') {
				$db1->where('tags','like','%'.$w[2].'%');
			}	
            $result = $db1->offset((($page - 1) * $limit))
                    ->limit($limit)
                    ->orderBy($order)
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
         * rekord kézlet olvasása
         * @param string tableName
         * @param string order
         * @return array
         */ 
        public function getRecords(string $table, string $order = 'id'): array {
			$q = new Query($table);
			return $q->orderBy($order)->all();
		}
        
}    
?>
