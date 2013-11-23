# ニセレディス #

Niseredis is a library full of lies.
Niseredis will make a fool of you by trying its best to mimic Redis.


## Supported Redis commands ##

Key space:

  - [`DEL`](http://redis.io/commands/del)
  - [`EXISTS`](http://redis.io/commands/exists)
  - [`KEYS`](http://redis.io/commands/keys)
  - [`MOVE`](http://redis.io/commands/move)
  - [`RANDOMKEY`](http://redis.io/commands/random)
  - [`RENAME`](http://redis.io/commands/rename)
  - [`RENAMENX`](http://redis.io/commands/renamenx)
  - [`TYPE`](http://redis.io/commands/type)

Strings:

  - [`APPEND`](http://redis.io/commands/append)
  - [`BITCOUNT`](http://redis.io/commands/bitcount)
  - [`DECR`](http://redis.io/commands/decr)
  - [`DECRBY`](http://redis.io/commands/decrby)
  - [`INCR`](http://redis.io/commands/incr)
  - [`INCRBY`](http://redis.io/commands/incrby)
  - [`INCRBYFLOAT`](http://redis.io/commands/incrbyfloat)
  - [`GET`](http://redis.io/commands/get)
  - [`GETBIT`](http://redis.io/commands/getbit)
  - [`GETRANGE`](http://redis.io/commands/getrange)
  - [`GETSET`](http://redis.io/commands/getset)
  - [`MGET`](http://redis.io/commands/mget)
  - [`MSET`](http://redis.io/commands/mset)
  - [`MSETNX`](http://redis.io/commands/msetnx)
  - [`SET`](http://redis.io/commands/set)
  - [`SETBIT`](http://redis.io/commands/setbit)
  - [`SETNX`](http://redis.io/commands/setnx)
  - [`SETRANGE`](http://redis.io/commands/setrange)
  - [`STRLEN`](http://redis.io/commands/strlen)

Lists:

  - [`LINDEX`](http://redis.io/commands/lindex)
  - [`LLEN`](http://redis.io/commands/llen)
  - [`LPOP`](http://redis.io/commands/lpop)
  - [`LPUSH`](http://redis.io/commands/lpush)
  - [`LRANGE`](http://redis.io/commands/lrange)
  - [`RPOP`](http://redis.io/commands/rpop)
  - [`RPUSH`](http://redis.io/commands/rpush)

Sets:

  - None

Sorted sets:

  - None

Hashes:

  - None

Connection:

  - [`ECHO`](http://redis.io/commands/echo) (as Redis::echo_() since `echo` is a reserved keyword)
  - [`PING`](http://redis.io/commands/ping)
  - [`QUIT`](http://redis.io/commands/quit)
  - [`SELECT`](http://redis.io/commands/select)

Server:

  - [`DBSIZE`](http://redis.io/commands/dbsize)
  - [`FLUSHALL`](http://redis.io/commands/flushall)
  - [`FLUSHDB`](http://redis.io/commands/flushdb)
  - [`TIME`](http://redis.io/commands/time)


## License ##

The code for Niseredis is distributed under the terms of the MIT license (see [LICENSE](LICENSE)).