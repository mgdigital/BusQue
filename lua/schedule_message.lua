local queue, id, message, timestamp = ARGV[1], ARGV[2], ARGV[3], ARGV[4]

redis.call('SADD', ':queues', queue)
redis.call('HSET', ':'..queue..':messages', id, message)

if redis.call('SISMEMBER', ':'..queue..':reserved_ids', id) == 0 then
    redis.call('SADD', ':'..queue..':reserved_ids', id)
    redis.call('HSET', ':'..queue..':statuses', id, 'scheduled')
    local joined = table.concat({queue, id}, '||')
    redis.call('ZADD', ':schedule', timestamp, joined)
end
