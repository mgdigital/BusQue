local queue, id = ARGV[1], ARGV[2]

local count = redis.call('LREM', ':'..queue..':receiving', 1, id)

if count == 1 then
    redis.call('HSET', ':'..queue..':statuses', id, 'in_progress')
    redis.call('SREM', ':'..queue..':reserved_ids', id)
    redis.call('LPUSH', ':'..queue..':consuming', id)
    return redis.call('HGET', ':'..queue..':messages', id)
end
