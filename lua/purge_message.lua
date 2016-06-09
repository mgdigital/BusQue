local queue, id = ARGV[1], ARGV[2]

redis.call('HDEL', ':'..queue..':messages', id)
redis.call('HDEL', ':'..queue..':statuses', id)
redis.call('SREM', ':'..queue..':reserved_ids', id)
redis.call('LREM', ':'..queue..':queue', 1, id)
redis.call('LREM', ':'..queue..':receiving', 1, id)
redis.call('LREM', ':'..queue..':consuming', 1, id)
redis.call('ZREM', ':schedule', queue..'||'..id)
