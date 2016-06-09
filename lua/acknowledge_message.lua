local queue, id = ARGV[1], ARGV[2]

redis.call('HSET', ':'..queue..':statuses', id, 'completed')
redis.call('SREM', ':'..queue..':reserved_ids', id)
redis.call('LREM', ':'..queue..':queue', 1, id)
redis.call('LREM', ':'..queue..':consuming', 1, id)
