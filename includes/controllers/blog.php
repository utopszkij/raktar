<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/blogmodel.php';
include_once __DIR__.'/../models/categorymodel.php';
include_once __DIR__.'/../models/blogcommentmodel.php';
include_once __DIR__.'/../uploader.php';
include_once __DIR__.'/../urlprocess.php';

class Blog extends Controller {

	function __construct() {
		parent::__construct();
		$this->model = new BlogModel();
        $this->name = "blog";
        $this->browserURL = 'index.php?task=blog.blogs';
        $this->addURL = 'index.php?task=blog.addblog';
        $this->editURL = 'index.php?task=blog.editblog';
        $this->browserTask = 'blog.blogs';
	}

    /**
     * HTML string páratlan tagok lezárása
     * @param string $html
     */
    function closeHtmlTags(string $html): string {
        preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
    } 

	/**
     * rekord ellenörzés felvitelnél, modosításnál van hivva
     * @param Record $record
     * @return string üres vagy hibaüzenet
     */
    protected function validator($record):string {
        $result = '';
        if ($record->title == '') {
            $result = 'TITLE_REQUESTED<br>';
        }
        if ($record->body == '') {
            $result .= 'BODY_REQUESTED<br>';
        }
        $oldRecs = $this->model->getBy('title',$record->title);
        if (count($oldRecs) > 0) {
            if ($oldRecs[0]->id != $record->id) {
                $result .= 'THIS_TITLE_ALREDY_EXISTS<br>';
            }
        }
        return $result;
    }


    /**
     * bejelentkezett user jogosult erre?
	 * a forman vannak szükség esetén letiltva a modositó mezők 
     * @param string $action new|edit|delete
     * @return bool
     */
    protected function  accessRight(string $action, $record):bool {
		$result = true;
        return $result;
    }

   /*
    * Blog bejegyzés lapozható lista legfrisebb elöl
	* session/GET: page, filter
    * paraméterek a viewernek: blogs, loged, logedGroup, page, total
	*    filter: {titleStr, bodyStr, creatorName, ctreatedAt}
    *    blogs: [{id,title, descripton, commentCount, creator, createdAt, likeCount},..] 
	*    creator:{id,name, avatar}
    *    pages, task
    *akciok (session[loged] jogosultság függően) 
    *    addBlog
    *    blog (itemClick)
    */
	public function blogs() {

        // csak bejelentkezett user használhatja
        //if ($this->loged <= 0) {
        //    echo '<div class="alert alert-danger">Ezt a funkciót csak bejelenkzett felhasználók használhatják.</div>';
        //    return;
        //}
        

        $page = 1;
        $filter = new \stdClass();
        $filter->titleStr = '';
        $filter->bodyStr = '';
        $filter->creatorName = '';
        $filter->createdAt = '';
        $name = 'blogs_';
        $limit = 8;

        // $page és $filter olvasása get-ből vagy session-ből
        $page = $this->request->input('page', $this->session->input($name.'page',1));
        $filter->titleStr = $this->request->input('titlestr', $this->session->input($name.'titleStr',''));
        $filter->bodyStr = $this->request->input('bodystr', $this->session->input($name.'bodyStr',''));
        $filter->creatorName = $this->request->input('creatorname', $this->session->input($name.'creatorName',''));
        $filter->createdAt = $this->request->input('createdat', $this->session->input($name.'createdAt',''));
        $filter->category = $this->request->input('category', $this->session->input($name.'category',''));

        // $page és $filter tárolása sessionba
        $this->session->set($name.'page',$page);
        $this->session->set($name.'titleStr',$filter->titleStr);
        $this->session->set($name.'bodyStr',$filter->bodyStr);
        $this->session->set($name.'creatorName',$filter->creatorName);
        $this->session->set($name.'createdAt',$filter->createdAt);

        $blogs = $this->model->getBlogs($page,$filter,$limit,'b.created_at','DESC');
        $total = $this->model->getBlogsTotal($filter);

        $pages = [];
        $p = 0;
        $w = 0;
        while ($w < $total) {
            $p++;
            $pages[] = $p;
            $w = $w + $limit;
        }

        foreach ($blogs as $fn => $fv) {
                // bevezető szöeg (max.4 sor) kiemelése
                /*
                $s = str_replace('<',' <',$fv->body);
                $s = str_replace('</p>','¤',$s);
                $s = str_replace('</div>','¤',$s);
                $s = str_replace('</li>','¤',$s);
                $s = str_replace('<br>','¤',$s);
                $s = str_replace('<br />','¤',$s);
                $lines = explode('¤',strip_tags($s,['img']));
                $s = '';
                $i = 0;
                while ((strlen($s) < 128) & ($i < count($lines)) & ($i < 4)) {
                    if ($i > 0) {
                        $s .= '<br />';
                    }
                    $s .= $lines[$i];
                    $i++;
                }
                $s = mb_substr($s,0,125);
                $blogs[$fn]->body = str_replace('¤','<br />',$s).
                */

                if (strlen($fv->body) > 500) {
                    $w = explode('>',$fv->body);
                    $s = '';
                    $i = 0;
                    while (($i < count($w)) & (strlen($s) < 500)) {
                        $s .= $w[$i].'>';
                        $i++;
                    }
                    $s = urlprocess($this->closeHtmlTags($s));
                    $fv->body = $s.'<button class="btn btn-secondary" type="button">&gt;&gt;&gt;</button>';
                } else {
                    $fv->body = urlprocess($this->closeHtmlTags($fv->body));
                }
        }
        
        $categoryModel = new CategoryModel();
        view('blogs',[
            "loged" => $this->session->input('loged',0),
            "logedGroup" => $this->session->input('logedGroup',''),
            "blogs" => $blogs,
            "total" => $total,
            "page" => $page,
            "pages" => $pages,
            "task" => $this->browserTask,
            "filter" => $filter,
            "errorMsg" => $this->session->input('errorMsg',''),
            "successMsg" => $this->session->input('successMsg',''),
            "allCategories" => $categoryModel->getItems(0,1000,'','name')
        ]);
        $this->session->set('errorMsg','');
        $this->session->set('successMsg','');
	}

   /*
    * Blog bejegyzés és comments lista megjelenitése
	* GET: blog_id, page
    */
	public function blog() {

        $name = 'blogcomments_';
        $limit = 8;
        $blog_id = $this->request->input('blog_id',0);
        $blog = $this->model->getById($blog_id);
        $page = $this->request->input('page', $this->session->input($name.'page',1));
        $this->session->set($name.'page',$page);
        $blog->bodyHtml = urlprocess($this->closeHtmlTags($blog->body));

        // attachmentek olvasása
        $attachments = [];
        if (is_dir('images/upload/blog'.$blog->id)) {
			$attachments = array_diff(scandir('images/upload/blog'.$blog->id), array('.', '..'));
		}
        

        $commentModel = new BlogcommentModel();
        if (isset($blog->id)) {
            $comments = $commentModel->getComments($page, $blog->id, $limit, 'created_at','DESC');
            $total = $commentModel->getTotal($blog_id);
        } else {
            $comments = [];
            $total = 0;
        }    

        $pages = [];
        $p = 0;
        $w = 0;
        while ($w < $total) {
            $p++;
            $pages[] = $p;
            $w = $w + $limit;
        }

        view('blog',[
            "flowKey" => $this->newFlowKey(),
            "loged" => $this->session->input('loged',0),
            "logedGroup" => $this->session->input('logedGroup',0),
            "blog" => $blog,
            "attachments" => $attachments,
            "comments" => $comments,
            "total" => $total,
            "page" => $page,
            "pages" => $pages,
            "task" => 'blog/blog_id/'.$blog->id,
            "errorMsg" => $this->session->input('errorMsg',''),
            "successMsg" => $this->session->input('successMsg','')
        ]);
	}

    /**
     * látógatói vélemények az oldalról
     */
    public function velemenyek() {
        $blogs = $this->model->getBy('title','Vélemények');
        if (count($blogs) == 0) {
            $blog = new Record();
            $blog->id = 0;
            $blog->title = 'Vélemények';
            $blog->body = '---';
            $blog->created_by = 1;
            $blog->created_at = date('Y-m-d');
            $id = $this->model->save($blog);
            $this->request->set('blog_id',$id);
        } else {
            $this->request->set('blog_id',$blogs[0]->id);
        }
        $this->blog();
    }

   /*
    * Blog bejegyzés add form
	* session[loged] és [logedGroup] jogosultság ellenörzéssel 
    * paraméterek a viewernek: blog, loged, logedGroup
    *    blog: {id,title, descripton, commentCount, creator, created_at, like_count} 
	*    creator:{id,name, avatar}
    * akciok (session[logedGroup] jogosultság függően) 
    *    save --> saveblog
    *    cancel --> blogs
    */
	public function addblog() {
        if ($this->session->input('loged') <= 0) {
            $this->session->set('errorMsg', 'ACCESS_DENIED');
            view('blogs',[]);
        }
        $q = new Query('users');
        $user = $q->where('id','=',$this->session->input('loged'))->first();
        if (!isset($user->id)) {
            $user->id = 0;
            $user->username = '';
        }
        $blog = $this->model->emptyRecord();
        $blog->commentCount = 0;
        $blog->likeCount = 0;
        $blog->createdAt = date('Y.m.d H:i');
        $blog->creator = JSON_decode('{"name":"'.$user->username.'", "avatar":"'.$this->model->userAvatar($user->id).'"}');        
        $blog->userLike = 0;
        $this->session->set('errorMsg', '');
        $this->session->set('successMsg', '');
        view('blogform',["flowKey" => $this->newFlowKey(),
            'blog' => $blog,
            "errorMsg" => $this->session->input('errorMsg',''),
            "attachments" => [],
            "successMsg" => $this->session->input('successMsg','')
        ]);
	}

	/*
    * Blog bejegyzés edit form
	* GET: blog_id
	* session[loged] és [logedGroup] jogosultság ellenörzéssel 
    * paraméterek a viewernek: blog, loged, logedGroup
    *    blog: {id,title, descripton, commentCount, creator, created_at, like_count} 
	*    creator:{id,name, avatar}
    * akciok (session[logedGroup] jogosultság függően) 
    *    save  --> saveblog 
    *    cancel --> blogs
    */
	public function editblog() {
        if ($this->session->input('loged') <= 0) {
            $this->session->set('errorMsg', 'ACCESS_DENIED');
            view('blogs',[]);
        }
        $q = new Query('users');
        $user = $q->where('id','=',$this->session->input('loged'))->first();
        if (!isset($user->id)) {
            $user->id = 0;
            $user->username = '';
        }
        $id = $this->request->input('blog_id',0);
        $blog = $this->model->getById($id);
        if (isset($blog->id)) {

            // attachmentek olvasása
            $attachments = [];
            if (is_dir('images/upload/blog'.$blog->id)) {
                $attachments = array_diff(scandir('images/upload/blog'.$blog->id), array('.', '..'));
            }

            $this->session->set('errorMsg', '');
            $this->session->set('successMsg', '');
            view('blogform',["flowKey" => $this->newFlowKey(),
                'blog' => $blog,
                'attachments' => $attachments,
                "errorMsg" => $this->session->input('errorMsg',''),
                "successMsg" => $this->session->input('successMsg','')
            ]);
        } else {
            $this->session->set('errorMsg', 'NOT_FOUND');
            $this->session->set('successMsg', '');
            $this->blogs();
            $this->session->set('errorMsg', '');
        }    
	}

   /*
    * Blog comment edit form
	* GET: comment_id, blog_id
	* session[logedGroup] és [loged] jogosultság ellenörzéssel 
    * paraméterek a viewernek: blog, comment, loged, logedGroup
    *    blog: {id,title, descripton, commentCount, creator, created_at, like_count} 
	*        creator:{id,name, avatar}
    *    comment: {id, body, creator, created_at, like_count} 
	*        creator:{id,name, avatar}
    * akciok (session[logedGroup] jogosultság függően) 
    *    save --> saveblogcomment, 
    *    cancel  --> blog, 
    *    delete  --> deleteblogcomment
    */
	public function editblogcomment() {
        if ((strpos($this->session->input('logedGroup'),'admin') > 0) |
            (strpos($this->session->input('logedGroup'),'moderator') > 0)) {
            $id = $this->request->input('id');
            $commentModel = new BlogcommentModel();
            $comment = $commentModel->getById($id);
            if (isset($comment->id)) {
                $blog_id = $comment->blog_id;
                $blog = $this->model->getById($blog_id);
                if (isset($blog->id)) {
                    if (strlen($blog->body) > 128) {
                        $blog->body = substr(strip_tags($blog->body),0,128).'....';
                    }
                    $blog->body =
                    view('blogcommentform',["flowKey" => $this->newFlowKey(),
                                            'blog' => $blog, 
                                            'comment' => $comment,
                                            'loged' => $this->session->input('loged'),
                                            'logedGroup' => $this->session->input('logedGroup')]);
                }
            }
            $this->session->set('errorMsg','NOT_FOUND');
            view('blogs',[]);
            $this->session->set('errorMsg', '');
        } else {
            $this->session->set('errorMsg', 'ACCESS_DENIED');
            view('blogs',[]);
            $this->session->set('errorMsg', '');
        }    
	}
	
	/**
	 * blog add/edit képernyő tárolása
	 * session[loged] és [logedGroup] jogosultság ellenörzéssel 
	 * POSTban: blog_id, blog rekord adatai
     * tárolás után --> blogs
	 */ 
	public function blogsave() {
        if (!$this->checkFlowKey('index.php?task=blogs')) {
            echo 'flowKey error.'; exit();
        }
        if ($this->session->input('loged') >= 0) {
            $id = $this->request->input('id',0);
            $record = $this->model->emptyRecord();
            if ($id > 0) {
                $oldRecord = $this->model->getById($id);
                $record->id = $id;
                $record->created_by = $oldRecord->created_by;
                $record->created_at = $oldRecord->created_at;
            } else {
                $q = new Query('users');
                $record->created_at = date('Y.m.d H:i');
                $record->created_by = $this->session->input('loged');        
            }    
            $record->title = $this->request->input('title','',HTML);
            $record->body = $this->request->input('body','',HTML);

            // bajelölt categoryes checkbokok a képernyöről
            // a képernyőn a checkbox name: 'category_##' value a cetogory id
            $categories = [];
            for ($i=0; $i<30; $i++) {
                $s = $this->request->input('category_'.$i,0);
                if ($s > 0) {
                    $categories[] = $s; 
                }
            }
            $errorMsg = ($this->validator($record));
            if ($errorMsg == '') {    
                unset($record->allCategories);
                unset($record->categories);
                $id = $this->model->save($record);
                // categoryes tárolása
                $errorMsg = $this->model->errorMsg;
                $this->model->saveCategories($id, $categories);
            }    

            // attachmentek tárolása
            for ($i=0; $i<5; $i++) {
                if (isset($_FILES['attachment'.$i])) {
                    if ($_FILES['attachment'.$i]['name'] != '') {
                        $uploader = new Uploader();
                        $res = $uploader->doUpload('attachment'.$i, 
                                            'images/upload/blog'.$id, 
                                            '', 
                                            ['doc','pdf','docx','odt','txt','xlsx']); 
                        if ($res->error != '') {
                            echo 'image upload error '.$res->error; exit();
                        }					
                    }
                }
            }

            if ($errorMsg == '') {    
                $this->session->set('successMsg', 'SAVED');
                $this->session->set('errorMsg', '');
                $this->blogs();
            } else {
                $this->session->set('successMsg', '');
                $this->session->set('errorMsg', $errorMsg);
                $this->blogs();
            }
        } else {
            $this->session->set('errorMsg', 'ACCESS_DENIED');
            $this->session->set('successMsg', '');
            $this->blogs();
            $this->session->set('successMsg', '');
            $this->session->set('errorMsg', '');
        }
	}

	/**
	 * blog comment add/edit képernyő tárolása
	 * session[loged] és [logedGroup] jogosultság ellenörzéssel 
	 * POSTban: blog_id, comment rekord adatai, parent, parentname
	 * SESSIONBAN logged, logedGroup
	 * tárolás után --> blogcomments
     * 
     * ha parent > 0
     *    ha a megadott parentben parent==0akkor az új rekordban a parent a POST -ban érkezett
     *    ha a megadott parentben parent>0akkor az új rekordban a parent a parent rekordban lévő parent
	 */ 
	public function blogcommentsave() {
        if ($this->session->input('loged') > 0) {
            $record = new \RATWEB\DB\Record();
            $record->blog_id = $this->request->input('blog_id',0);
            $record->id = $this->request->input('id',0);
            $record->body = $this->request->input('commentbody','',HTML);
            if ($record->id == 0) {
                $record->created_at = date('Y.m.d');
                $record->created_by = $this->session->input('loged',0);
            }
            if (!$this->checkFlowKey('index.php?task=blog&blog_id='.$record->blog_id)) {
                echo 'flowKey error. Lehet hogy túl hosszú várakozás miatt lejárt a munkamenet, vagy a böngésző frissitést használtad.'; exit(); 
            }
            $commentModel = new BlogcommentModel();

            // válasz kezelés
            $parentId = $this->request->input('parent',0);
            $parentName = $this->request->input('parentname','');
            if ($parentId > 0) {
				if ($record->id == 0) {
					$record->body = '<var class="parentName">#'.$parentName.'</var>:&nbsp;'.$record->body;
			    }
				$record->parent = $parentId;
                // most jön a $parentId szükség szerinti felülbirálata....
                // (hogy ne legyen végtelen mélységű fa szerkezet)
                $parent = $commentModel->getById($parentId);
                if (isset($parent->id)) {
                    if ($parent->parent > 0) {
                        $record->parent = $parent->parent;
                    }
                }
            } else {
                $record->parent = 0;
            }

            $commentModel->save($record);

            $this->session->set('errorMsg', '');
            $this->session->set('successMsg', 'SAVED');
            $this->blog();
            $this->session->set('successMsg', '');
            $this->session->set('errorMsg', '');
        } else {
            $this->session->set('errorMsg', 'ACCESS_DENIED');
            $this->session->set('successMsg', '');
            $this->blog();
            $this->session->set('successMsg', '');
            $this->session->set('errorMsg', '');
        }    
	}
	

	/**
	 * blog bejegyzés és kommentjei, like -jai törlése
	 * GET param: blog_id 
     * jogosultság ellenörzés!
	 * SESSIONban loged, logedGroup
	 * Jogosultság ellenörzés!
     * törlések után --> blogs
	 */ 
	public function delblog() {
        if (($this->session->input('logedGroup') == 'admin') |
            ($this->session->input('logedGroup') == 'moderator') |
            (strpos($this->session->input('logedGroup'),'admin') > 0)) {
            $id = $this->request->input('blog_id',0);
            $this->model->delById($id);

            // attachmentek törlése
            $attachments = [];
            if (is_dir('images/upload/blog'.$blog->id)) {
                $attachments = array_diff(scandir('images/upload/blog'.$blog->id), array('.', '..'));
                foreach ($attachments as $attachemnt) {
                    unlink('images/upload/blog'.$blog->id.'/'.$attachment);
                }
            }
            
            $this->session->set('successMsg','DELETED');
            $this->session->set('errorMsg','');
            $this->blogs();    
            $this->session->set('successMsg','');
            $this->session->set('errorMsg','');
        } else {
            $this->session->set('successMsg','');
            $this->session->set('errorMsg','ACCESS_DENIED');
            $this->blogs();    
            $this->session->set('successMsg','');
            $this->session->set('errorMsg','');
        }
	}

   	/**
	 * attachment törlése
    */ 
	public function delattach() {
		$id = $this->request->input('id','0');
		$attachment = $this->request->input('attachment','');
		unlink('images/upload/blog'.$id.'/'.$attachment);
		$this->edit();
	}


	/**
	 * blog komment törlése
	 * GET param: comment_id, blog_id jogosultság ellenörzés!
	 * SESSIONban loged, logedGroup
	 * Jogosultság ellenörzés!
     * törlés után --> blog
	 */ 
	public function blogcommentdelete() {
        if ((strpos($this->session->input('logedGroup'),'admin') > 0) |
            (strpos($this->session->input('logedGroup'),'moderator') > 0)) {
            $blog_id = $this->request->input('blog_id',0);
            $comment_id = $this->request->input('comment_id',0);
            $commentModel = new BlogcommentModel();
            $commentModel->delById((int) $comment_id);
            $this->session->set('successMsg','DELETED');
            $this->session->set('errorMsg','');
            $this->blog();    
            $this->session->set('successMsg','');
            $this->session->set('errorMsg','');
        } else {
            $this->session->set('successMsg','');
            $this->session->set('errorMsg','ACCESS_DENIED');
            $this->blog();    
            $this->session->set('successMsg','');
            $this->session->set('errorMsg','');
        }
  	}


}


?>
