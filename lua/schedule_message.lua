local ns, queue, id, message, timestamp = ARGV[1], ARGV[2], ARGV[3], ARGV[4], ARGV[5]

redis.call('SADD', ns..':queues', queue)
redis.call('HSET', ns..':'..queue..':messages', id, message)

redis.call('SREM', ns..':'..queue..':queued_ids', id)
redis.call('LREM', ns..':'..queue..':queue', 1, id)

local joined = queue..'||'..id
redis.call('ZREM', ns..':schedule', joined)
redis.call('ZADD', ns..':schedule', timestamp, joined)
