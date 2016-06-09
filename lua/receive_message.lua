local queue, id = ARGV[1], ARGV[2]

redis.call('HSET', ':'..queue..':statuses', id, 'in_progress')
redis.call('SREM', ':'..queue..':reserved_ids', id)
return redis.call('HGET', ':'..queue..':messages', id)
