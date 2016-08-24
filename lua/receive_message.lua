local ns, queue, id = ARGV[1], ARGV[2], ARGV[3]

local count = redis.call('LREM', ns..':'..queue..':receiving', 1, id)

if redis.call('SISMEMBER', ns..':'..queue..':consuming', id) == 1 then
    if count == 1 and redis.call('SISMEMBER', ns..':'..queue..':queued_ids', id) == 0 then
        redis.call('SADD', ns..':'..queue..':queued_ids', id)
        redis.call('LPUSH', ns..':'..queue..':queue', id)
    end
    return nil
end

if count == 1 then
    redis.call('SREM', ns..':'..queue..':queued_ids', id)
    redis.call('SADD', ns..':'..queue..':consuming', id)
    return redis.call('HGET', ns..':'..queue..':messages', id)
end
