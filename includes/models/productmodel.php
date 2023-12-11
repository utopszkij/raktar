<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    include_once 'includes/models/tagmodel.php';

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
            $result->error_stock = 1;
            $result->warning_stock = 5;
			$result->created_by = $_SESSION['loged']; 
			$result->created_at = date('Y-m-d');
            return $result;
        }

        /**
         * rekordok lapozható listája
         * @param int $page
         * @param int $limit
         * @param string $filter  'filter_name'|name|
         *                         'filter_code'|code|
         *                         'filter_tag'|tag|
         * @param string $order 
         * @param bool $fullTree
         * @return array
         */
        public function getItems(int $page, int $limit, string $filter, 
            string $order = 'sort_name', bool $fullTree = true): array {
			if ($page <= 0) $page = 1;
			
            $db1 = new Query($this->table,'p');
            $db1->join('LEFT OUTER','events','e','e.product_id','=','p.id')
				->join('LEFT OUTER','storages','s','s.id','=','p.storage_id')
				->select(['p.id','p.sort_name','p.image_link','p.warning_stock','p.error_stock',
                    'concat(s.code," ",s.subname) code' ,'p.tags','sum(e.quantity) stock', 'p.unit'])
				->groupBy(['p.id','p.sort_name','p.tags','s.code','p.unit','p.warning_stock','p.error_stock']);
			if ($filter != '') {
				$w = explode('|',$filter);
				while (count($w) < 8) {
					$w[] = '';
				}
			} else {
				$w = ['filter_name','','filter_code','','filter_tag','','filter_full',1];
			}	
			if ($w[1] != '') {
				$db1->where('p.sort_name','like','%'.$w[1].'%');
			}	
			if ($w[3] != '') {
				$db1->where('s.code','like','%'.$w[3].'%');
			}	
			if ($w[5] != '') {
                // tag szerinti keresés 
                // $fullTree esetén beolvasni a tag összes alrekordjaihoz rendelteket is
                $tagModel = new TagModel();
                if ($w[5] == 'root') {
                    $recs = [JSON_decode('{"id":0}')];
                } else {
                    $recs = $tagModel->getBy('name', $w[5]);
                }  
                if (count($recs) > 0) {    
                    $subTags = [];      
                    if ($fullTree) {
                        $tagModel->getItems1($recs[0]->id, 0, $subTags);
                    }    
                    // összeállítjuk az sql in szürő stringet
                    $w2 = [$w[5]];
                    foreach ($subTags as $subTag) {
                        $w2[] = $subTag->name;
                    }
                    $db1->where('tags','in',$w2);
                    foreach ($w2 as $w3) {    
                        $db1->orWhere('tags','like','%'.$w3.'%');
                    }    
                }    
			}	
    //echo $db1->getSql().'<br />';
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
        public function getTotal($filter = '',$fullTree): int {
			$res = $this->getItems(1, 99999999, $filter, '1',$fullTree);
			return count($res);
        }
        
        /**
         * rekord készlet olvasása
         * @param string tableName
         * @param string order
         * @return array
         */ 
        public function getRecords(string $table, string $order = 'id'): array {
			$q = new Query($table);
			return $q->orderBy($order)->all();
		}

        public function getById(int $id): Record {
            $result = parent::getById($id);
            $db = new Query('events');
            $recs = $db->where('product_id','=',$id)
                    ->select(['sum(quantity) quantity'])
                    ->groupBy(['product_id'])
                    ->all();
            if (count($recs) > 0) {        
                $result->quantity = $recs[0]->quantity;        
            } else {
                $result->quantity = 0;
            }    
            return $result;
        }
        
}    
?>
