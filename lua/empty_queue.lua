local ns, queue = ARGV[1], ARGV[2]

redis.call('DEL', ns..':'..queue..':queue', ':'..queue..':receiving', ':'..queue..':consuming', ':'..queue..':queued_ids')
redis.call('SREM', ns..':queues', queue)
