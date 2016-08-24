local ns, queue, id = ARGV[1], ARGV[2], ARGV[3]

redis.call('SREM', ns..':'..queue..':queued_ids', id)
redis.call('SREM', ns..':'..queue..':consuming', id)
