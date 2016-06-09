local queue = ARGV[1]

redis.call('DEL', ':'..queue..':queue', ':'..queue..':receiving', ':'..queue..':consuming', ':'..queue..':statuses', ':'..queue..':reserved_ids')
redis.call('SREM', ':queues', queue)
