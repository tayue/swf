<?php
/**
 * 协程es客户端
 */

namespace Framework\SwServer\Es;
use Elasticsearch\ClientBuilder;
use Framework\SwServer\Guzzle\RingPhp\CoroutineHandler;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Swoole\Coroutine;


class ElasticSearch
{
    private $client;
    private $hosts=[
        '192.168.99.89:9200'
    ];

    // 构造函数
    public function __construct($hosts = [])
    {
        if($hosts){
            $this->hosts = $hosts;
        }
        $this->client = ClientBuilder::create();
        if (Coroutine::getCid() > 0) {
            $this->client->setHandler(new CoroutineHandler());
        }
        $this->client = $this->client->setHosts($this->hosts)->build();
    }

    // 创建索引
    public function createIndex($index_name = 'test_ik') { // 只能创建一次
        $params = [
            'index' => $index_name,
            'body' => [
                'settings' => [
                    'number_of_shards' => 5,
                    'number_of_replicas' => 2
                ]
            ]
        ];

        try {
            return $this->client->indices()->create($params);
        } catch (BadRequest400Exception $e) {
            $msg = $e->getMessage();
            $msg = json_decode($msg,true);
            return $msg;
        }
    }

    // 删除索引
    public function deleteIndex($index_name = 'test_ik') {
        $params = ['index' => $index_name];
        $response = $this->client->indices()->delete($params);
        return $response;
    }

    // 创建文档模板
    public function createMappings($type_name = 'goods',$index_name = 'test_ik') {

        $params = [
            'index' => $index_name,
            'body' => [
                $type_name => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'id' => [
                            'type' => 'integer', // 整型
                            'index' => 'not_analyzed',
                        ],
                        'title' => [
                            'type' => 'string', // 字符串型
                            'index' => 'analyzed', // 全文搜索
                            'analyzer' => 'ik_max_word'
                        ],
                        'content' => [
                            'type' => 'string',
                            'index' => 'analyzed',
                            'analyzer' => 'ik_max_word'
                        ],
                        'price' => [
                            'type' => 'integer'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->indices()->putMapping($params);
        return $response;
    }

    // 查看映射
    public function getMapping($type_name = 'goods',$index_name = 'test_ik') {
        $params = [
            'index' => $index_name,
            'type' => $type_name
        ];
        $response = $this->client->indices()->getMapping($params);
        return $response;
    }

    // 添加文档
    public function addDoc($id,$doc,$index_name = 'test_ik',$type_name = 'goods') {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id,
            'body' => $doc
        ];

        $response = $this->client->index($params);
        return $response;
    }

    // 判断文档存在
    public function existsDoc($id = 1,$index_name = 'test_ik',$type_name = 'goods') {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id
        ];

        $response = $this->client->exists($params);
        return $response;
    }


    // 获取文档
    public function getDoc($id = 1,$index_name = 'test_ik',$type_name = 'goods') {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id
        ];

        $response = $this->client->get($params);
        return $response;
    }

    // 更新文档
    public function updateDoc($id = 1,$index_name = 'test_ik',$type_name = 'goods') {
        // 可以灵活添加新字段,最好不要乱添加
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id,
            'body' => [
                'doc' => [
                    'title' => '苹果手机iPhoneX'
                ]
            ]
        ];

        $response = $this->client->update($params);
        return $response;
    }

    // 删除文档
    public function deleteDoc($id = 1,$index_name = 'test_ik',$type_name = 'goods') {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id
        ];

        $response = $this->client->delete($params);
        return $response;
    }

    // 查询文档 (分页，排序，权重，过滤)
    public function searchDoc($keywords = "电脑",$index_name = "test_ik",$type_name = "goods",$from = 0,$size = 2) {
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            [ 'match' => [ 'title' => [
                                'query' => $keywords,
                                'boost' => 3, // 权重大
                            ]]],
                            [ 'match' => [ 'content' => [
                                'query' => $keywords,
                                'boost' => 2,
                            ]]],
                        ],
                    ],
                ],
                'sort' => ['price'=>['order'=>'desc']]
                , 'from' => $from, 'size' => $size
            ]
        ];

        $results = $this->client->search($params);
//        $maxScore  = $results['hits']['max_score'];
//        $score = $results['hits']['hits'][0]['_score'];
//        $doc   = $results['hits']['hits'][0]['_source'];
        return $results;
    }

}