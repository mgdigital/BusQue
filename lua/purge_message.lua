local ns, queue, id = ARGV[1], ARGV[2], ARGV[3]

redis.call('HDEL', ns..':'..queue..':messages', id)
redis.call('SREM', ns..':'..queue..':queued_ids', id)
redis.call('LREM', ns..':'..queue..':queue', 1, id)
redis.call('LREM', ns..':'..queue..':receiving', 1, id)
redis.call('SREM', ns..':'..queue..':consuming', id)
redis.call('ZREM', ns..':schedule', queue..'||'..id)
