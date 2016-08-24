local ns, start_time, end_time, limit = ARGV[1], ARGV[2], ARGV[3], ARGV[4]

local result = redis.call('ZRANGEBYSCORE', ns..':schedule', start_time, end_time, 'WITHSCORES', 'LIMIT', 0, limit)

local messages = {}

if #result > 0 then
    for i = 1, #result, 2 do
        local joined, time = result[i], result[i + 1]
        local delimeter_pos = string.find(joined, '||')
        local queue = string.sub(joined, 0, delimeter_pos - 1)
        local id = string.sub(joined, delimeter_pos + 2)
        local message = redis.call('HGET', ns..':'..queue..':messages', id)
        table.insert(messages, {queue, id, message, time})
        redis.call('ZREM', ns..':schedule', joined)
        redis.call('SREM', ns..':'..queue..':queued_ids', id)
    end
end

return messages
