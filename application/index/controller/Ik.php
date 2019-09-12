<?php
namespace app\index\controller;
use think\Controller;
use Elasticsearch\ClientBuilder;
use think\Db;
class Ik extends Controller
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

    //设置索引
    public function index()
    {
        $params = [
            // 索引名称
            'index' => 'ik',
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
                                'synonyms_path' => "http://192.168.30.209/synonym.txt",//http://192.168.30.189:25525/synonym.txt
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
                        ],
                        "pinyin_full_filter"=>[
                            "type" => "pinyin",
                            "keep_first_letter" => false,
                            "keep_separate_first_letter" => false,
                            "keep_full_pinyin" => false,
                            "none_chinese_pinyin_tokenize" => false,
                            "keep_original" => false,
                            "limit_first_letter_length" => 50,
                            "lowercase" => false
                        ],
                    ]
                ],
                // 映射
                'mappings' => [
                    'ik' => [
                        //配置数据结构与类型
                        'properties' => [
                            //标题(标准问题)
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
        dump($res);die;
    }

    //添加数据
    public function addData()
    {
        $params = [
            "index" => "lsx_indexa",
            "type" => "zyr_fulltext",
            "body" => [
                "content" => "超薄电视",
            ]
        ];
        $response = $this->es->index($params);
        dump($response);die;
    }

    //查询数据
    public function selData()
    {
        $params = [
            'index' => 'ik',
            'type' => 'ik',
            'body' => [
                'query' => [
                    "bool"=>[
                        'wildcard' => [
                            'title' => '*是*',
                        ]
                    ]
                ]
            ]
        ];
        $result = $this->es->search($params);
        var_dump($result);
    }
}