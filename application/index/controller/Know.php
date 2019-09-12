<?php
namespace app\index\controller;
use think\Controller;
use Elasticsearch\ClientBuilder;
use think\Db;
use think\Loader;
class Know extends Controller
{
    protected $es;
    public function __construct()
    {
        $params = array(
            '192.168.30.209:9200'
        );
        $this->es = ClientBuilder::create()->setHosts($params)->build();
        $this->word = Loader::model('Word');
    }
    //创建索引
    public function setindex()
    {
        $params = [
            // 索引名称
            'index' => 'know02',
            'body' => [
                //配置
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            //同义词
                            'my_synonym_filter' => [
                                'type' => 'dynamic_synonym',
                                //本地同义词路径
                                //  'synonyms_path' => "analysis/synonym.txt"
                                // 远程同义词路径（文件utf-8编码）
                                'synonyms_path' => "http://192.168.30.209/synonym.txt",
                                //同义词文件30秒刷新一次
                                "interval" => 30
                            ]
                        ],
                        'analyzer' => [
                            //配置同义词过滤器
                            'my_synonyms' => [
                                //最细粒度拆分
                                'tokenizer' => 'ik_max_word',
                                'filter' => [
                                    'lowercase',
                                    'my_synonym_filter'
                                ]
                            ]
                        ]
                    ]
                ],
                // 映射
                'mappings' => [
                    'know_test01' => [
                        //配置数据结构与类型
                        'properties' => [
                            "leve"=>[
                                'type' => 'integer'
                            ],
                            "jurisdiction"=>[
                                'type' => 'integer'
                            ],
                            "channel"=>[
                                'type' => 'integer'
                            ],
                            "type"=>[
                                'type' => 'integer'
                            ],
                            "attribute"=>[
                                'type' => 'integer'
                            ],
                            "over_stime"=>[
                                'type' => 'text'
                            ],
                            "over_etime"=>[
                                'type' => 'text'
                            ],
                            "reason"=>[
                                'type' => 'text'
                            ],
                            'title' => [
                                //text会被拆分
                                'type' => 'text',
                                //使用同义词
                                'analyzer' => 'my_synonyms',
                                'fields' => [
                                    //拼音会被拆分
                                    'pinyin' => [
                                        'type' => 'text',
                                        'analyzer' => 'pinyin',
                                    ]
                                ]
                            ],
                            'cabstract_text' => [
                                //text会被拆分
                                'type' => 'text',
                                //使用同义词
                                'analyzer' => 'my_synonyms',
                                'fields' => [
                                    'pinyin' => [
                                        'type' => 'text',
                                        'analyzer' => 'pinyin'
                                    ],
                                    'suggest' => [
                                        //completion用前缀搜索的特殊结构
                                        'type' => 'completion',
                                        'analyzer' => 'ik_max_word'
                                    ]
                                ]
                            ],
                            "cabstract_img"=>[
                                'type' => 'text'
                            ],
                            'maintext' => [
                                //text会被拆分
                                'type' => 'text',
                                //使用同义词
                                'analyzer' => 'my_synonyms',
                                'fields' => [
                                    'pinyin' => [
                                        'type' => 'text',
                                        'analyzer' => 'pinyin'
                                    ],
                                    'suggest' => [
                                        //completion用前缀搜索的特殊结构
                                        'type' => 'completion',
                                        'analyzer' => 'ik_max_word'
                                    ]
                                ]
                            ],
                            "standard"=>[
                                'type' => 'text'
                            ],
                            "similar"=>[
                                'type' => 'text'
                            ],
                            "answer"=>[
                                'type' => 'text'
                            ],
                            "coll_num"=>[
                                'type' => 'integer'
                            ],
                            "fab_num"=>[
                                'type' => 'integer'
                            ],
                            "down_num"=>[
                                'type' => 'integer'
                            ],
                            "click_num"=>[
                                'type' => 'integer'
                            ],
                            "status"=>[
                                'type' => 'integer'
                            ],
                            "send_stime"=>[
                                'type' => 'text'
                            ],
                            "send_etime"=>[
                                'type' => 'text'
                            ],
                            "datetime"=>[
                                'type' => 'date'
                            ],
                            "deletetime"=>[
                                'type' => 'keyword'
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $res = $this->es->indices()->create($params);
        var_dump($res);
    }
    //添加数据
    public function addindex()
    {
        $params =[
            'index' => 'know02',
            'type' => 'know_test01',
            'body' => [
                'k_id' => 1,
                'leve' => 2,
                'jurisdiction' => 1,
                'channel' => 1,
                'type' => 2,
                'attribute' => 1,
                'over_stime' => "2019-07-01",
                'over_etime' => "2019-08-02",
                'reason' => "测试文本类型05",
                'title' => "松花粉",
                'cabstract_text' => "视频类型的摘要01",
                'cabstract_img' => "/home/wwwroot/default/tp/public/download/images/1561455587.jpeg",
                'maintext' => "视频类型的正文01",
                'standard' => "标准问题03",
                'similar' => "相似问题03",
                'answer' => "问题答案03",
                'coll_num' => 16,
                'fab_num' => 17,
                'down_num' => 18,
                'click_num' => 19,
                'status' => 2,
                'datetime' => time(),
                'deletetime'=>'2019-06-06'
            ]
        ];
        $response = $this->es->index($params);
        var_dump($response);
    }
    //搜索
    public function serachindex()
    {
        $post = input('post.');
        //普通搜索
        if(!isset($post['type'])) return jsonp(['code'=>"201",'massgae'=>"参数有误",'data'=>[]]);
        //默认所有类型
        if($post['type'] == 0){
            $type = [];
        }
        //文本
        $type=[
            "term"=>[
                "attribute"=>$post['type']
            ]
        ];

        //排序
        if($post['order'] == 1){
            $post['order'] = 'datetime';
        }else{
            $post['order'] = 'click_num';
        }
        //标题检索
        if($post['retrieval'] == 1){
            $fuzzy = [
                "fuzzy"=>[
                    "title"=>[
                        "value"=> "松花粉",
                        "fuzziness"=> 1
                    ]
                ]
            ];
            $pinyin = [
                'match'=>[
                    'title.pinyin' =>'song',
                ]
            ];
            $jinyici = [
                "match_phrase"=>[
                    "title"=>[
                        "analyzer"=>"my_synonyms",
                        "query"=>"西红柿"
                    ]
                ]
            ];
        }
        //全文检索
        if($post['retrieval'] == 2){
            $fuzzy = [
                "fuzzy"=>[
                    "title"=>[
                        "value"=> "核桃粉",
                        "fuzziness"=> 1
                    ],
                    "cabstract_text"=>[
                        "value"=> "核桃粉",
                        "fuzziness"=> 1
                    ],
                    "maintext"=>[
                        "value"=> "核桃粉",
                        "fuzziness"=> 1
                    ]
                ]
            ];
            $pinyin = [
                'match'=>[
                    'title.pinyin' =>'song',
                    'cabstract_text.pinyin' =>'song',
                    'maintext.pinyin' =>'song',
                ]
            ];
            $jinyici = [
                "match_phrase"=>[
                    "title"=>[
                        "analyzer"=>"my_synonyms",
                        "query"=>"西红柿"
                    ],
                    "cabstract_text"=>[
                        "analyzer"=>"my_synonyms",
                        "query"=>"西红柿"
                    ],
                    "maintext"=>[
                        "analyzer"=>"my_synonyms",
                        "query"=>"西红柿"
                    ],
                ]
            ];
        }
        $no = [];
        //高级搜索
        if($post['search'] == 2){
//            echo 111;die;
            $no = [
                "term"=>[
                    "title"=>"核桃粉"
                ]

            ];
            //是否过期
            if(isset($post['judge'])){
                $type=[
                    //知识树
                    [
                        "term"=>[
                            "leve"=>1
                        ]
                    ],
                    //发布时间
                    [
                        'range' => [
                            'datetime' => [
                                'gte' => "1562162400",
                                'lte' => "1562169599"
                            ],
                            'over_stime'=>[
                                'lte'=>date('Y-m-d',time())
                            ]
                        ]
                    ]
                ];
            }
            //标题搜索
            $type=[
                //知识树
                [
                    "term"=>[
                        "leve"=>1
                    ]
                ],
                //发布时间
                [
                    'range' => [
                        'datetime' => [
                            'gte' => "1562162400",
                            'lte' => "1562169599"
                        ]
                    ]
                ]
            ];

        }
        $params = [
            'index' => 'know01',
            'type' => 'know_test01',
            'body' => [
                //分页
                'from' => 0,  //$pages,
                'size' => 10,  //$page_size,
                "query"=>[
                    "bool"=>[
                        "must"=>$type,
                        "should"=>[
                            //模糊
                            $fuzzy,
                            //拼音搜索
                            $pinyin,
                            //近义词
                            $jinyici
                        ],
                        "must_not"=>$no,
//                        "filter"=>[
//                            "term"=>[
//                                "deletetime"=>"null"
//                            ]
//                        ]


                    ],
                ],
                //排序
                'sort' => [
                    $post['order'] => [
                        'order' => 'desc'
                    ]
                ],
                //高亮
                "highlight"=> [
                    "pre_tags" => [ "<b>" ],
                    "post_tags" => [ "</b>" ],
                    "fields" => [
                        "title" => new \stdClass(),
                    ]
                ]
            ]
        ];
        $result = $this->es->search($params)['hits']['hits'];
        //var_dump($result);die;
        foreach($result as $key =>$value){
            $arr[$key]['k_id'] = $value['_source']['k_id'];
            $arr[$key]['content'] = $value['_source']['maintext'];
            $arr[$key]['title'] = $value['highlight']['title'];
        }
//        dump($array);die;
        return json_encode(['code'=>"200",'massgae'=>"成功",'data'=>$arr],JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
    //词库管理 查询
    public function wordlist()
    {
        $post = input('post.');
        //分页
        if(!isset($post['tpage'])){
            $post['tpage'] = 1;
        }
        //搜索内容
        $dwhere = '';$nwhere = '';
        if(isset($post['content']) && $post['content'] != '' && $post['content'] != 0){
            $content = $post['content'];
            $dwhere .="`d_word` LIKE '%$content%' ";
            $nwhere .= "`n_word` LIKE '%$content%' ";
        }
        //创建时间 默认降序
        $createtime = "create_time DESC";
        if(isset($post['create_time']) && $post['create_time'] == 1){
            $createtime = "create_time DESC";
        }
        if(isset($post['create_time']) && $post['create_time'] == 2){
            $createtime = "create_time ASC";
        }
        //修改时间
        $updatetime = "";
        if(isset($post['update_time']) && $post['update_time'] == 1){
            $createtime = '';
            $updatetime = "update_time DESC";
        }
        if(isset($post['update_time']) && $post['update_time'] == 2){
            $createtime = '';
            $updatetime = "update_time ASC";
        }
        $data = $this->word->wordlists($post,$dwhere,$nwhere,$createtime,$updatetime);
        //把数据放入es远程控制的文本里面
        $text = $this->text($data);
        return json(['code'=>"200",'massgae'=>"成功",'data'=>$data]);
    }
    //词库管理 添加
    public function wordadd()
    {
        $post = input('post.');
        if(!isset($post['d_word']) || $post['d_word'] == '') return json(['code'=>"201",'massgae'=>"代表词语不能为空",'data'=>[]]);
        if(!isset($post['n_word']) || $post['n_word'] == '') return json(['code'=>"201",'massgae'=>"近义词描述不能为空",'data'=>[]]);
        $where = [
            "d_word"=>$post['d_word']
        ];
        $dword = $this->word->seldword($where);
        if($dword) return json(['code'=>"201",'massgae'=>"该代表词已存在",'data'=>[]]);
        $n_word = (array)$post['n_word'];
        $str = implode('/',$n_word);
        $arr = [
            "d_word"=>$post['d_word'],
            "n_word"=>$str,
            "update_time"=>date('Y-m-d H:i:s',time()),
            "create_time"=>date('Y-m-d H:i:s',time()),
        ];
        $data = $this->word->addword($arr);
        $res = $this->word->selword();
        //把数据放入es远程控制的文本里面
        $text = $this->text($res);
        return json(['code'=>"200",'massgae'=>"添加成功",'data'=>$data]);
    }
    //词库管理 编辑
    public function updateword()
    {
        $post = input('post.');
        if(!isset($post['d_word']) || $post['d_word'] == '') return json(['code'=>"201",'massgae'=>"代表词语不能为空",'data'=>[]]);
        if(!isset($post['n_word']) || $post['n_word'] == '') return json(['code'=>"201",'massgae'=>"近义词描述不能为空",'data'=>[]]);
        $where = [
            "d_word"=>$post['d_word']
        ];
        $dword = $this->word->seldword($where);
        if($dword) return json(['code'=>"201",'massgae'=>"该代表词已存在",'data'=>[]]);
        $id = ["id"=>$post['id']];
        $n_word = (array)$post['n_word'];
        $str = implode('/',$n_word);
        $wheres = [
            "d_word"=>$post['d_word'],
            "n_word"=>$str,
            "update_time"=>date('Y-m-d H:i:s',time()),
        ];
        $data = $this->word->updateword($id,$wheres);
        $res = $this->word->selword();
        //把数据放入es远程控制的文本里面
        $text = $this->text($res);
        return json(['code'=>"200",'massgae'=>"编辑成功",'data'=>$data]);
    }
    //词库管理 删除
    public function delword()
    {
        $post = input('id/a');
        if(empty($post)) return json(['code'=>"200",'massgae'=>"为传id",'data'=>[]]);
        $data = $this->word->delword($post);
        $res = $this->word->selword();
        //把数据放入es远程控制的文本里面
        $text = $this->text($res);
        return json(['code'=>"200",'massgae'=>"删除成功",'data'=>$data]);
    }
    public function text($sql)
    {
        if(empty($sql)){
            $file = ROOT_PATH . 'public/test.txt';
            $content = '';
            file_put_contents($file,$content);die;
        }
        foreach($sql as $key => $value){
            $str = str_replace('/',',',$value['n_word']);
            $arr[$key] = $value['d_word'].','.$str."\r\n";
        }
        $content = implode('',$arr);
        $file = ROOT_PATH . 'public/test.txt';
        file_put_contents($file,$content);
    }
    //
    public function setindexs()
    {
        $params = [
            // 索引名称
            'index' => 'know05',
            'body' => [
                //配置
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            //同义词
                            'my_synonym_filter' => [
                                'type' => 'dynamic_synonym',
                                //本地同义词路径
                                //  'synonyms_path' => "analysis/synonym.txt"
                                // 远程同义词路径（文件utf-8编码）
                                'synonyms_path' => "http://192.168.30.209/synonym.txt",
                                //同义词文件30秒刷新一次
                                "interval" => 30
                            ]
                        ],
                        'analyzer' => [
                            //配置同义词过滤器
                            'my_synonyms' => [
                                //最细粒度拆分
                                'tokenizer' => 'ik_max_word',
                                'filter' => [
                                    'lowercase',
                                    'my_synonym_filter'
                                ]
                            ]
                        ]
                    ]
                ],
                // 映射
                'mappings' => [
                    'know_test01' => [
                        //配置数据结构与类型
                        'properties' => [

                            'title' => [
                                //text会被拆分
                                'type' => 'text',
                                //使用同义词
                                'analyzer' => 'my_synonyms',
                                'fields' => [
                                    //拼音会被拆分
                                    'pinyin' => [
                                        'type' => 'text',
                                        'analyzer' => 'pinyin',
                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $res = $this->es->indices()->create($params);
        var_dump($res);
    }
    /*GET  know05/_search
{
  "query": {
      "match_phrase": {
        "title": {
          "analyzer": "my_synonyms",
          "query": "松花粉"
        }
      }
    }
}*/
    public function a()
    {
        $params =[
            'index' => 'know05',
            'type' => 'know_test01',
            'body' => [
                'title' => "松花粉",
            ]
        ];
        $response = $this->es->index($params);
        var_dump($response);
    }

}