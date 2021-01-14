<?php


namespace App\Model;


use App\Foundation\CachedTrait;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Db\Cursor;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\ORM\Utility\PreProcess;

class BaseModel extends AbstractModel
{
    use CachedTrait;

    /* 快速支持连贯操作 */
    private $fields = "*";
    private $limit  = NULL;
    private $withTotalCount = FALSE;
    private $order  = NULL;
    private $where  = [];
    private $join   = NULL;
    private $group  = NULL;
    private $alias  = NULL;

    protected $autoTimeStamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    protected $tableNum = 1;

    protected $prefix = '';

    protected $connectionName = '';

    public function __construct(array $data = [])
    {
        //设置连接名
        if (empty($this->connectionName)) {

            $connectionName = strtolower(basename(dirname(get_class_ds($this, '/'))));

            if ($connectionName == 'model') {
                $connectionName = 'default';
            } else {
                $this->prefix = $connectionName;
            }

            $this->connection($connectionName);
        }

        //设置表名
        if (empty($this->tableName)) {
            $tableName = substr(str_camel2uline(get_classname($this)), 0, -6);

            //设置前缀
            if ($this->prefix) {
                $tableName = "{$this->prefix}_{$tableName}";
            }

            $this->tableName($tableName);
        }

        parent::__construct($data);
    }


    /**
     * @param array $data
     * @param int $table
     * @return AbstractModel
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public static function create(array $data = [], $table = -1): AbstractModel
    {
        $instance = new static($data);

        if ($table >= 0) {
            $tempTable = $instance->tableName . '_' . ($table % $instance->tableNum);
            $instance->tableName($tempTable, true);
        }

        return $instance;
    }


    public static function T($table = -1):AbstractModel
    {
        return self::create([], $table);
    }

    /**
     * 批量查询
     * @param null $where
     * @param bool $returnAsArray
     * @return array|bool|Cursor
     * @throws Exception
     * @throws \Throwable
     */
    public function getAll($where = null)
    {
        $builder = new QueryBuilder;
        $builder = PreProcess::mappingWhere($builder, $where, $this);
        $this->preHandleQueryBuilder($builder);
        $builder->get($this->parseTableName(), $this->limit, $this->fields);
        $results = $this->query($builder);
        if ($results === false){
            return [];
        }
        return $results;
    }

    /**
     * 连贯操作预处理
     * @param QueryBuilder $builder
     * @throws Exception
     * @throws \EasySwoole\Mysqli\Exception\Exception
     */
    private function preHandleQueryBuilder(QueryBuilder $builder)
    {
        // 快速连贯操作
        if ($this->withTotalCount) {
            $builder->withTotalCount();
        }
        if ($this->order && is_array($this->order)) {
            foreach ($this->order as $order){
                $builder->orderBy(...$order);
            }
        }
        if ($this->where) {
            $whereModel = new static();
            foreach ($this->where as $whereOne){
                if (is_array($whereOne[0]) || is_int($whereOne[0])){
                    $builder = PreProcess::mappingWhere($builder, $whereOne[0], $whereModel);
                }else{
                    $builder->where(...$whereOne);
                }
            }
        }
        if($this->group){
            $builder->groupBy($this->group);
        }
        if($this->join){
            foreach ($this->join as $joinOne) {
                $builder->join($joinOne[0], $joinOne[1], $joinOne[2]);
            }
        }
        // 如果在闭包里设置了属性，并且Model没设置，则覆盖Model里的
        if ( $this->fields == '*' ){
            $this->fields = implode(', ', $builder->getField());
        }

    }

    private function parseTableName()
    {
        $table = $this->getTableName();
        if ($this->alias !== NULL){
            $table .= " AS `{$this->alias}`";
        }
        return $table;
    }

}