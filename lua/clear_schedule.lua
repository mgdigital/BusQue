local ns, queue, start_time, end_time = ARGV[1], ARGV[2], ARGV[3], ARGV[4]

local result = redis.call('ZRANGEBYSCORE', ns..':schedule', start_time, end_time)

local this_queue, this_id, num

for i, joined in ipairs(result) do
    local delimeter_pos = string.find(joined, '||')
    local this_queue = string.sub(joined, 0, delimeter_pos - 1)
    local id = string.sub(joined, delimeter_pos + 2)
    if queue == nil or queue == '' or this_queue == queue then
        num = redis.call('ZREM', ns..':schedule', joined)
        if num > 0 then
            redis.call('SREM', ns..':'..this_queue..':queued_ids', id)
        end
    end
end
