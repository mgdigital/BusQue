local ns, queue, id, message = ARGV[1], ARGV[2], ARGV[3], ARGV[4]

redis.call('SADD', ns..':queues', queue)
redis.call('HSET', ns..':'..queue..':messages', id, message)

if redis.call('SISMEMBER', ns..':'..queue..':queued_ids', id) == 0 then
    redis.call('ZREM', ns..':schedule', queue..'||'..id)
    redis.call('SADD', ns..':'..queue..':queued_ids', id)
    redis.call('LPUSH', ns..':'..queue..':queue', id)
end
