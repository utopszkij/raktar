<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    include_once __DIR__.'/../models/categorymodel.php';

    class BlogModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('blogs');
            $this->errorMsg = ''; 
        }

        /**
         * logikai user rekord (users+profilok)
         */
        public function emptyRecord(): Record {
            $result = new Record();
            $result->id = 0;
            $result->title = '';
            $result->body = '';
            $result->created_by = '';
            $result->created_at = '';
            $result->categories = [];
            $categoryModel = new CategoryModel();
            $result->allCategories = $categoryModel->getItems(0,1000,11,'name');
            return $result;
        }

        /**
         * a $filter alapján Query -t alakit ki
         * @param Query $q
         * @param object $filter {titleStr, bodyStr, creatorName, createdAt}
         */
        protected function processBlogFilter(&$q, $filter) {
            if ($filter->titleStr != '') {
                $q->where('b.title','like','%'.$filter->titleStr.'%');
            }
            if ($filter->bodyStr != '') {
                $q->where('b.body','like','%'.$filter->bodyStr.'%');
            }
            if ($filter->createdAt != '') {
                $q->where('b.created_at','>=',$filter->createdAt);
            }
            if ($filter->category != '') {
                $categoryModel = new CategoryModel();
                $catIds = [$filter->category];
                $w = [];
                $categoryModel->getItems1($filter->category, 0, $w);
                foreach($w as $w1) {
                    $catIds[] = $w1->id;
                } 
                $q->where('c.id','in',$catIds);
            }
            
            if ($filter->creatorName != '') {
                $q2 = new Query('users');
                $user = $q2->where('username','=',$filter->creatorName)->first();
                if (isset($user->id)) {
                    $q->where('b.created_by','=',$user->id);
                } else {
                    $q->where('b.created_by','=',0);
                }
            }
        }

        /**
         * user name lapján avatar kép url -t képez
         * @param string $name
         * @return string
         */
        public function userAvatar(int $user_id): string {
            $result = 'images/users/noavatar.png';
            /*
            $q = new Query('profilok');
            $p = $q->where('id','=',$user_id)->first();
            if (isset($p->id)) {
                if ($p->avatar != '') {
                    $result = 'images/users/'.$p->avatar;
                }
            }
            */
            return $result;
        }

        /**
         * blogg böngésző számára rekord set
         * @param int $page
         * @param object $filter {titleStr, bodyStr, creatorName, createdAt}
         * @param int $limit
         * @param string $oder
         * @param string $orderDir 'ASC'|'DESC'
         * @return array
         */
        public function getBlogs(int $page, $filter,
                 int $limit, string $order, string $orderDir):array {
            $q = new Query('blogs','b');
            $this->processBlogFilter($q, $filter); 
            $q->select(['b.id','b.title','b.body',
                               'b.created_by','b.created_at createdAt','group_concat(c.name,",") categories'])
                    ->join('LEFT','blog_category','bc','bc.blog_id','=','b.id')
                    ->join('LEFT','categories','c','c.id','=','bc.category_id')           
                    ->groupBy(['b.id','b.title','b.body','b.created_by','b.created_at'])
                    ->offset(($page-1)*$limit)
                    ->limit($limit)
                    ->orderBy($order)
                    ->orderDir($orderDir);
            $result = $q->all();

            foreach ($result as $res) {
                $q2 = new Query('users','u');
                $user = $q2->select(['u.id','u.username,u.avatar'])
                ->where('u.id','=',$res->created_by)->first();
                $res->creator = new \stdClass();
                if (isset($user->id)) {
                    $res->creator->id = $user->id;
                    $res->creator->name = $user->username;
                    $res->creator->avatar = 'images/users/'.$user->avatar;
                    $res->creator->group = 'admin';
                } else {
                    $res->creator->id = 0;
                    $res->creator->name = '';
                    $res->creator->avatar = 'images/users/noavatar.png';
                    $res->creator->group = '';
                }
                // userGroup TEST

                $q2 = new Query('blogcomments');
                $res->commentCount = count(
                    $q2->select(['id'])
                    ->where('blog_id','=',$res->id)
                    ->all()
                );
                $q2 = new Query('likes');
                $res->likeCount = 0;
            }
            return $result;        
        }

        /**
         * filter object alapján összes rekord számot ad vissza
         * @param object $filter {titleStr, bodyStr, creatorName, createdAt}
         * @return int
         */
        public function getBlogsTotal($filter):int {
            $q = new Query('blogs','b');
            $q->select(['b.id','b.title','b.body',
                               'b.created_by','b.created_at createdAt'])
                    ->join('LEFT','blog_category','bc','bc.blog_id','=','b.id')
                    ->join('LEFT','categories','c','c.id','=','bc.category_id')           
                    ->groupBy(['b.id','b.title','b.body','b.created_by','b.created_at']);
            $this->processBlogFilter($q,$filter); 
            return count($q->select(['b.id'])->all());
        }

        /**
         * id alapján blog rekord és néhány kapcsolodó infó olvasása
         * @param int $id
         * @return object
        */
        public function getById(int $id):Record {
            $q = new Query('blogs');
            $result = $q->where('id','=',$id)->first();
            if (isset($result->id)) {
                $q2 = new Query('users','u');
                $user = $q2->select(['u.id','u.username','u.avatar'])
                ->where('u.id','=',$result->created_by)->first();
                $result->creator = new \stdClass();
                if (isset($user->id)) {
                    $result->creator->id = $user->id;
                    $result->creator->name = $user->username;
                    $result->creator->avatar = $user->avatar;
                    $result->creator->group = 'admin';
                } else {
                    $result->creator->id = 0;
                    $result->creator->name = '';
                    $result->creator->avatar = 'images/users/noavatar.png';
                    $result->creator->group = '';
                }
                // usergroup   TEST

                $q2 = new Query('blogcomments');
                $result->commentCount = count(
                    $q2->select(['id'])
                    ->where('blog_id','=',$result->id)
                    ->all()
                );
                $q2 = new Query('likes');
                $result->likeCount = 0;
                $result->userLike = false;
            }
            $db = new Query('blog_category','b');
            $result->categories = $db->join('LEFT','categories','c','c.id','=','b.category_id')
            ->select(['b.id','b.category_id','c.name'])
            ->where('b.blog_id','=',$result->id)
            ->orderBy('c.name')
            ->all();
            $db = new Query('categories');
            $result->allCategories = $db->orderBy('name')->all();
            return $result;
        }

        /**
         * id alapján blog rekord és alrekordjainak törlése
         * @param int $id
         * @return bool
        */
        public function delById(int $id):bool {
            $result = true;
            $q = new Query('blogs');
            $q->where('id','=',$id)->delete();
            $q2 = new Query('blogcomments');
            $q2->where('blog_id','=',$id)->delete();
            /*
            $q2 = new Query('likes');
            $q2->where('target_id','=',$id)
            ->where('target_type','=','blog')
            ->delete();
            */
            return ($q->error == '');
        }

        /**
         * blog categories tárolása
         * @param int blog id
         * @param array [category.id,....]
         */
        public function saveCategories($id, $categories) {
            $db = new Query('blog_category');
            $db->where('blog_id','=',$id)->delete();
            foreach ($categories as $category) {
                $rec = new Record();
                $rec->id = 0;
                $rec->blog_id = $id;
                $rec->category_id = $category;
                $db->insert($rec);
            }
        }

        public function getAllCategories() {
            $db = new Query('categories');
            return $db->orderBy('name')->all();
        }
}    
?>
