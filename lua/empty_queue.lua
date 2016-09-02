local ns, queue = ARGV[1], ARGV[2]

redis.call('DEL', ns..':'..queue..':queue', ns..':'..queue..':receiving', ns..':'..queue..':consuming', ns..':'..queue..':queued_ids')
redis.call('SREM', ns..':queues', queue)
