<?php

namespace Supering\Venus;

class Redis
{
    private static $redis;

    public function __construct()
    {
        self::getInstance();
    }

    private static function getInstance()
    {
        if (!is_object(self::$redis)) {
            try {
                self::$redis = new \Redis();
                self::$redis->connect(Config::$redisHost, Config::$redisPort, Config::$redisTimeOut);
                self::$redis->auth(Config::$redisPass);
            } catch (\Exception $e) {
                throw new \Exception('redis connetc faild');
                exit();
            }
        }
    }

    private function setKey($key)
    {
        return md5($key);
    }

    /**
     * 查看 Redis 是否连接成功，正常返回 +PONG
     */
    public function ping()
    {
        return self::$redis->ping();
    }

    /**
     * 设置值，成功返回 true，失败返回false
     */
    public function set($key, $value, $express = 0)
    {
        $key = $this->setKey($key);
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }
        return $express ? self::$redis->setex($key, $express, $value) : self::$redis->set($key, $value);
    }

    /**
     * 设置key 的过期时间
     */
    public function expire($key, $time)
    {
        $key = $this->setKey($key);
        return self::$redis->expire($key, $time);
    }

    /**
     * 设置值，如果$key存在数据库，返回false，设置成功返回 true，失败返回false
     */
    public function setnx($key, $value)
    {
        $key = $this->setKey($key);
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }
        return self::$redis->setnx($key, $value);
    }

    /**
     * 获取值，不存在返回false
     */
    public function get($key)
    {
        $key   = $this->setKey($key);
        $value = self::$redis->get($key);
        if ($value) {
            $values = @unserialize($value);
            if (is_object($values) || is_array($values)) {
                return $values;
            }
            return $value;
        }
        return $value;
    }

    /**
     * 删除值，返回受影响的行数
     */
    public function delete($key)
    {
        $key = $this->setKey($key);
        return self::$redis->delete($key);
    }

    /**
     * 判断值是否存在，存在返回true，不存在返回false
     */
    public function exists($key)
    {
        $key = $this->setKey($key);
        return self::$redis->exists($key);
    }

    /**
     * $key 自增1
     */
    public function incr($key)
    {
        $key = $this->setKey($key);
        return self::$redis->incr($key);
    }

    /**
     * $key 自减1
     */
    public function decr($key)
    {
        $key = $this->setKey($key);
        return self::$redis->decr($key);
    }

    /**
     * 当 $key 不存在时，返回 -2 。 当 $key 存在但没有设置剩余生存时间时，返回 -1 。 否则，以秒为单位，返回 $key 的剩余生存时间。
     */
    public function ttl($key)
    {
        $key = $this->setKey($key);
        return self::$redis->ttl($key);
    }

    public function flushAll()
    {
        return self::$redis->flushall();
    }

    public function flushDb()
    {
        return self::$redis->flushdb();
    }
}
