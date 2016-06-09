local queue, id, message = ARGV[1], ARGV[2], ARGV[3]

redis.call('SADD', ':queues', queue)
redis.call('HSET', ':'..queue..':messages', id, message)

if redis.call('SISMEMBER', ':'..queue..':reserved_ids', id) == 0 then
    redis.call('SADD', ':'..queue..':reserved_ids', id)
    redis.call('HSET', ':'..queue..':statuses', id, 'queued')
    redis.call('LPUSH', ':'..queue..':queue', id)
end
