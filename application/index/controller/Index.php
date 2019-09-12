<?php
namespace app\index\controller;
use think\Controller;
use Elasticsearch\ClientBuilder;
use think\Db;
class Index extends Controller
{
    protected $es;
    public function __construct()
    {
        parent::__construct();
        $params = array(
            '192.168.30.209:9200'
        );
        $this->es = ClientBuilder::create()->setHosts($params)->build();
    }
    //设置索引 （分词，拼音，近义词）
    public function setMapping()
    {
        $params = [
            // 索引名称
            'index' => 'test01',
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
                    'my_test' => [
                        //配置数据结构与类型
                        'properties' => [
                            'name' => [
                                //keyword不会被拆分
                                'type' => 'keyword',
                                'fields' => [
                                    //拼音会被拆分
                                    'pinyin' => [
                                        'type' => 'text',
                                        'analyzer' => 'pinyin',
                                    ]
                                ]
                            ],
                            'age' => [
                                //age是整型
                                'type' => 'integer'
                            ],
                            'content' => [
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
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $res = $this->es->indices()->create($params);
        var_dump($res);
    }
    //添加
    public function add()
    {
        $params =[
            'index' => 'test01',
            'type' => 'my_test',
            'id' => 'id_8',
            'body' => [
                'name' => 'test01',
                'age' => 78,
                'content' => '书籍是伟大的天才留给人类的遗产'
            ]
        ];
        $response = $this->es->index($params);
        var_dump($response);
    }
    //拼音
    public function search()
    {
        $post = input("post.");
        //前端传过来的分页
        //$page_size = 10;
        //$pages = ($post['page']-1)*$page_size+1;
        //dump($pages);die;
        $params = [
            'index' => 'test01',
            'type' =>  'my_test',
            'body' => [
                //分页
                'from' => 0,  //$pages,
                'size' => 10,  //$page_size,
                "query"=>[
                    "bool"=>[
//                        "must" => [
//                            //拼音搜索
//                            [
//                                'match'=>[
//                                    'name.pinyin' =>'zhang',
                                      //ik 分词器
                                      //"content"=>"书籍"
//                                ]
//                            ],
//                        ],
                        "should" => [
                            //正则
//                            'regexp' => [
//                                'name' => '..课'
//                            ]
                            //通配符
//                            'wildcard' => [
//                                'content' => '西*',
//                            ]
                            //前缀
//                            'prefix' => [
//                                'content' => '马铃',
//                            ]
                            //近义词
                            [
                                "match_phrase"=>[
                                    "content"=>[
                                        "analyzer"=>"my_synonyms",
                                        "query"=>"西红柿"
                                    ]
                                ]
                            ],
                            //过滤空字段
//                            [
//                                'constant_score' => [
//                                    'filter' => [
//                                        //exists过滤为null的字段
//                                        'exists' => [
//                                            'field' => 'name'
//                                        ]
//                                    ]
//                                ]
//                            ],
                        //多字段查询
//                            [
//                                'multi_match' => [
//                                    'query' => '马铃薯',
//                                    'fields' => ['name^1.0', 'content^1.0']
//                                ]
//
//                            ],
//                            "analyzer"=>"ik_max_word",
//                            "content"=>"我是中国人"
                        ]
                    ],
                    //近义词
//                    "match_phrase"=>[
//                        "content"=>[
//                            "analyzer"=>"my_synonyms",
//                            "query"=>"西红柿"
//                        ]
//                    ]
                ],
                //排序
//                'sort' => [
//                    'age' => [
//                        'order' => 'desc'
//                    ]
//                ],
                //聚合 热搜词
//                'aggs' => [
//                    'top' => [
//                        'terms' => [
//                            'field' => 'name',
//                            'size' => 2, //top2
//                            'order' => ['_count' => 'desc'] //降序排列
//                        ]
//                    ]
//                ],
                //高亮
                "highlight"=> [
                    "pre_tags" => [ "<b>" ],
                    "post_tags" => [ "</b>" ],
                    "fields" => [
                        "content" => new \stdClass()
                    ]
                ]
            ]
        ];
        $result = $this->es->search($params);
        var_dump($result);
    }
    //将字符串写入文件换行
    public function addtext()
    {
        $sql = Db::table('es_near')->field('content')->select();
        foreach($sql as $key => $value){
            $arr[$key] = $value['content']."\r\n";
        }
        $content = implode('',$arr);
        $file = ROOT_PATH . 'public/test.txt';
        //$content = "测试003\r\n";
        file_put_contents($file,$content);
        $get = file_get_contents($file);
        dump($get);
    }
    public function searchs()
    {
        $post = input("post.");
        //前端传过来的分页
        //$page_size = 10;
        //$pages = ($post['page']-1)*$page_size+1;
        //dump($pages);die;
        $params = [
            'index' => 'test01',
            'type' =>  'my_test',
            'body' => [
                //分页
                'from' => 0,  //$pages,
                'size' => 10,  //$page_size,
                "query"=>[
                    "bool"=>[
                        "should" => [
                            //近义词
                            [
                                "match_phrase"=>[
                                    "content"=>[
                                        "analyzer"=>"my_synonyms",
                                        "query"=>"西红柿"
                                    ]
                                ]
                            ],
                            //模糊
                            [
                                "fuzzy"=>[
                                    "content"=>[
                                        "value"=> "人类",
                                        "fuzziness"=> 0.5
                                    ]
                                ]
                            ]
                        ]
                    ],

                ]
            ]
        ];
        $result = $this->es->search($params)['hits']['hits'];
//        var_dump($result);die;
        foreach($result as $key =>$value){
            $arr[$key]['id'] = $value['_id'];
            $arr[$key]['content'] = $value['_source']['content'];
        }
        dump($arr);
    }
}
