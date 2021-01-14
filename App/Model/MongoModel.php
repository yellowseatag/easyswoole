<?php


namespace App\Model;


use App\Foundation\CachedTrait;
use Jenssegers\Mongodb\Eloquent\Model;

class MongoModel extends Model
{

    use CachedTrait;

    public function __construct(array $attributes = [])
    {
        if (!isset($this->collection)) {
            $connectionName = strtolower(basename(dirname(get_class_ds($this, '/'))));
            if ($connectionName == 'model') {
                $connectionName = 'default';
            }
            $this->connection = $connectionName . '-mongodb';
        }

        parent::__construct($attributes);
    }

}